{{-- resources/views/livewire/ministry/circular/create-component.blade.php --}}

<div class="notice-create-wrap">

    <div class="px-3 pt-3">
        <a href="{{ route('ministry.circulars.index') }}" class="inst-back-link">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_back</span>
            <span data-en="Back to Circulars" data-bn="পরিপত্রে ফিরে যান">Back to Circulars</span>
        </a>
    </div>

    <div class="px-3 pt-3 pb-4">
        <form wire:submit="save" class="dash-section-card">

            <div class="dash-section-title">
                <span data-en="Publish New Circular" data-bn="নতুন পরিপত্র প্রকাশ করুন">Publish New Circular</span>
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-size:13px;" data-en="Title" data-bn="শিরোনাম">Title</label>
                <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-size:13px;" data-en="Description" data-bn="বিবরণ">Description</label>
                <textarea wire:model="description" rows="5"
                          class="form-control @error('description') is-invalid @enderror"></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-size:13px;" data-en="Attachment (optional)" data-bn="সংযুক্তি (ঐচ্ছিক)">Attachment (optional)</label>
                <input type="file" wire:model="attachment" class="form-control @error('attachment') is-invalid @enderror">
                @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div wire:loading wire:target="attachment" class="text-secondary mt-1" style="font-size:12px;" data-en="Uploading..." data-bn="আপলোড হচ্ছে...">
                    Uploading...
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-size:13px;" data-en="Expires At (optional)" data-bn="মেয়াদ শেষ (ঐচ্ছিক)">Expires At (optional)</label>
                <input type="date" wire:model="expiresAt" class="form-control @error('expiresAt') is-invalid @enderror">
                @error('expiresAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-size:13px;" data-en="Audience" data-bn="প্রাপক">Audience</label>
                <select wire:model.live="audience" class="form-select @error('audience') is-invalid @enderror">
                    <option value="all" data-en="All Institutions" data-bn="সকল প্রতিষ্ঠান">All Institutions</option>
                    <option value="division" data-en="Specific Division" data-bn="নির্দিষ্ট বিভাগ">Specific Division</option>
                    <option value="district" data-en="Specific District" data-bn="নির্দিষ্ট জেলা">Specific District</option>
                    <option value="institution" data-en="Specific Institution" data-bn="নির্দিষ্ট প্রতিষ্ঠান">Specific Institution</option>
                </select>
                @error('audience') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if($audience === 'division')
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;" data-en="Division" data-bn="বিভাগ">Division</label>
                    <select wire:model="division" class="form-select @error('division') is-invalid @enderror">
                        <option value="" data-en="Select Division" data-bn="বিভাগ নির্বাচন করুন">Select Division</option>
                        @foreach($this->divisions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('division') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif

            @if($audience === 'district')
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;" data-en="District" data-bn="জেলা">District</label>
                    <select wire:model="district" class="form-select @error('district') is-invalid @enderror">
                        <option value="" data-en="Select District" data-bn="জেলা নির্বাচন করুন">Select District</option>
                        @foreach($this->districts as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif

            @if($audience === 'institution')
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;" data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</label>
                    <select wire:model="institutionId" class="form-select @error('institutionId') is-invalid @enderror">
                        <option value="" data-en="Select Institution" data-bn="প্রতিষ্ঠান নির্বাচন করুন">Select Institution</option>
                        @foreach($this->institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                    @error('institutionId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endif

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save" data-en="Publish Circular" data-bn="পরিপত্র প্রকাশ করুন">Publish Circular</span>
                    <span wire:loading wire:target="save" data-en="Publishing..." data-bn="প্রকাশ হচ্ছে...">Publishing...</span>
                </button>
                <a href="{{ route('ministry.circulars.index') }}" class="btn btn-sm btn-outline-secondary" data-en="Cancel" data-bn="বাতিল">Cancel</a>
            </div>

        </form>
    </div>

</div>

@push('styles')
<style>
    .notice-create-wrap { background: var(--body-bg); min-height: 100vh; }
    .inst-back-link {
        font-size: 12px; color: var(--lbl); text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .dash-section-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 18px; box-shadow: var(--shadow);
        max-width: 640px;
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: var(--val);
        display: flex; align-items: center; gap: 6px; margin-bottom: 16px;
    }
</style>
@endpush