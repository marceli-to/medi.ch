<?php

namespace App\Console\Commands;

use App\Models\CardState;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cards:reset {--force : Skip the confirmation prompt}')]
#[Description('Reset all cards back to Leitner box 1, clearing review history and due dates.')]
class ResetCards extends Command
{
    public function handle(): int
    {
        $count = CardState::query()->count();

        if ($count === 0) {
            $this->info('No cards to reset.');

            return self::SUCCESS;
        }

        if (! $this->option('force')
            && ! $this->confirm("Reset Leitner progress for {$count} card(s)? This cannot be undone.")) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        // Reset state in place rather than deleting rows, so the (drug, section)
        // cards stay intact and just return to a fresh, due-now state.
        CardState::query()->update([
            'leitner_box' => 1,
            'last_rating' => null,
            'last_reviewed_at' => null,
            'due_at' => null,
        ]);

        $this->info("Reset {$count} card(s) to box 1.");

        return self::SUCCESS;
    }
}
