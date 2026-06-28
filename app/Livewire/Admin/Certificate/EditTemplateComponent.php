<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;

class EditTemplateComponent extends Component
{
    use WithFileUploads;

    public CertificateTemplate $template;
    public int $templateId;

    public string $certificate_name = '';
    public string $applicable_user  = '';
    public string $page_layout      = 'a4_portrait';
    public string $qr_code_text     = 'register_no';
    public string $photo_style      = 'square';
    public int    $photo_size       = 100;

    public int $margin_top    = 80;
    public int $margin_right  = 80;
    public int $margin_bottom = 80;
    public int $margin_left   = 80;

    public string $certificate_content = '';

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
        $this->page_layout         = $t->page_layout;
        $this->qr_code_text        = $t->qr_code_text        ?? 'register_no';
        $this->photo_style         = $t->photo_style         ?? 'square';
        $this->photo_size          = $t->photo_size          ?? 100;
        $this->margin_top          = $t->margin_top          ?? 80;
        $this->margin_right        = $t->margin_right        ?? 80;
        $this->margin_bottom       = $t->margin_bottom       ?? 80;
        $this->margin_left         = $t->margin_left         ?? 80;
        $this->certificate_content = $t->certificate_content ?? '';
    }

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
            'signature_image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'background_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    protected array $messages = [
        'certificate_name.required'    => 'Certificate name is required.',
        'applicable_user.required'     => 'Please select who this certificate applies to.',
        'page_layout.required'         => 'Please select a page layout.',
        'certificate_content.required' => 'Certificate content cannot be empty.',
        'signature_image.image'        => 'Signature must be an image.',
        'signature_image.mimes'        => 'Signature must be JPG or PNG.',
        'signature_image.max'          => 'Signature must not exceed 2MB.',
        'logo_image.image'             => 'Logo must be an image.',
        'logo_image.mimes'             => 'Logo must be JPG or PNG.',
        'logo_image.max'               => 'Logo must not exceed 2MB.',
        'background_image.image'       => 'Background must be an image.',
        'background_image.mimes'       => 'Background must be JPG or PNG.',
        'background_image.max'         => 'Background must not exceed 2MB.',
    ];

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

        foreach (['signature_image', 'logo_image', 'background_image'] as $field) {
            if ($this->$field) {
                $data[$field] = $this->$field->store('certificates', 'public');
            }
        }

        $this->template->update($data);

        $this->dispatch('toast', type: 'success', message: 'Certificate template updated successfully!');
    }

    public function render()
    {
        return view('livewire.admin.certificate.edit-template-component')
            ->layout('layouts.admin.app', [
                'title' => 'Edit Certificate Template | ' . institution()->name,
            ]);
    }
}