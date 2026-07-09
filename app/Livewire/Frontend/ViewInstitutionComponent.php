<?php

namespace App\Livewire\Frontend;

use App\Models\Institution;
use App\Models\Scopes\InstitutionScope;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ViewInstitutionComponent extends Component
{
    use WithPagination;

    public Institution $institution;

    public string $employeeSearch = '';

    /**
     * Teacher Detail Modal এর জন্য বর্তমানে সিলেক্ট করা teacher id।
     * eye icon ক্লিক করলে এখানে id সেট হয়, modal সেই id অনুযায়ী detail দেখায়।
     */
    public ?int $selectedEmployeeId = null;

    protected string $paginationTheme = 'bootstrap';

    public function mount(Institution $institution): void
    {
        // Global scope এর কারণে অন্য institution context থেকে এই পেজ খুললে
        // record না পাওয়ার সম্ভাবনা থাকে, তাই status ও existence re-verify করা হচ্ছে।
        $verified = Institution::withoutGlobalScope(InstitutionScope::class)
            ->where('id', $institution->id)
            ->where('status', true)
            ->first();

        abort_if(! $verified, 404);

        $this->institution = $verified;
    }

    public function updatingEmployeeSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Teacher Detail Modal ওপেন করার জন্য ব্যবহৃত হয়।
     * শুধু id সেট করা হয় — real data getEmployeeDetailProperty() থেকে
     * প্রতিবার fresh query করে আনা হয় (security: institution + role scoped)।
     */
    public function openEmployeeDetails(int $employeeId): void
    {
        $this->selectedEmployeeId = $employeeId;
    }

    public function closeEmployeeDetails(): void
    {
        $this->selectedEmployeeId = null;
    }

    public function getStatsProperty(): array
    {
        return [
            'students' => DB::table('students')
                ->where('institution_id', $this->institution->id)
                ->count(),

            'teachers' => User::withoutGlobalScope(InstitutionScope::class)
                ->where('institution_id', $this->institution->id)
                ->where('role', 'teacher')
                ->count(),

            'classes' => DB::table('academic_classes')
                ->where('institution_id', $this->institution->id)
                ->count(),

            'sections' => DB::table('academic_sections')
                ->where('institution_id', $this->institution->id)
                ->count(),
        ];
    }

    /**
     * Principal & Vice Principal Message Section।
     *
     * TODO (Dynamic Plan): ভবিষ্যতে `users` টেবিলে একটি `message` (text, nullable)
     * column যোগ করে নিচের placeholder সরিয়ে `$user->message` ব্যবহার করতে হবে।
     * Blade structure অপরিবর্তিত থাকবে কারণ Blade শুধু 'id', 'name', 'avatar',
     * 'role', 'role_label_bn', 'role_label_en', 'message' — এই key গুলো আশা করে।
     */
    public function getPrincipalsProperty()
    {
        $roleLabels = [
            'principal'      => ['bn' => 'প্রধান শিক্ষক', 'en' => 'Principal'],
            'vice_principal' => ['bn' => 'সহকারী প্রধান শিক্ষক', 'en' => 'Vice Principal'],
        ];

        return Employee::query()
            ->select(['id', 'user_id', 'designation_id', 'name', 'photo'])
            ->whereHas('designation', function ($query) {
                $query->whereIn('name', ['Principal', 'Vice Principal']);
            })
            ->with(['designation:id,name'])
            ->get()
            ->sortBy(function ($employee) {
                return $employee->designation->name === 'Principal' ? 0 : 1;
            })
            ->values()
            ->map(function ($employee) use ($roleLabels) {
                $designationName = $employee->designation->name;
                $roleKey = $designationName === 'Principal' ? 'principal' : 'vice_principal';

                $employee->role_label_bn = $roleLabels[$roleKey]['bn'] ?? $designationName;
                $employee->role_label_en = $roleLabels[$roleKey]['en'] ?? $designationName;

                // Placeholder message — employees টেবিলে message column তৈরি হলে এখানে $employee->message বসবে।
                $employee->message = $roleKey === 'principal'
                    ? 'আমাদের প্রতিষ্ঠানে শিক্ষার্থীদের মানসম্মত শিক্ষা ও নৈতিক মূল্যবোধ গঠনে আমরা প্রতিশ্রুতিবদ্ধ। আপনাদের সন্তানদের উজ্জ্বল ভবিষ্যৎ গড়ার লক্ষ্যে আমরা সবসময় পাশে আছি।'
                    : 'শিক্ষার্থীদের সার্বিক উন্নয়ন ও শৃঙ্খলা রক্ষায় আমরা নিরলসভাবে কাজ করে যাচ্ছি। অভিভাবকদের সহযোগিতা আমাদের এই যাত্রাকে আরও সহজ করে তোলে।';

                return $employee;
            });
    }

    /**
     * Public-safe Teacher Directory।
     * শুধু name, avatar দেখানো হচ্ছে —
     * phone, email, salary, NID ইত্যাদি sensitive তথ্য কখনোই এখানে যোগ করা যাবে না।
     * Modal-এ দেখানো detail-ও একই নিয়ম মেনে চলে (দেখুন getEmployeeDetailProperty)।
     */
    public function getEmployeesProperty()
    {
        return User::withoutGlobalScope(InstitutionScope::class)
            ->select(['id', 'name', 'avatar', 'role'])
            ->where('institution_id', $this->institution->id)
            ->where('role', 'teacher')
            ->when($this->employeeSearch !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->employeeSearch . '%');
            })
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * Teacher Detail Modal এর ডেটা।
     * নিরাপত্তার জন্য institution_id ও role = teacher দিয়ে scoped —
     * URL/param manipulation করে অন্য institution বা অন্য role এর ডেটা দেখা যাবে না।
     *
     * TODO (Dynamic Plan): qualification, subject, joining_date ইত্যাদি public-safe
     * column যোগ হলে select এ যুক্ত করে নিচে array তে বসাতে হবে।
     */
    public function getEmployeeDetailProperty(): ?array
    {
        if (! $this->selectedEmployeeId) {
            return null;
        }

        $teacher = User::withoutGlobalScope(InstitutionScope::class)
            ->select(['id', 'name', 'avatar', 'role'])
            ->where('institution_id', $this->institution->id)
            ->where('role', 'teacher')
            ->where('id', $this->selectedEmployeeId)
            ->first();

        if (! $teacher) {
            return null;
        }

        return [
            'id'     => $teacher->id,
            'name'   => $teacher->name,
            'avatar' => $teacher->avatar,
            'role'   => $teacher->role,
            // Placeholder bio — users এ public-safe bio/qualification column যোগ হলে এখানে বসবে।
            'bio'    => 'তিনি এই প্রতিষ্ঠানের একজন নিবেদিতপ্রাণ শিক্ষক, যিনি শিক্ষার্থীদের শিক্ষাগত ও নৈতিক উন্নয়নে সক্রিয়ভাবে ভূমিকা রাখেন।',
        ];
    }

    /**
     * স্কুল/কলেজ Ranking — বর্তমানে শুধু Student Count ভিত্তিক।
     * TODO: Result module রেডি হলে Pass Rate যোগ করে weighted score বানাতে হবে,
     * তখন এই মেথডে formula আপডেট করলেই চলবে, ব্লেড ফাইল পরিবর্তনের দরকার নেই।
     */
    public function getRankingProperty(): array
    {
        $ranked = Institution::withoutGlobalScope(InstitutionScope::class)
            ->where('status', true)
            ->select('institutions.id')
            ->selectSub(function ($query) {
                $query->from('students')
                    ->selectRaw('count(*)')
                    ->whereColumn('students.institution_id', 'institutions.id');
            }, 'student_count')
            ->orderByDesc('student_count')
            ->get();

        $totalInstitutions = $ranked->count();
        $position = $ranked->search(fn ($item) => (int) $item->id === (int) $this->institution->id);

        return [
            'position' => $position === false ? null : $position + 1,
            'total'    => $totalInstitutions,
        ];
    }

    /**
     * Facilities / Infrastructure list.
     *
     * TODO (Dynamic Plan): ভবিষ্যতে একটি `institution_facilities` টেবিল বানাতে হবে
     * (columns: institution_id, key, label_bn, label_en, icon, is_available)
     * এবং এই মেথডে DB থেকে fetch করলেই Blade ফাইল অপরিবর্তিত থাকবে,
     * কারণ Blade শুধু 'key', 'label_bn', 'label_en', 'icon', 'available' — এই structure আশা করে।
     */
    public function getFacilitiesProperty(): array
    {
        return [
            ['key' => 'library',   'label_bn' => 'লাইব্রেরি',        'label_en' => 'Library',          'icon' => 'bi-book-half',        'available' => true],
            ['key' => 'science_lab','label_bn' => 'সায়েন্স ল্যাব',    'label_en' => 'Science Lab',      'icon' => 'bi-flask',            'available' => true],
            ['key' => 'computer_lab','label_bn' => 'কম্পিউটার ল্যাব', 'label_en' => 'Computer Lab',     'icon' => 'bi-pc-display',       'available' => true],
            ['key' => 'playground', 'label_bn' => 'খেলার মাঠ',        'label_en' => 'Playground',       'icon' => 'bi-tree',             'available' => true],
            ['key' => 'transport',  'label_bn' => 'পরিবহন সুবিধা',    'label_en' => 'Transport',        'icon' => 'bi-bus-front',        'available' => false],
            ['key' => 'hostel',     'label_bn' => 'হোস্টেল',          'label_en' => 'Hostel',           'icon' => 'bi-house-door',       'available' => false],
            ['key' => 'canteen',    'label_bn' => 'ক্যান্টিন',        'label_en' => 'Canteen',          'icon' => 'bi-cup-hot',          'available' => true],
        ];
    }

    /**
     * Public Notice Board.
     *
     * TODO (Dynamic Plan): `notices` টেবিল থেকে
     * ->where('institution_id', $this->institution->id)->where('is_public', true)
     * ->latest()->take(5)->get() দিয়ে replace করতে হবে।
     * Blade structure: 'title', 'type', 'date' — এই তিনটা key থাকলেই চলবে।
     */
    public function getNoticesProperty(): array
    {
        return [
            ['title' => 'নতুন শিক্ষাবর্ষের ভর্তি বিজ্ঞপ্তি প্রকাশিত হয়েছে', 'type' => 'admission', 'date' => now()->subDays(2)->format('d M, Y')],
            ['title' => 'বার্ষিক ক্রীড়া প্রতিযোগিতা আগামী মাসে অনুষ্ঠিত হবে', 'type' => 'event',     'date' => now()->subDays(5)->format('d M, Y')],
            ['title' => 'জাতীয় ছুটির কারণে প্রতিষ্ঠান বন্ধ থাকবে',           'type' => 'holiday',   'date' => now()->subDays(9)->format('d M, Y')],
        ];
    }

    /**
     * Admission Info Summary.
     *
     * TODO (Dynamic Plan): ভবিষ্যতে `institutions` টেবিলে বা আলাদা
     * `admission_settings` টেবিলে (institution_id, is_open, fee_min, fee_max,
     * apply_mode, documents_json) কলাম যোগ করে এখান থেকে fetch করতে হবে।
     */
    public function getAdmissionProperty(): array
    {
        return [
            'is_open'    => true,
            'fee_min'    => 500,
            'fee_max'    => 1500,
            'apply_mode' => 'online', // online | offline | both
            'documents'  => [
                'জন্ম নিবন্ধন সনদের কপি',
                'পূর্ববর্তী স্কুলের প্রত্যায়নপত্র (যদি থাকে)',
                'অভিভাবকের জাতীয় পরিচয়পত্রের কপি',
                'সাম্প্রতিক তোলা ২ কপি ছবি',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.frontend.view-institution-component', [
            'institution'    => $this->institution,
            'stats'          => $this->stats,
            'principals'     => $this->principals,
            'employees'      => $this->employees,
            'employeeDetail' => $this->employeeDetail,
            'ranking'        => $this->ranking,
            'facilities'     => $this->facilities,
            'notices'        => $this->notices,
            'admission'      => $this->admission,
        ])->layout('layouts.frontend.app', [
            'title' => $this->institution->name,
        ]);
    }
}