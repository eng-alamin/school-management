<?php

namespace App\Mail;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

class AdmissionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{
     *     student: array{username: string, email: ?string, password: string, has_email: bool},
     *     guardian: array{username: string, email: string, password: ?string, is_new: bool}
     * } $credentials
     * @param string $recipientRole 'guardian' | 'student' — kar credentials ei
     *     specific mail-e dekhano hobe seta thik kore.
     */
    public function __construct(
        public Admission $admission,
        public array $credentials = [],
        public string $recipientRole = 'guardian',
    ) {
        if (!in_array($this->recipientRole, ['guardian', 'student'], true)) {
            throw new InvalidArgumentException(
                "AdmissionApprovedMail: recipientRole must be 'guardian' or 'student', got '{$this->recipientRole}'."
            );
        }
    }

    public function build()
    {
        $recipientCredentials = $this->credentials[$this->recipientRole] ?? [];

        return $this->subject('Admission Application Approved')
            ->view('emails.admission.approved', [
                'admission'            => $this->admission,
                'recipientRole'        => $this->recipientRole,
                'recipientCredentials' => $recipientCredentials,
            ]);
    }
}