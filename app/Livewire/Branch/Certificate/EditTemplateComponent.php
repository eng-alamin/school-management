<?php

namespace App\Livewire\Branch\Certificate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditTemplateComponent extends Component
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

    public CertificateTemplate $template;
    public int $templateId;

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

        'one' => '<div style="font-family:Georgia,serif;padding:20px;">
    <div style="text-align:center;">
        <strong style="font-size:16px;">{institute_name}</strong><br>
        {institute_email} | {institute_mobile}<br>
        {institute_address}
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin:20px 0;">
        <div>Registration No: {registration_no}</div>
        <div>{logo}</div>
        <div>Admission On: {admission_date}</div>
    </div>
    <h2 style="text-align:center;letter-spacing:1px;margin:20px 0;">SCHOOL CERTIFICATE EXAMINATION-2020</h2>
    <p style="font-size:15px;line-height:1.9;text-align:justify;">
        This Is To Certify That {name} And Of He/she Is Gender As {gender} Son/daughter Of {father_name} &amp; {mother_name}
        Of {institute_name} And {institute_address} Bearing Roll- {roll} &amp; Exam Center Campus-04 And Class {class}
        Dulg Passed The Final school Certificate Examination Of 2020 {group} And His/her Date Of Birth As Recorded
        Is {birthday} Obtained G.p.a 4.94 In The Scale Of 5.00.
    </p>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:30px;">
        <div>Date of Publication of Result: {print_date}</div>
        <div>{qr_code}</div>
    </div>
</div>',

        'two' => '<div style="border:3px double #7a1f1f;font-family:Georgia,serif;padding:25px;">
    <div style="text-align:center;">
        <strong style="font-size:16px;">{institute_name}</strong><br>
        {institute_email} | {institute_mobile}<br>
        {institute_address}
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin:20px 0;">
        <div>Registration No: {registration_no}</div>
        <div>{logo}</div>
        <div>Admission On: {admission_date}</div>
    </div>
    <h2 style="text-align:center;letter-spacing:1px;margin:20px 0;">SCHOOL CERTIFICATE EXAMINATION-2020</h2>
    <p style="font-size:15px;line-height:1.9;text-align:justify;">
        This Is To Certify That {name} And Of He/she Is Gender As {gender} Son/daughter Of {father_name} &amp; {mother_name}
        Of {institute_name} And {institute_address} Bearing Roll- {roll} &amp; Exam Center Campus-04 And Class {class}
        Dulg Passed The Final school Certificate Examination Of 2020 {group} And His/her Date Of Birth As Recorded
        Is {birthday} Obtained G.p.a 4.94 In The Scale Of 5.00.
    </p>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:30px;">
        <div>Date of Publication of Result: {print_date}</div>
        <div>{qr_code}</div>
    </div>
</div>',

        'three' => '<div style="padding:40px;font-family:\'Times New Roman\',serif;text-align:center;position:relative;background:#fefefe;border:1px solid #ccc;">
    <h3 style="margin:0;color:#555;font-size:13px;">{institute_name}</h3>
    <p style="margin:2px 0;font-size:12px;color:#777;">{institute_address}</p>
    <p style="margin:0 0 20px;font-size:12px;color:#777;">{institute_email} | {institute_mobile}</p>

    <h1 style="font-size:28px;letter-spacing:4px;color:#2c2c2c;margin:20px 0;">CERTIFICATE</h1>
    <p style="font-size:13px;color:#999;margin-bottom:25px;">Registration No: {registration_no} &nbsp;|&nbsp; Admission On: {admission_date}</p>

    <p style="font-size:16px;line-height:2;max-width:650px;margin:0 auto;">
        Presented to <strong>{name}</strong>, S/D of {father_name} &amp; {mother_name}, studying in Class {class}
        (Section: {section}), bearing Roll No. {roll}. Gender: {gender}, Religion: {religion},
        Date of Birth: {birthday}. Awarded in recognition of excellent performance and conduct.
    </p>

    <div style="margin-top:50px;display:flex;justify-content:space-around;font-size:13px;">
        <div>_____________________<br>Date: {print_date}</div>
        <div>_____________________<br>Authorized Signature</div>
    </div>
</div>',

        'four' => '<div style="padding:30px;font-family:\'Segoe UI\',Arial,sans-serif;">
    <div style="display:flex;justify-content:space-between;border-bottom:3px solid #16a34a;padding-bottom:12px;margin-bottom:25px;">
        <div>
            <div style="font-size:18px;font-weight:700;color:#16a34a;">{institute_name}</div>
            <div style="font-size:12px;color:#666;">{institute_address}</div>
            <div style="font-size:12px;color:#666;">{institute_email} | {institute_mobile}</div>
        </div>
        <div style="text-align:right;font-size:12px;color:#666;">
            Reg No: {registration_no}<br>Admission: {admission_date}
        </div>
    </div>

    <h2 style="color:#16a34a;font-size:20px;margin-bottom:15px;">Certificate of Recognition</h2>

    <p style="font-size:14.5px;line-height:1.8;color:#333;">
        This is to certify that <strong>{name}</strong> (Gender: {gender}), Son/Daughter of {father_name} and {mother_name},
        a student of Class {class}, Section {section}, Roll {roll}, Blood Group {blood}, born on {birthday}, Religion {religion},
        has fulfilled all the requirements set by {institute_name}.
    </p>

    <div style="display:flex;justify-content:space-between;margin-top:60px;font-size:13px;color:#444;">
        <div>Issued on: {print_date}</div>
        <div>Signature &amp; Seal</div>
    </div>
</div>',

        'five' => '<div style="padding:35px;font-family:Georgia,serif;border:6px solid #7a1f1f;text-align:center;">
    <div style="font-size:14px;color:#333;margin-bottom:5px;">
        <strong>{institute_name}</strong> &nbsp;|&nbsp; {institute_email} &nbsp;|&nbsp; {institute_mobile}
    </div>
    <div style="font-size:12px;color:#666;margin-bottom:20px;">{institute_address}</div>

    <h2 style="font-size:24px;color:#7a1f1f;letter-spacing:2px;margin-bottom:5px;">SCHOOL CERTIFICATE EXAMINATION</h2>
    <p style="font-size:12px;color:#777;margin-bottom:20px;">
        Registration No: {registration_no} &nbsp;&nbsp; Admission On: {admission_date}
    </p>

    <p style="font-size:15px;line-height:1.9;text-align:justify;max-width:680px;margin:0 auto;">
        This is to certify that <strong>{name}</strong>, Gender: <strong>{gender}</strong>, Son/Daughter of
        <strong>{father_name}</strong> &amp; <strong>{mother_name}</strong>, of {institute_name}, {institute_address},
        bearing Roll No. <strong>{roll}</strong> and Class <strong>{class}</strong> (Section: {section}),
        has passed the final examination with satisfactory academic standing.
        Date of Birth: <strong>{birthday}</strong>, Religion: <strong>{religion}</strong>, Blood Group: <strong>{blood}</strong>.
    </p>

    <div style="display:flex;justify-content:space-between;margin-top:55px;font-size:13px;">
        <div>Date of Publication: {print_date}</div>
        <div>Controller of Examinations</div>
    </div>
</div>',
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

    public function mount(int $id): void
    {
        $t = CertificateTemplate::findOrFail($id);
        $this->template   = $t;
        $this->templateId = $id;

        $this->certificate_name    = $t->certificate_name;
        $this->applicable_user     = $t->applicable_user;
        $this->qr_code_text        = $t->qr_code_text        ?? self::DEFAULT_QR_CODE;
        $this->photo_style         = $t->photo_style         ?? self::DEFAULT_PHOTO_STYLE;
        $this->certificate_content = $t->certificate_content ?? '';

        // ── Reverse-map real DB columns back into friendly presets ──
        $layout = $t->page_layout ?? 'a4_portrait';
        $parts  = explode('_', $layout, 2);
        $this->paperSize   = in_array($parts[0] ?? 'a4', ['a4', 'a5'], true) ? $parts[0] : 'a4';
        $this->orientation = in_array($parts[1] ?? 'portrait', ['portrait', 'landscape'], true) ? $parts[1] : 'portrait';

        $this->spacing = $this->closestPresetKey(self::SPACING_MAP, $t->margin_top ?? 80, 'normal');
        $this->photoSizePreset = $this->closestPresetKey(self::PHOTO_SIZE_MAP, $t->photo_size ?? 100, 'medium');

        $this->recalculatePageLayout();
        $this->recalculateSpacing();
        $this->recalculatePhotoSize();

        // Preserve the actual stored values rather than silently rounding
        // them to a preset — presets only drive *future* changes.
        $this->margin_top    = $t->margin_top    ?? $this->margin_top;
        $this->margin_right  = $t->margin_right  ?? $this->margin_right;
        $this->margin_bottom = $t->margin_bottom ?? $this->margin_bottom;
        $this->margin_left   = $t->margin_left   ?? $this->margin_left;
        $this->photo_size    = $t->photo_size    ?? $this->photo_size;
    }

    private function closestPresetKey(array $map, int $value, string $fallback): string
    {
        $closest = null;
        $smallestDiff = null;

        foreach ($map as $key => $presetValue) {
            $diff = abs($presetValue - $value);
            if ($smallestDiff === null || $diff < $smallestDiff) {
                $smallestDiff = $diff;
                $closest = $key;
            }
        }

        return $closest ?? $fallback;
    }

    protected function rules(): array
    {
        return [
            'certificate_name'    => 'required|string|max:255|unique:certificate_templates,certificate_name,' . $this->templateId,
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

    // ── Save (update existing template) ──
    public function update(): void
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
                    $this->deleteOldFile($this->template->$field);
                    $data[$field] = $this->storeUploadedFile($this->$field, $field);
                }
            }

            $this->template->update($data);

            activity()
                ->performedOn($this->template)
                ->withProperties(['certificate_name' => $this->template->certificate_name])
                ->log('Certificate template updated: ' . $this->template->certificate_name);
        });

        $this->dispatch('toast', type: 'success', message: 'Certificate template updated successfully!');
    }

    /**
     * When update() fails validation, jump the wizard to whichever step owns
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

    private function deleteOldFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $fullPath = public_path($relativePath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    public function render()
    {
        return view('livewire.admin.certificate.edit-template-component')
            ->layout('layouts.branch.app', [
                'title' => 'Edit Certificate Template | ' . institution()->name,
            ]);
    }
}