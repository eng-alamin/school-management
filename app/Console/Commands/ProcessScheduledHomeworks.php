<?php

namespace App\Console\Commands;

use App\Models\Homework;
use App\Services\HomeworkNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessScheduledHomeworks extends Command
{
    /**
     * php artisan homework:publish-scheduled
     */
    protected $signature = 'homework:publish-scheduled';

    protected $description = 'Publish homeworks whose "Publish Later" schedule_date has arrived, and notify students & guardians.';

    public function handle(): int
    {
        $dueHomeworks = Homework::where('published_later', true)
            ->where('status', 'draft')
            ->whereNotNull('schedule_date')
            ->where('schedule_date', '<=', now())
            ->get();

        if ($dueHomeworks->isEmpty()) {
            $this->info('No scheduled homeworks due for publishing.');
            return self::SUCCESS;
        }

        foreach ($dueHomeworks as $homework) {
            try {
                DB::transaction(function () use ($homework) {
                    $homework->update([
                        'status'          => 'published',
                        'published_later' => false,
                    ]);

                    activity()
                        ->performedOn($homework)
                        ->log('Homework "' . $homework->title . '" auto-published (scheduled).');
                });

                // Notification পাঠানো হচ্ছে transaction commit হওয়ার পর, যাতে notification fail
                // হলেও publish status rollback না হয়।
                HomeworkNotificationService::notifyStudentsAndGuardians($homework->fresh());

                $this->info("Published homework #{$homework->id}: {$homework->title}");
            } catch (\Throwable $e) {
                Log::error('Failed to auto-publish scheduled homework.', [
                    'homework_id' => $homework->id,
                    'error'       => $e->getMessage(),
                ]);
                $this->error("Failed to publish homework #{$homework->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}