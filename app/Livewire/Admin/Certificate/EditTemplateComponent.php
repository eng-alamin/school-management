<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\DB;

class EditTemplateComponent extends Component
{
    use WithFileUploads;

    // ── Default constants (DRY: single source of truth) ──
    private const DEFAULT_PAGE_LAYOUT = 'a4_portrait';
    private const DEFAULT_QR_CODE     = 'register_no';
    private const DEFAULT_PHOTO_STYLE = 'square';
    private const DEFAULT_PHOTO_SIZE  = 100;
    private const DEFAULT_MARGIN      = 80;
    private const UPLOAD_FOLDER       = 'uploads/certificates';

    public CertificateTemplate $template;
    public int $templateId;

    public string $certificate_name = '';
    public string $applicable_user  = '';
    public string $page_layout      = self::DEFAULT_PAGE_LAYOUT;
    public string $qr_code_text     = self::DEFAULT_QR_CODE;
    public string $photo_style      = self::DEFAULT_PHOTO_STYLE;
    public int    $photo_size       = self::DEFAULT_PHOTO_SIZE;

    public int $margin_top    = self::DEFAULT_MARGIN;
    public int $margin_right  = self::DEFAULT_MARGIN;
    public int $margin_bottom = self::DEFAULT_MARGIN;
    public int $margin_left   = self::DEFAULT_MARGIN;

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
        $this->page_layout         = $t->page_layout         ?? self::DEFAULT_PAGE_LAYOUT;
        $this->qr_code_text        = $t->qr_code_text        ?? self::DEFAULT_QR_CODE;
        $this->photo_style         = $t->photo_style         ?? self::DEFAULT_PHOTO_STYLE;
        $this->photo_size          = $t->photo_size          ?? self::DEFAULT_PHOTO_SIZE;
        $this->margin_top          = $t->margin_top          ?? self::DEFAULT_MARGIN;
        $this->margin_right        = $t->margin_right        ?? self::DEFAULT_MARGIN;
        $this->margin_bottom       = $t->margin_bottom       ?? self::DEFAULT_MARGIN;
        $this->margin_left         = $t->margin_left         ?? self::DEFAULT_MARGIN;
        $this->certificate_content = $t->certificate_content ?? '';
    }

    protected function rules(): array
    {
        return [
            'certificate_name'    => 'required|string|max:255|unique:certificate_templates,certificate_name,' . $this->templateId,
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

    protected function messages(): array
    {
        return [
            'certificate_name.required'    => 'Certificate name is required.',
            'certificate_name.unique'      => 'This certificate name already exists. Please choose another.',
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
    }

    public function update(): void
    {
        $validated = $this->validate();

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
                    // Bug fix: delete old file before storing the new one
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
     * Safely store an uploaded file without relying on Livewire's default
     * store() path resolution (avoids known Windows/XAMPP path issues).
     */
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

    /**
     * Delete previously stored file (if any) so replaced uploads
     * don't leave orphan files on the server.
     */
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
            ->layout('layouts.admin.app', [
                'title' => 'Edit Certificate Template | ' . institution()->name,
            ]);
    }
}