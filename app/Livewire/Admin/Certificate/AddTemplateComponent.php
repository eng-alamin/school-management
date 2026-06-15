<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AddTemplateComponent extends Component
{
    use WithFileUploads;

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

    // ── File Uploads ──
    public $signature_image   = null;
    public $logo_image        = null;
    public $background_image  = null;

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
    ];

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

    // ── Save ──
    public function save(): void
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

        // Handle file uploads
        foreach (['signature_image', 'logo_image', 'background_image'] as $field) {
            if ($this->$field) {
                $data[$field] = \App\Helpers\TenantFileHelper::store($this->$field, 'certificates');
            }
        }

        CertificateTemplate::create($data);

        $this->dispatch('toast', type: 'success', message: 'Certificate template created successfully!');
        $this->resetForm();
    }

    // ── Reset ──
    public function resetForm(): void
    {
        $this->reset([
            'certificate_name',
            'applicable_user',
            'certificate_content',
            'signature_image',
            'logo_image',
            'background_image',
        ]);

        $this->dispatch('resetSummernote');

        $this->page_layout      = 'a4_portrait';
        $this->qr_code_text     = 'register_no';
        $this->photo_style      = 'square';
        $this->photo_size       = 100;
        $this->margin_top       = 80;
        $this->margin_right     = 80;
        $this->margin_bottom    = 80;
        $this->margin_left      = 80;
    }

    public function render()
    {
        return view('livewire.admin.certificate.add-template-component')
            ->layout('layouts.admin.app', [
                'title' => 'Add Certificate Template | Monarchy School',
            ]);
    }
}