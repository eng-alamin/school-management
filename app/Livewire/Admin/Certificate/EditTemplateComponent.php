<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;

class EditTemplateComponent extends Component
{
    use WithFileUploads;

    // ── Model ──
    public CertificateTemplate $template;
    public int $templateId;

    // ── Basic Info ──
    public string $certificate_name = '';
    public string $applicable_user  = '';
    public string $page_layout      = 'a4_portrait';
    public string $qr_code_text     = 'register_no';
    public string $photo_style      = 'square';
    public int    $photo_size       = 100;

    // ── Margins ──
    public int $margin_top    = 80;
    public int $margin_right  = 80;
    public int $margin_bottom = 80;
    public int $margin_left   = 80;

    // ── Content ──
    public string $certificate_content = '';

    // ── Existing image paths (from DB) ──
    public ?string $existing_signature_image  = null;
    public ?string $existing_logo_image       = null;
    public ?string $existing_background_image = null;

    // ── New File Uploads ──
    public $signature_image  = null;
    public $logo_image       = null;
    public $background_image = null;

    // ── Mount ──
    public function mount(int $id): void
    {
        $t = CertificateTemplate::findOrFail($id);
        $this->template   = $t;
        $this->templateId = $id;

        $this->certificate_name    = $t->certificate_name;
        $this->applicable_user     = $t->applicable_user;
        $this->page_layout         = $t->page_layout;
        $this->qr_code_text        = $t->qr_code_text        ?? 'register_no';
        $this->photo_style         = $t->photo_style         ?? 'square';
        $this->photo_size          = $t->photo_size          ?? 100;
        $this->margin_top          = $t->margin_top          ?? 80;
        $this->margin_right        = $t->margin_right        ?? 80;
        $this->margin_bottom       = $t->margin_bottom       ?? 80;
        $this->margin_left         = $t->margin_left         ?? 80;
        $this->certificate_content = $t->certificate_content ?? '';

        $this->existing_signature_image  = $t->signature_image;
        $this->existing_logo_image       = $t->logo_image;
        $this->existing_background_image = $t->background_image;
    }

    // ── Validation Rules ──
    protected function rules(): array
    {
        return [
            'certificate_name'    => 'required|string|max:255',
            'applicable_user'     => 'required|in:student,employee',
            'page_layout'         => 'required|in:a4_portrait,a4_landscape,a5_portrait,a5_landscape',
            'qr_code_text'        => 'required|in:register_no,roll_no,name,email,mobile',
            'photo_style'         => 'required|in:square,circle',
            'photo_size'          => 'required|integer|min:50|max:300',
            'margin_top'          => 'required|integer|min:0|max:300',
            'margin_right'        => 'required|integer|min:0|max:300',
            'margin_bottom'       => 'required|integer|min:0|max:300',
            'margin_left'         => 'required|integer|min:0|max:300',
            'certificate_content' => 'required|string',
            'signature_image'     => 'nullable',
            'logo_image'          => 'nullable',
            'background_image'    => 'nullable',
        ];
    }

    protected array $messages = [
        'certificate_name.required'    => 'Certificate name is required.',
        'applicable_user.required'     => 'Please select who this certificate applies to.',
        'page_layout.required'         => 'Please select a page layout.',
        'certificate_content.required' => 'Certificate content cannot be empty.',
        'signature_image.image'        => 'Signature must be an image file.',
        'logo_image.image'             => 'Logo must be an image file.',
        'background_image.image'       => 'Background must be an image file.',
        'signature_image.max'          => 'Signature image must not exceed 2MB.',
        'logo_image.max'               => 'Logo image must not exceed 2MB.',
        'background_image.max'         => 'Background image must not exceed 2MB.',
    ];

    // ── Real-time validation for images ──
    public function updatedLogoImage(): void
    {
        try {
            $this->validateOnly('logo_image');
        } catch (\Throwable $e) {
            // tmp file not ready yet — skip silent, will validate on save
        }
    }

    public function updatedSignatureImage(): void
    {
        try {
            $this->validateOnly('signature_image');
        } catch (\Throwable $e) {
            //
        }
    }

    public function updatedBackgroundImage(): void
    {
        try {
            $this->validateOnly('background_image');
        } catch (\Throwable $e) {
            //
        }
    }

    public function safePreviewUrl($upload): ?string
    {
        if (!$upload) return null;
        try {
            return $upload->temporaryUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function deleteOldFile($path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // ── Update ──
    public function update(): void
    {
        $this->validate();

        $data = [
            'certificate_name'    => $this->certificate_name,
            'applicable_user'     => $this->applicable_user,
            'page_layout'         => $this->page_layout,
            'qr_code_text'        => $this->qr_code_text,
            'photo_style'         => $this->photo_style,
            'photo_size'          => $this->photo_size,
            'margin_top'          => $this->margin_top,
            'margin_right'        => $this->margin_right,
            'margin_bottom'       => $this->margin_bottom,
            'margin_left'         => $this->margin_left,
            'certificate_content' => $this->certificate_content,
        ];

        // ── Handle image uploads ──
        $imageFields = [
            'logo_image'       => 'existing_logo_image',
            'signature_image'  => 'existing_signature_image',
            'background_image' => 'existing_background_image',
        ];

        foreach ($imageFields as $field => $existingField) {
            if ($this->$field) {
                // Delete old file
                if ($this->$existingField) {
                    $this->deleteOldFile($this->$existingField);
                }
                // Store new file
                $path = \App\Helpers\TenantFileHelper::store($this->$field, 'certificates');
                $data[$field]         = $path;
                $this->$existingField = $path;
                $this->$field         = null;
            }
        }

        $this->template->update($data);

        $this->template->refresh();
        $this->existing_logo_image       = $this->template->logo_image;
        $this->existing_signature_image  = $this->template->signature_image;
        $this->existing_background_image = $this->template->background_image;

        $this->dispatch('toast', type: 'success', message: 'Certificate template updated successfully!');
    }

    // ── Remove individual image ──
    public function removeImage(string $field): void
    {
        $existingField = 'existing_' . $field;
        if ($this->$existingField) {
            $this->deleteOldFile($this->$existingField);
            $this->template->update([$field => null]);
            $this->$existingField = null;
        }
        $this->$field = null;
    }

    public function render()
    {
        return view('livewire.admin.certificate.edit-template-component')
            ->layout('layouts.admin.app', [
                'title' => 'Edit Certificate Template | Monarchy School',
            ]);
    }
}