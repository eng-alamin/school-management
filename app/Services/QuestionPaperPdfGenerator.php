<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Renders the final printable PDF using the SAME visual design as
 * question-paper-preview-component.blade.php (institute name, exam title,
 * meta row, section titles, marks badges, MCQ option grid, matching-pairs
 * table, stimulus box) — this is not a separate "print" look, it's the
 * preview sheet made real, plus three stacked watermark layers on top of
 * the same $watermarkCode:
 *
 *   1. Visible footer  — plain text, readable at a glance ("QP-XXXXX-XXXX").
 *   2. Diagonal tile   — faint, repeated, covers the whole page; survives a
 *      phone-camera photo even if the footer is cropped out of frame.
 *   3. Word-spacing stego — the watermark code's bits are encoded into tiny
 *      letter-spacing deltas between words in the question/option/stimulus
 *      text itself. Best-effort only: a secondary clue during a leak
 *      investigation, not primary evidence — it can't survive re-typing,
 *      only a direct photocopy/photo of the original layout.
 */
class QuestionPaperPdfGenerator
{
    private const STEGO_NARROW = '0.00em';
    private const STEGO_WIDE = '0.09em';

    private const PRIMARY = '#2f6fed';
    private const HEADING = '#1c2536';
    private const MUTED = '#6b7280';
    private const BORDER = '#d9dee6';

    /**
     * @param array{
     *   institute_name:string, exam_name:string, class_label:?string,
     *   subject_label:?string, full_marks:float|string, time_label:?string,
     *   questions: array<int, array{
     *     id:int, section:string, family:string,
     *     question_text:?string, stimulus_text:?string, marks:float,
     *     figure_path:?string,
     *     options: array<int, array{text:string}>,
     *     matches: array<int, array{left:string,right:string}>,
     *     parts: array<int, array{label:string,text:string,marks:float}>
     *   }>
     * } $data
     */
    public function generate(string $watermarkCode, array $data): string
    {
        $html = $this->buildHtml($watermarkCode, $data);

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    private function buildHtml(string $watermarkCode, array $data): string
    {
        $bits = $this->codeToBits($watermarkCode);
        $bitIndex = 0;

        $sections = [];
        foreach ($data['questions'] as $question) {
            $sections[$question['section']][] = $question;
        }

        $sectionsHtml = '';
        foreach ($sections as $section => $questions) {
            $sectionsHtml .= '<div class="section-title">' . e($section) . '</div>';
            $displayIndex = 0;
            foreach ($questions as $question) {
                $displayIndex++;
                $sectionsHtml .= $this->renderQuestion($question, $displayIndex, $bits, $bitIndex);
            }
        }

        $instituteName = e($data['institute_name'] ?? '');
        $examName = e($data['exam_name'] ?? '');
        $classLabel = e($data['class_label'] ?: '—');
        $subjectLabel = e($data['subject_label'] ?: '—');
        $fullMarks = e($this->trimMarks($data['full_marks'] ?? 0));
        $timeLabel = e($data['time_label'] ?: '—');
        $safeCode = e($watermarkCode);
        $printedAt = e(now()->format('d M Y, h:i A'));

        // Diagonal tiled watermark, tiled manually across the page (dompdf
        // doesn't reliably repeat a CSS background image/text pattern).
        $tiles = '';
        for ($row = 0; $row < 7; $row++) {
            for ($col = 0; $col < 3; $col++) {
                $top = $row * 150;
                $left = $col * 220 - 60;
                $tiles .= "<div class=\"wm-tile\" style=\"top:{$top}px;left:{$left}px;\">{$safeCode}</div>";
            }
        }

        $primary = self::PRIMARY;
        $heading = self::HEADING;
        $muted = self::MUTED;
        $border = self::BORDER;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 46px 46px 60px 46px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: {$heading}; line-height: 1.7; }

    .wm-layer { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -1; }
    .wm-tile {
        position: absolute; font-size: 32px; color: #000; opacity: 0.045;
        transform: rotate(-30deg); white-space: nowrap;
    }

    .sheet-head { text-align: center; border-bottom: 2px solid {$heading}; padding-bottom: 10px; margin-bottom: 12px; }
    .inst-name { font-size: 17px; font-weight: 700; color: {$heading}; }
    .exam-title { font-size: 12px; color: {$muted}; margin-top: 3px; }

    .meta-row {
        display: table; width: 100%; margin-bottom: 16px; padding-bottom: 10px;
        border-bottom: 1px dashed {$border}; font-size: 11px; color: {$muted};
    }
    .meta-row .cell { display: table-cell; text-align: center; }
    .meta-lbl { color: {$heading}; font-weight: 700; }

    .section-title {
        font-weight: 700; font-size: 12.5px; text-align: center; color: {$heading};
        margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 1px solid {$border};
    }

    .q-item { margin-bottom: 13px; }
    .q-row { display: table; width: 100%; }
    .q-text-cell { display: table-cell; }
    .q-marks-cell { display: table-cell; width: 34px; text-align: right; vertical-align: top; }
    .q-num { font-weight: 700; margin-right: 3px; }
    .marks-tag {
        display: inline-block; font-size: 10px; font-weight: 700; color: {$primary};
        background: #eaf1fe; border-radius: 20px; padding: 1px 8px;
    }

    .figure-wrap { margin: 8px 0 4px 18px; }
    .figure-wrap img { max-width: 220px; max-height: 170px; border: 1px solid {$border}; }

    .opts-grid { width: 100%; margin: 5px 0 0 18px; font-size: 11px; }
    .opts-grid .opt-cell { display: inline-block; width: 47%; padding: 1px 0; }

    .match-wrap { margin: 6px 0 0 18px; font-size: 11px; }
    .match-row { margin-bottom: 3px; }
    .match-idx { font-weight: 700; display: inline-block; width: 16px; }

    .stimulus-box {
        background: #f4f6fa; border-left: 3px solid {$primary};
        padding: 8px 12px; margin: 4px 0 8px 18px; font-size: 11px;
    }
    .part-row { display: table; width: 100%; padding-left: 18px; margin-bottom: 3px; font-size: 11px; }
    .part-text-cell { display: table-cell; }
    .part-marks-cell { display: table-cell; width: 34px; text-align: right; }

    .footer-code { position: fixed; bottom: 16px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #888; }
</style>
</head>
<body>
    <div class="wm-layer">{$tiles}</div>

    <div class="sheet-head">
        <div class="inst-name">{$instituteName}</div>
        <div class="exam-title">{$examName}</div>
    </div>

    <div class="meta-row">
        <div class="cell"><span class="meta-lbl">Class:</span> {$classLabel}</div>
        <div class="cell"><span class="meta-lbl">Subject:</span> {$subjectLabel}</div>
        <div class="cell"><span class="meta-lbl">Full Marks:</span> {$fullMarks}</div>
        <div class="cell"><span class="meta-lbl">Time:</span> {$timeLabel}</div>
    </div>

    {$sectionsHtml}

    <div class="footer-code">{$safeCode} &middot; Printed {$printedAt}</div>
</body>
</html>
HTML;
    }

    private function renderQuestion(array $q, int $displayIndex, array $bits, int &$bitIndex): string
    {
        $marksTag = '<span class="marks-tag">' . e($this->trimMarks($q['marks'])) . '</span>';
        $figure = $this->renderFigure($q['figure_path'] ?? null);

        return match ($q['family']) {
            'options' => '<div class="q-item">'
                . $this->questionRow($displayIndex, $q['question_text'], $marksTag, $bits, $bitIndex)
                . $figure
                . $this->renderOptions($q['options'], $bits, $bitIndex)
                . '</div>',

            'matching_pairs' => '<div class="q-item">'
                . $this->questionRow($displayIndex, $q['question_text'], $marksTag, $bits, $bitIndex)
                . $figure
                . $this->renderMatches($q['matches'], $bits, $bitIndex)
                . '</div>',

            'stimulus_parts' => '<div class="q-item">'
                . '<span class="q-num">' . $displayIndex . '.</span>'
                . '<div class="stimulus-box">' . $this->encodeStego((string) $q['stimulus_text'], $bits, $bitIndex) . '</div>'
                . $figure
                . $this->renderParts($q['parts'], $bits, $bitIndex)
                . '</div>',

            default => '<div class="q-item">'
                . $this->questionRow($displayIndex, $q['question_text'], $marksTag, $bits, $bitIndex)
                . $figure
                . '</div>',
        };
    }

    private function questionRow(int $index, ?string $text, string $marksTag, array $bits, int &$bitIndex): string
    {
        return '<div class="q-row">'
            . '<div class="q-text-cell"><span class="q-num">' . $index . '.</span>'
            . $this->encodeStego((string) $text, $bits, $bitIndex) . '</div>'
            . '<div class="q-marks-cell">' . $marksTag . '</div>'
            . '</div>';
    }

    private function renderOptions(array $options, array $bits, int &$bitIndex): string
    {
        if (empty($options)) {
            return '';
        }

        $html = '<div class="opts-grid">';
        foreach ($options as $i => $opt) {
            $label = chr(65 + $i) . ') ';
            $text = $opt['text'] !== '' ? $opt['text'] : '...........';
            $html .= '<span class="opt-cell">' . $label . $this->encodeStego($text, $bits, $bitIndex) . '</span>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderMatches(array $matches, array $bits, int &$bitIndex): string
    {
        if (empty($matches)) {
            return '';
        }

        $html = '<div class="match-wrap">';
        foreach ($matches as $i => $m) {
            $left = $m['left'] !== '' ? $m['left'] : '...........';
            $right = $m['right'] !== '' ? $m['right'] : '...........';
            $html .= '<div class="match-row">'
                . '<span class="match-idx">' . ($i + 1) . '.</span> '
                . $this->encodeStego($left, $bits, $bitIndex)
                . ' &#8594; '
                . $this->encodeStego($right, $bits, $bitIndex)
                . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderParts(array $parts, array $bits, int &$bitIndex): string
    {
        if (empty($parts)) {
            return '';
        }

        $html = '';
        foreach ($parts as $p) {
            $text = $p['text'] !== '' ? $p['text'] : '...........';
            $html .= '<div class="part-row">'
                . '<div class="part-text-cell">' . e($p['label']) . ') ' . $this->encodeStego($text, $bits, $bitIndex) . '</div>'
                . '<div class="part-marks-cell"><span class="marks-tag">' . e($this->trimMarks($p['marks'])) . '</span></div>'
                . '</div>';
        }

        return $html;
    }

    private function renderFigure(?string $figurePath): string
    {
        if (!$figurePath || !Storage::disk('public')->exists($figurePath)) {
            return '';
        }

        // Local filesystem path (not a public URL) so dompdf can embed the
        // image without needing remote-file-fetching enabled.
        $absolutePath = Storage::disk('public')->path($figurePath);

        return '<div class="figure-wrap"><img src="' . e($absolutePath) . '" alt="Figure"></div>';
    }

    private function trimMarks(float|string $marks): string
    {
        return rtrim(rtrim(number_format((float) $marks, 1), '0'), '.');
    }

    /** @return array<int> bit array (0/1) derived from the watermark code's bytes. */
    private function codeToBits(string $code): array
    {
        $bits = [];
        foreach (str_split($code) as $char) {
            foreach (str_split(str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT)) as $bit) {
                $bits[] = (int) $bit;
            }
        }
        return $bits;
    }

    /**
     * Wraps each word in a span whose letter-spacing is nudged by one bit
     * of the watermark code, cycling through the bit stream across the
     * whole document (continues from wherever the previous text field left
     * off, via the shared $bitIndex reference).
     */
    private function encodeStego(string $text, array $bits, int &$bitIndex): string
    {
        $bitCount = count($bits);
        if ($bitCount === 0 || trim($text) === '') {
            return e($text);
        }

        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        foreach ($words as $token) {
            if (trim($token) === '') {
                $out .= $token;
                continue;
            }
            $bit = $bits[$bitIndex % $bitCount];
            $bitIndex++;
            $spacing = $bit === 1 ? self::STEGO_WIDE : self::STEGO_NARROW;
            $out .= '<span style="letter-spacing:' . $spacing . '">' . e($token) . '</span>';
        }

        return $out;
    }
}