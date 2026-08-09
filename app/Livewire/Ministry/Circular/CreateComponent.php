<?php

namespace App\Livewire\Ministry\Circular;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Circular;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateComponent extends Component
{
    use WithFileUploads;

    public string $title       = '';
    public string $description = '';
    public string $audience    = Circular::AUDIENCE_ALL;
    public string $division    = '';
    public string $district    = '';
    public ?int   $institutionId = null;
    public ?string $expiresAt  = null;
    public $attachment;

    protected function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'audience'      => ['required', 'in:' . implode(',', array_keys(Circular::AUDIENCES))],
            'division'      => ['required_if:audience,division', 'nullable', 'string'],
            'district'      => ['required_if:audience,district', 'nullable', 'string'],
            'institutionId' => ['required_if:audience,institution', 'nullable', 'exists:institutions,id'],
            'expiresAt'     => ['nullable', 'date', 'after_or_equal:today'],
            'attachment'    => ['nullable', 'file', 'max:5120'],
        ];
    }

    public function updatingAudience(): void
    {
        $this->division      = '';
        $this->district      = '';
        $this->institutionId = null;
    }

    #[Computed]
    public function divisions(): array
    {
        return Institution::DIVISIONS;
    }

    #[Computed]
    public function districts()
    {
        return Institution::query()
            ->when($this->division, fn ($q) => $q->where('division', $this->division))
            ->whereNotNull('district')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');
    }

    #[Computed]
    public function institutions()
    {
        return Institution::orderBy('name')->select('id', 'name')->get();
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $path = $this->attachment ? $this->attachment->store('circulars', 'public') : null;

            $circular = Circular::create([
                'title'            => $this->title,
                'description'      => $this->description,
                'attachment'       => $path,
                'attachment_name'  => $this->attachment?->getClientOriginalName(),
                'audience'         => $this->audience,
                'division'         => $this->audience === Circular::AUDIENCE_DIVISION ? $this->division : null,
                'district'         => $this->audience === Circular::AUDIENCE_DISTRICT ? $this->district : null,
                'institution_id'   => $this->audience === Circular::AUDIENCE_INSTITUTION ? $this->institutionId : null,
                'created_by'       => Auth::id(),
                'status'           => 'active',
                'published_at'     => today(),
                'expires_at'       => $this->expiresAt,
            ]);

            $targetIds = $circular->targetInstitutionsQuery()->pluck('id');

            $rows = $targetIds->map(fn ($id) => [
                'circular_id'    => $circular->id,
                'institution_id' => $id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ])->toArray();

            if (!empty($rows)) {
                DB::table('circular_reads')->insert($rows);
            }

            activity()->log("Published circular: {$circular->title}");

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Failed to publish circular.');
            throw $e;
        }

        $this->dispatch('toast', type: 'success', message: 'Circular published successfully.');

        return redirect()->route('ministry.circulars.index');
    }

    public function render()
    {
        return view('livewire.ministry.circular.create-component')
            ->layout('layouts.ministry.app', [
                'title' => 'Publish Circular | ' . setting('app_name', 'EMS'),
            ]);
    }
}