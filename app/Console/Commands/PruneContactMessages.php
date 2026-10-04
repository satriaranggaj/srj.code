<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use Illuminate\Console\Command;

/**
 * Deletes contact messages older than the configured retention window.
 *
 * Retention exists because each stored message also holds the visitor's IP address
 * and user agent, which is personal data. Nothing is ever removed automatically:
 * deletion happens only when an operator runs this command, so the retention window
 * is always an explicit decision.
 */
class PruneContactMessages extends Command
{
    protected $signature = 'contact:prune
                            {--days= : Override the configured retention window in days.}
                            {--dry-run : Report what would be deleted without deleting anything.}';

    protected $description = 'Delete contact messages older than the configured retention window';

    public function handle(): int
    {
        // `!== null` rather than a truthiness check, so an explicit `--days=0` is rejected
        // instead of silently falling back to the configured value.
        $override = $this->option('days');
        $days = $override !== null ? (int) $override : (int) config('portfolio.contact_retention_days', 90);

        if ($days < 1) {
            $this->components->error('The retention window must be at least 1 day.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        $query = ContactMessage::where('created_at', '<', $cutoff);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->components->info("No messages older than {$days} days. Nothing to do.");

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->components->warn("{$total} message(s) are older than {$days} days and would be deleted. Dry run, so nothing was changed.");

            return self::SUCCESS;
        }

        $deleted = 0;

        // Chunked so a large backlog cannot exhaust memory in one transaction.
        $query->orderBy('id')->chunkById(200, function ($messages) use (&$deleted) {
            $deleted += $messages->each(fn (ContactMessage $message) => $message->delete())->count();
        });

        $this->components->info("Deleted {$deleted} message(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
