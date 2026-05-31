<?php

namespace App\Livewire;

use App\Models\CardState;
use App\Support\Leitner;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StudySession extends Component
{
    /** The card currently shown, or null when the queue is empty. */
    public ?CardState $card = null;

    /** Whether the answer (back of card) is revealed. */
    public bool $revealed = false;

    /** Tally of ratings given this session, for the completion summary. */
    public array $tally = ['know' => 0, 'unsure' => 0, 'unknown' => 0];

    /** How many cards have been answered this session. */
    public int $answered = 0;

    public function mount(): void
    {
        $this->loadNextCard();
    }

    public function reveal(): void
    {
        if ($this->card) {
            $this->revealed = true;
        }
    }

    public function rate(string $rating, Leitner $leitner): void
    {
        if (! $this->card || ! $this->revealed) {
            return;
        }

        if (! in_array($rating, [Leitner::KNOW, Leitner::UNSURE, Leitner::UNKNOWN], true)) {
            return;
        }

        $leitner->rate($this->card, $rating);

        $this->tally[$rating]++;
        $this->answered++;

        $this->loadNextCard();
    }

    public function restart(): void
    {
        // Make every card due now and start a fresh session.
        CardState::query()->update(['due_at' => now()]);

        $this->tally = ['know' => 0, 'unsure' => 0, 'unknown' => 0];
        $this->answered = 0;
        $this->loadNextCard();
    }

    public function render(): View
    {
        $remaining = $this->card ? $this->dueQuery()->count() : 0;

        return view('livewire.study-session', [
            'remaining' => $remaining,
        ]);
    }

    private function loadNextCard(): void
    {
        $this->revealed = false;

        $this->card = $this->dueQuery()
            ->with('medicament')
            ->orderByRaw('due_at IS NULL DESC')
            ->orderBy('due_at')
            ->inRandomOrder()
            ->first();
    }

    private function dueQuery()
    {
        return CardState::query()->due();
    }
}
