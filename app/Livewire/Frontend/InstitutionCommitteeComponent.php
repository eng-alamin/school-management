<?php

namespace App\Livewire\Frontend;

use App\Models\InstitutionCommittee;
use Livewire\Component;
use Livewire\WithPagination;

class InstitutionCommitteeComponent extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * active | former | all
     */
    public string $statusFilter = 'active';

    /**
     * Detail Modal এর জন্য বর্তমানে সিলেক্ট করা member id।
     * eye icon ক্লিক করলে এখানে id সেট হয়, modal সেই id অনুযায়ী detail দেখায়।
     */
    public ?int $selectedMemberId = null;

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    /**
     * Member Detail Modal ওপেন করার জন্য ব্যবহৃত হয়।
     * শুধু id সেট করা হয় — real data getSelectedMemberProperty() থেকে
     * প্রতিবার fresh query করে আনা হয়।
     */
    public function openMemberDetails(int $memberId): void
    {
        $this->selectedMemberId = $memberId;
    }

    public function closeMemberDetails(): void
    {
        $this->selectedMemberId = null;
    }

    /**
     * কমিটি সদস্যদের তালিকা।
     * BelongsToInstitution trait এর কারণে বর্তমান institution অনুযায়ী auto-scoped,
     * তাই এখানে আলাদা করে institution_id দিয়ে filter করার দরকার নেই।
     */
    public function getMembersProperty()
    {
        return InstitutionCommittee::query()
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('designation', 'like', '%' . $this->search . '%');
                });
            })
            ->ordered()
            ->paginate(9);
    }

    /**
     * Active / Former ট্যাবের পাশে badge count দেখানোর জন্য।
     */
    public function getCountsProperty(): array
    {
        return [
            'active' => InstitutionCommittee::query()->where('status', 'active')->count(),
            'former' => InstitutionCommittee::query()->where('status', 'former')->count(),
        ];
    }

    /**
     * Modal এর জন্য সিলেক্ট করা সদস্যের সম্পূর্ণ ডিটেইলস।
     * প্রতিবার fresh query — data consistency নিশ্চিত করার জন্য।
     */
    public function getSelectedMemberProperty(): ?InstitutionCommittee
    {
        if (! $this->selectedMemberId) {
            return null;
        }

        return InstitutionCommittee::query()->find($this->selectedMemberId);
    }

    public function render()
    {
        return view('livewire.frontend.institution-committee-component', [
            'members'        => $this->members,
            'counts'         => $this->counts,
            'selectedMember' => $this->selectedMember,
        ])->layout('layouts.frontend.app', [
            'title' => 'Committee Members',
        ]);
    }
}