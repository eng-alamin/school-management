<?php

namespace App\Services;

use App\Models\QuestionPaperPrintLog;

/**
 * Produces a per-print question ordering when an exam has randomization
 * turned on, and records exactly which ordering + seed a given printed
 * copy received — so if a leaked copy surfaces, the order it was printed
 * in can be matched back to a specific print log / watermark_code.
 *
 * Each $questions entry passed in is expected to be:
 *   ['id' => int, 'section' => string, 'text' => string]
 * (see PrintQuestionPaperComponent::flattenQuestionText()).
 */
class QuestionOrderShuffleService
{
    /**
     * Shuffle within each section, but never across sections — a "MCQ"
     * section must stay a contiguous MCQ block on the printed page
     * regardless of randomization, only the question order *inside* it
     * varies per copy.
     *
     * @param array<int, array{id:int,section:string,text:string}> $questions
     * @return array{questions: array, question_order: array<int>, seed: int}
     */
    public function shuffleForPrint(array $questions): array
    {
        $seed = random_int(1, PHP_INT_MAX);

        $bySection = [];
        foreach ($questions as $question) {
            $bySection[$question['section']][] = $question;
        }

        $shuffled = [];
        $seedOffset = 0;
        foreach ($bySection as $section => $sectionQuestions) {
            $shuffled = array_merge($shuffled, $this->seededShuffle($sectionQuestions, $seed + $seedOffset));
            $seedOffset++;
        }

        return [
            'questions' => $shuffled,
            'question_order' => array_column($shuffled, 'id'),
            'seed' => $seed,
        ];
    }

    public function recordDistribution(QuestionPaperPrintLog $log, array $questionOrder, int $seed): void
    {
        $log->update([
            'question_order' => $questionOrder,
            'shuffle_seed' => $seed,
        ]);
    }

    /**
     * Fisher-Yates shuffle driven by mt_srand($seed) so the same seed always
     * reproduces the same order — needed to regenerate/verify a specific
     * printed copy's layout during a leak investigation.
     */
    private function seededShuffle(array $items, int $seed): array
    {
        mt_srand($seed);

        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        mt_srand(); // reseed from entropy so nothing else in the request is affected

        return $items;
    }
}
