<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddTemplateComponent extends Component
{
    use WithFileUploads;

    // ── Default constants (DRY: single source of truth) ──
    private const DEFAULT_QR_CODE     = 'registration_no';
    private const DEFAULT_PHOTO_STYLE = 'square';
    private const UPLOAD_FOLDER       = 'uploads/certificates';

    // ── Preset → real-column maps (keeps DB columns/validation unchanged) ──
    private const PHOTO_SIZE_MAP = ['small' => 70, 'medium' => 100, 'large' => 140];
    private const SPACING_MAP    = ['compact' => 40, 'normal' => 80, 'spacious' => 120];

    // ── Wizard step (UI-only, not persisted) ──
    public int $step = 1;
    public int $totalSteps = 4;

    // ── Basic Info (Step 1) ──
    public string $certificate_name = '';
    public string $applicable_user  = '';

    // ── Friendly presets shown to the user (Step 3) ──
    public string $paperSize        = 'a4';       // a4 | a5
    public string $orientation      = 'portrait'; // portrait | landscape
    public string $spacing          = 'normal';   // compact | normal | spacious
    public string $photoSizePreset  = 'medium';   // small | medium | large
    public string $photo_style      = self::DEFAULT_PHOTO_STYLE;
    public string $qr_code_text     = self::DEFAULT_QR_CODE;

    // ── Real DB-bound values, computed from the presets above ──
    public string $page_layout   = 'a4_portrait';
    public int    $photo_size    = 100;
    public int    $margin_top    = 80;
    public int    $margin_right  = 80;
    public int    $margin_bottom = 80;
    public int    $margin_left   = 80;

    // ── Content (Step 4) ──
    public string $certificate_content = '';

    // ── Design Selector ──
    public string $selectedDesign = '';

    public array $designs = [
        'one' => '<div>design one</div>',
        'two' => '<div>design two</div>',
        'three' => '<div>design three</div>',
        'four' => '<div>design four</div>',
        'five' => '<div>design five</div>',
    ];

    public array $designMeta = [
        'one'   => ['title' => 'Classic',  'sub' => 'Simple layout',   'color' => '#2563eb'],
        'two'   => ['title' => 'Bordered', 'sub' => 'Double border',   'color' => '#7a1f1f'],
        'three' => ['title' => 'Elegant',  'sub' => 'Formal seal',     'color' => '#4b5563'],
        'four'  => ['title' => 'Modern',   'sub' => 'Minimal style',   'color' => '#16a34a'],
        'five'  => ['title' => 'Formal',   'sub' => 'Bold signature',  'color' => '#7a1f1f'],
    ];

    // ── File Uploads ──
    public $signature_image  = null;
    public $logo_image       = null;
    public $background_image = null;

    public function mount(): void
    {
        $this->recalculatePageLayout();
        $this->recalculateSpacing();
        $this->recalculatePhotoSize();
    }

    protected function rules(): array
    {
        return [
            'certificate_name'    => 'required|string|max:255|unique:certificate_templates,certificate_name',
            'applicable_user'     => 'required|in:student,employee',
            'page_layout'         => 'required|in:a4_portrait,a4_landscape,a5_portrait,a5_landscape',
            'qr_code_text'        => 'required|in:registration_no,roll_no,name,email,mobile',
            'photo_style'         => 'required|in:square,circle',
            'photo_size'          => 'required|integer|min:50|max:300',
            'margin_top'          => 'required|integer|min:0|max:300',
            'margin_right'        => 'required|integer|min:0|max:300',
            'margin_bottom'       => 'required|integer|min:0|max:300',
            'margin_left'         => 'required|integer|min:0|max:300',
            'certificate_content' => 'required|string',

            'signature_image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'background_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    protected function messages(): array
    {
        return [
            'certificate_name.required'    => 'Please give this template a name.',
            'certificate_name.unique'      => 'A template with this name already exists. Please choose another name.',
            'applicable_user.required'     => 'Please select who this certificate is for (Student or Employee).',
            'page_layout.required'         => 'Please select a paper size.',
            'certificate_content.required' => 'Certificate content cannot be empty.',
            'signature_image.image'        => 'Signature must be an image file.',
            'signature_image.mimes'        => 'Signature must be a JPG or PNG file.',
            'signature_image.max'          => 'Signature image must not exceed 2MB.',
            'logo_image.image'             => 'Logo must be an image file.',
            'logo_image.mimes'             => 'Logo must be a JPG or PNG file.',
            'logo_image.max'               => 'Logo image must not exceed 2MB.',
            'background_image.image'       => 'Background must be an image file.',
            'background_image.mimes'       => 'Background must be a JPG or PNG file.',
            'background_image.max'         => 'Background image must not exceed 2MB.',
        ];
    }

    public function updatedPaperSize(): void
    {
        $this->recalculatePageLayout();
    }

    public function updatedOrientation(): void
    {
        $this->recalculatePageLayout();
    }

    public function updatedSpacing(): void
    {
        $this->recalculateSpacing();
    }

    public function updatedPhotoSizePreset(): void
    {
        $this->recalculatePhotoSize();
    }

    private function recalculatePageLayout(): void
    {
        $paper = in_array($this->paperSize, ['a4', 'a5'], true) ? $this->paperSize : 'a4';
        $orient = in_array($this->orientation, ['portrait', 'landscape'], true) ? $this->orientation : 'portrait';
        $this->page_layout = $paper . '_' . $orient;
    }

    private function recalculateSpacing(): void
    {
        $px = self::SPACING_MAP[$this->spacing] ?? self::SPACING_MAP['normal'];
        $this->margin_top = $this->margin_right = $this->margin_bottom = $this->margin_left = $px;
    }

    private function recalculatePhotoSize(): void
    {
        $this->photo_size = self::PHOTO_SIZE_MAP[$this->photoSizePreset] ?? self::PHOTO_SIZE_MAP['medium'];
    }

    /**
     * Called when a design card is clicked (Step 2). Sets certificate_content
     * directly server-side so it never depends on Summernote already being
     * mounted in the DOM (which previously only happened once Step 4 rendered).
     */
    public function selectDesign(string $key): void
    {
        if (! array_key_exists($key, $this->designs)) {
            return;
        }

        $this->selectedDesign = $key;
        $this->certificate_content = $this->designs[$key];

        // Tell the JS side to push this same content into Summernote if it's
        // already mounted, and to (re)mount it if we're now on/near Step 4.
        $this->dispatch('designContentUpdated', content: $this->designs[$key]);
    }

    // ── Wizard navigation (client feels like a simple guided form) ──
    public function goToStep(int $target): void
    {
        if ($target < 1) {
            $target = 1;
        }
        if ($target > $this->totalSteps) {
            $target = $this->totalSteps;
        }

        if ($target > $this->step) {
            for ($s = $this->step; $s < $target; $s++) {
                if (! $this->validateStep($s)) {
                    return;
                }
            }
        }

        $this->step = $target;

        // Whenever we land on Step 4, make sure Summernote reflects whatever
        // certificate_content currently holds (design pick or manual edits).
        if ($this->step === 4) {
            $this->dispatch('ensureEditorSynced', content: $this->certificate_content);
        }
    }

    public function nextStep(): void
    {
        $this->goToStep($this->step + 1);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->resetValidation();
    }

    private function validateStep(int $step): bool
    {
        if ($step === 1) {
            $this->validate([
                'certificate_name' => $this->rules()['certificate_name'],
                'applicable_user'  => $this->rules()['applicable_user'],
            ]);
            return true;
        }

        if ($step === 2 && $this->certificate_content === '' && $this->selectedDesign === '') {
            return $this->failStep('Please pick a design, or move on and write your own content.');
        }

        return true;
    }

    private function failStep(string $message): bool
    {
        $this->dispatch('toast', type: 'error', message: $message);
        return false;
    }

    // ── Save ──
    public function save(): void
    {
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $this->handleValidationFailure($e);
            throw $e;
        }

        DB::transaction(function () use ($validated) {

            $data = [
                'certificate_name'    => $validated['certificate_name'],
                'applicable_user'     => $validated['applicable_user'],
                'page_layout'         => $validated['page_layout'],
                'qr_code_text'        => $validated['qr_code_text'],
                'photo_style'         => $validated['photo_style'],
                'photo_size'          => $validated['photo_size'],
                'margin_top'          => $validated['margin_top'],
                'margin_right'        => $validated['margin_right'],
                'margin_bottom'       => $validated['margin_bottom'],
                'margin_left'         => $validated['margin_left'],
                'certificate_content' => $validated['certificate_content'],
            ];

            foreach (['signature_image', 'logo_image', 'background_image'] as $field) {
                if ($this->$field) {
                    $data[$field] = $this->storeUploadedFile($this->$field, $field);
                }
            }

            $template = CertificateTemplate::create($data);

            activity()
                ->performedOn($template)
                ->withProperties(['certificate_name' => $template->certificate_name])
                ->log('Certificate template created: ' . $template->certificate_name);
        });

        $this->dispatch('toast', type: 'success', message: 'Certificate template created successfully!');
        $this->resetForm();
    }

    /**
     * When save() fails validation, jump the wizard to whichever step owns
     * the first failing field and show a clear toast — instead of silently
     * failing on a step the user isn't currently looking at.
     */
    private function handleValidationFailure(ValidationException $e): void
    {
        $errors = $e->validator->errors();

        $fieldToStep = [
            'certificate_name'    => 1,
            'applicable_user'     => 1,
            'page_layout'         => 3,
            'qr_code_text'        => 3,
            'photo_style'         => 3,
            'photo_size'          => 3,
            'margin_top'          => 3,
            'margin_right'        => 3,
            'margin_bottom'       => 3,
            'margin_left'         => 3,
            'signature_image'     => 3,
            'logo_image'          => 3,
            'background_image'    => 3,
            'certificate_content' => 4,
        ];

        $firstField = collect($errors->keys())->first();
        $targetStep = $fieldToStep[$firstField] ?? 1;

        $this->step = $targetStep;

        if ($targetStep === 4) {
            $this->dispatch('ensureEditorSynced', content: $this->certificate_content);
        }

        $this->dispatch('toast', type: 'error', message: $errors->first($firstField) ?: 'Please fix the highlighted errors before saving.');
    }

    private function storeUploadedFile($file, string $field): string
    {
        $folder = self::UPLOAD_FOLDER . '/' . str_replace('_image', '', $field);
        $destinationPath = public_path($folder);

        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($fullPath, file_get_contents($file->getRealPath()));

        return $folder . '/' . $filename;
    }

    // ── Reset ──
    public function resetForm(): void
    {
        $this->reset([
            'certificate_name',
            'applicable_user',
            'paperSize',
            'orientation',
            'spacing',
            'photoSizePreset',
            'photo_style',
            'qr_code_text',
            'certificate_content',
            'signature_image',
            'logo_image',
            'background_image',
            'selectedDesign',
        ]);

        $this->step         = 1;
        $this->paperSize     = 'a4';
        $this->orientation   = 'portrait';
        $this->spacing       = 'normal';
        $this->photoSizePreset = 'medium';
        $this->photo_style   = self::DEFAULT_PHOTO_STYLE;
        $this->qr_code_text  = self::DEFAULT_QR_CODE;

        $this->recalculatePageLayout();
        $this->recalculateSpacing();
        $this->recalculatePhotoSize();

        $this->resetValidation();

        $this->dispatch('resetSummernote');
        $this->dispatch('resetSelects');
    }

    public function render()
    {
        return view('livewire.admin.certificate.add-template-component')
            ->layout('layouts.admin.app', [
                'title' => 'Add Certificate Template | ' . institution()->name,
            ]);
    }
}