<?php

namespace App\Livewire\Frontend;

use App\Models\Institution;
use App\Models\InstitutionFacility;
use App\Models\Scopes\InstitutionScope;
use App\Models\User;
use App\Models\Employee;
use App\Models\InstitutionCommittee;
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
     */
    public function getPrincipalsProperty()
    {
        $roleLabels = [
            'principal'      => ['bn' => 'প্রধান শিক্ষক', 'en' => 'Principal'],
            'vice_principal' => ['bn' => 'সহকারী প্রধান শিক্ষক', 'en' => 'Vice Principal'],
        ];

        $defaultMessages = [
            'principal'      => 'আমাদের প্রতিষ্ঠানে শিক্ষার্থীদের মানসম্মত শিক্ষা ও নৈতিক মূল্যবোধ গঠনে আমরা প্রতিশ্রুতিবদ্ধ। আপনাদের সন্তানদের উজ্জ্বল ভবিষ্যৎ গড়ার লক্ষ্যে আমরা সবসময় পাশে আছি।',
            'vice_principal' => 'শিক্ষার্থীদের সার্বিক উন্নয়ন ও শৃঙ্খলা রক্ষায় আমরা নিরলসভাবে কাজ করে যাচ্ছি। অভিভাবকদের সহযোগিতা আমাদের এই যাত্রাকে আরও সহজ করে তোলে।',
        ];

        return Employee::query()
            ->select(['id', 'user_id', 'institution_id', 'designation_id', 'name', 'photo', 'comments'])
            ->where('institution_id', $this->institution->id)
            ->whereHas('designation', function ($query) {
                $query->whereIn('name', ['Principal', 'Vice Principal']);
            })
            ->with(['designation:id,name'])
            ->get()
            ->sortBy(function ($employee) {
                return $employee->designation->name === 'Principal' ? 0 : 1;
            })
            ->values()
            ->map(function ($employee) use ($roleLabels, $defaultMessages) {
                $designationName = $employee->designation->name;
                $roleKey = $designationName === 'Principal' ? 'principal' : 'vice_principal';

                $employee->role_label_bn = $roleLabels[$roleKey]['bn'] ?? $designationName;
                $employee->role_label_en = $roleLabels[$roleKey]['en'] ?? $designationName;

                $employee->message = !empty($employee->comments)
                    ? $employee->comments
                    : $defaultMessages[$roleKey];

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

    public function getFacilitiesProperty(): array
    {
        return InstitutionFacility::query()
            ->where('institution_id', $this->institution->id)
            ->where('status', InstitutionFacility::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(function (InstitutionFacility $facility) {
                return [
                    'id'   => $facility->id,
                    'name' => $facility->name,
                    'icon' => self::facilityIconFor($facility->name),
                ];
            })
            ->all();
    }

    /**
     * Facility name এর keyword অনুযায়ী একটা relevant bootstrap icon return করে।
     * কোনো keyword না মিললে generic fallback icon ব্যবহার হয়।
     */
    private static function facilityIconFor(string $name): string
    {
        $name = strtolower($name);

        $iconMap = [
            'library'    => 'bi-book-half',
            'science'    => 'bi-eyedropper',
            'lab'        => 'bi-flask',
            'computer'   => 'bi-pc-display',
            'playground' => 'bi-tree',
            'ground'     => 'bi-tree',
            'transport'  => 'bi-bus-front',
            'bus'        => 'bi-bus-front',
            'hostel'     => 'bi-house-door',
            'canteen'    => 'bi-cup-hot',
            'cafeteria'  => 'bi-cup-hot',
            'mosque'     => 'bi-moon-stars',
            'prayer'     => 'bi-moon-stars',
            'wifi'       => 'bi-wifi',
            'internet'   => 'bi-wifi',
            'cctv'       => 'bi-camera-video',
            'security'   => 'bi-shield-check',
            'generator'  => 'bi-lightning-charge',
            'medical'    => 'bi-heart-pulse',
            'health'     => 'bi-heart-pulse',
            'sports'     => 'bi-trophy',
            'auditorium' => 'bi-easel',
            'parking'    => 'bi-p-square',
            'air'        => 'bi-snow',
        ];

        foreach ($iconMap as $keyword => $icon) {
            if (str_contains($name, $keyword)) {
                return $icon;
            }
        }

        return 'bi-patch-check';
    }

    /**
     * Public Notice Board.
     */
    public function getNoticesProperty(): array
    {
        return \App\Models\Notice::query()
        ->where('institution_id', $this->institution->id)
        ->forAudience('all')
        ->active()
        ->latest('published_at')
        ->take(5)
        ->get()
        ->map(fn ($notice) => [
            'title' => $notice->title,
            'type'  => $notice->priority,
            'date'  => $notice->published_at->format('d M, Y'),
        ])
        ->all();
    }

    /**
     * Admission Info Summary.
     *
     * TODO (Dynamic Plan): ভবিষ্যতে `institutions` টেবিলে বা আলাদা
     * `admission_settings` টেবিলে (institution_id, is_open, fee_min, fee_max,
     * apply_mode, documents_json) কলাম যোগ করে এখান থেকে fetch করতে হবে।
     * `documents` এর প্রতিটা item-এ 'bn' ও 'en' দুইটা key thakle Blade আর কোনো
     * পরিবর্তন লাগবে না।
     */
    public function getAdmissionProperty(): array
    {
        return [
            'is_open'    => true,
            'fee_min'    => 500,
            'fee_max'    => 1500,
            'apply_mode' => 'online', // online | offline | both
            'documents'  => [
                ['bn' => 'জন্ম নিবন্ধন সনদের কপি', 'en' => 'Copy of Birth Registration Certificate'],
                ['bn' => 'পূর্ববর্তী স্কুলের প্রত্যায়নপত্র (যদি থাকে)', 'en' => 'Transfer Certificate from Previous School (if any)'],
                ['bn' => 'অভিভাবকের জাতীয় পরিচয়পত্রের কপি', 'en' => "Copy of Guardian's National ID Card"],
                ['bn' => 'সাম্প্রতিক তোলা ২ কপি ছবি', 'en' => '2 Recent Passport-size Photographs'],
            ],
        ];
    }

    public function getInstitutionCommitteesProperty()
    {
        return InstitutionCommittee::query()
            ->withoutGlobalScope(InstitutionScope::class)
            ->where('institution_id', $this->institution->id)
            ->where('status', 'active')
            ->ordered()
            ->take(6)
            ->get();
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
            'institutionCommittees' => $this->InstitutionCommittees,
        ])->layout('layouts.frontend.app', [
            'title' => $this->institution->name,
        ]);
    }
}