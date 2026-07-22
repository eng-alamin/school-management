<?php

namespace App\Livewire\Admin\Setting;

use Livewire\Component;
use App\Models\Feature as InstitutionFeature;
use App\Support\Feature;


class FeatureComponent extends Component
{
    public array $features = [];

    public function mount(): void
    {
        $this->loadFeatures();
    }
    private function loadFeatures(): void
    {
        $institutionId = auth()->user()->institution_id;

        $saved = InstitutionFeature::where('institution_id', $institutionId)
            ->pluck('is_active', 'feature_key')
            ->toArray();

        $this->features = [];

        foreach (Feature::all() as $key => $label) {
            $this->features[$key] = [
                'label'     => $label,
                'is_active' => $saved[$key] ?? false,
            ];
        }
    }

    public function toggleFeature(string $featureKey): void
    {
        if (!array_key_exists($featureKey, Feature::all())) {
            $this->dispatch('toast', type: 'error', message: 'Invalid feature.');
            return;
        }

        $institutionId = auth()->user()->institution_id;

        $feature = InstitutionFeature::firstOrCreate(
            [
                'institution_id' => $institutionId,
                'feature_key'    => $featureKey,
            ],
            ['is_active' => true]
        );

        $feature->update(['is_active' => !$feature->is_active]);

        activity()
            ->performedOn($feature)
            ->causedBy(auth()->user())
            ->withProperties(['icon' => 'tune', 'type' => 'feature_toggle'])
            ->log($feature->is_active ? "{$featureKey} activated" : "{$featureKey} deactivated");

        $this->loadFeatures();

        $this->dispatch('toast', type: 'success', message: 'Feature status updated!');
    }

    public function render()
    {
        return view('livewire.admin.setting.feature-component')
            ->layout('layouts.admin.app', [
                'title' => 'Feature Control | ' . institution()->name,
            ]);
    }
}
