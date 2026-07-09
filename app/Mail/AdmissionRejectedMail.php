<?php

namespace App\Mail;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class AdmissionRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $editUrl;

    public function __construct(public Admission $admission)
    {
        $this->editUrl = URL::signedRoute('admission.edit', [
            'admission' => $admission->id,
        ]);
    }

    public function build()
    {
        return $this->subject('Admission Application Update')
            ->view('emails.admission.rejected');
    }
}