<?php

namespace App\Console\Commands;

use App\Models\JournalAutoGeneratorSetting;
use App\Services\Journal\JournalPostGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class GenerateJournalPost extends Command
{
    protected $signature = 'journal:generate-post {--force : Ignore the enabled/schedule check and generate immediately}';

    protected $description = 'Generate a new AI-written journal post, if the auto-generator schedule is due';

    public function handle(): int
    {
        $settings = JournalAutoGeneratorSetting::current();

        if (! $this->option('force')) {
            if (! $settings->enabled) {
                $this->info('Journal auto-generator is disabled. Skipping.');

                return self::SUCCESS;
            }

            if (! $this->dueToRun($settings)) {
                $this->info('Journal auto-generator is not due to run yet. Skipping.');

                return self::SUCCESS;
            }
        }

        $this->info('Generating journal post...');

        try {
            // Resolved inside the try block (not via method injection) so that a
            // missing ANTHROPIC_API_KEY — thrown from the constructor — is caught
            // and recorded, rather than crashing the command before this block runs.
            $post = app(JournalPostGenerator::class)->generate($settings->topic_notes);

            $settings->update([
                'last_generated_at' => now(),
                'last_run_status' => 'success',
                'last_error' => null,
            ]);

            $this->info("Generated: {$post->title}");

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);

            $settings->update([
                'last_run_status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            $this->error("Journal post generation failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function dueToRun(JournalAutoGeneratorSetting $settings): bool
    {
        $now = Carbon::now();

        if (! $settings->last_generated_at) {
            return $this->matchesScheduleDay($settings, $now);
        }

        $daysSinceLastRun = $settings->last_generated_at->diffInDays($now);

        $minDaysBetweenRuns = match ($settings->frequency) {
            'daily' => 1,
            'weekly' => 6,
            'biweekly' => 13,
            'monthly' => 27,
            default => 6,
        };

        if ($daysSinceLastRun < $minDaysBetweenRuns) {
            return false;
        }

        return $this->matchesScheduleDay($settings, $now);
    }

    private function matchesScheduleDay(JournalAutoGeneratorSetting $settings, Carbon $now): bool
    {
        return match ($settings->frequency) {
            'daily' => true,
            'weekly', 'biweekly' => $settings->day_of_week === null
                || (int) $now->dayOfWeek === (int) $settings->day_of_week,
            // Clamp to the last day of the month so "31" still fires in e.g. February.
            'monthly' => $settings->day_of_month === null
                || $now->day === min((int) $settings->day_of_month, $now->daysInMonth),
            default => true,
        };
    }
}
