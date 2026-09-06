<?php

namespace App\Services;

use App\Models\QuestionPaperPrintAuthorization;
use App\Models\QuestionPaperPrintLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Owns the forensic print ledger: every time a paper is actually printed,
 * this mints a unique `watermark_code` and writes one row to
 * question_paper_print_logs *before* the PDF is rendered, so the code that
 * ends up embedded in the PDF (visibly + via the word-spacing stego layer)
 * always has a matching DB record to trace back to — a code is never
 * generated without a log row, and vice versa.
 */
class QuestionPaperWatermarkService
{
    /**
     * Log the print event and mint its watermark_code.
     *
     * $authorization is the print-window row that authorized this action
     * (already verified as currently-open by the caller). It tells us
     * exam_id/subject_id/institution_id/branch_id for the log row.
     */
    public function authorizeAndLogPrint(
        QuestionPaperPrintAuthorization $authorization,
        int $institutionId,
        int $branchId,
        ?string $ipAddress,
        ?string $deviceFingerprint
    ): QuestionPaperPrintLog {
        $copyCount = QuestionPaperPrintLog::query()
            ->where('print_authorization_id', $authorization->id)
            ->count() + 1;

        return QuestionPaperPrintLog::create([
            'institution_id' => $institutionId,
            'branch_id' => $branchId,
            'exam_id' => $authorization->exam_id,
            'subject_id' => $authorization->subject_id,
            'printed_by' => Auth::id(),
            'print_authorization_id' => $authorization->id,
            'watermark_code' => $this->generateUniqueCode(),
            'copy_count' => $copyCount,
            'ip_address' => $ipAddress,
            'device_fingerprint' => $deviceFingerprint,
            'printed_at' => now(),
        ]);
    }

    /**
     * Short, human-typeable, collision-checked code (e.g. "QP-7F3K9-2LX8").
     * Kept short on purpose — this is the string that gets visibly printed
     * on the page and manually typed in during a leak investigation, so it
     * has to stay easy to read off a photo/photocopy.
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = 'QP-' . strtoupper(Str::random(5)) . '-' . strtoupper(Str::random(4));
        } while (QuestionPaperPrintLog::where('watermark_code', $code)->exists());

        return $code;
    }
}
