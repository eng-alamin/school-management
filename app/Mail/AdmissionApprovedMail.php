<?php

namespace App\Mail;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdmissionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{
     *     student: array{username: string, email: ?string, password: string},
     *     guardian: array{username: string, email: string, password: ?string, is_new: bool}
     * } $credentials
     */
    public function __construct(
        public Admission $admission,
        public array $credentials = [],
    ) {
    }

    public function build()
    {
        return $this->subject('Admission Application Approved')
            ->view('emails.admission.approved');
    }
}