<?php

namespace App\Support;

use App\Models\CardState;

/**
 * Applies a study rating to a card's Leitner state.
 *
 *   know    -> up one box (max), due in the new box's interval
 *   unsure  -> stay, due after a short interval
 *   unknown -> reset to box 1, due now (resurfaces this session)
 *
 * Intervals come from config/leitner.php.
 */
class Leitner
{
    public const KNOW = 'know';
    public const UNSURE = 'unsure';
    public const UNKNOWN = 'unknown';

    public function rate(CardState $card, string $rating): CardState
    {
        $maxBox = (int) config('leitner.max_box', 5);

        switch ($rating) {
            case self::KNOW:
                $card->leitner_box = min($card->leitner_box + 1, $maxBox);
                $card->due_at = now()->addMinutes($this->boxInterval($card->leitner_box));
                break;

            case self::UNSURE:
                // Stay in the current box, short interval.
                $card->due_at = now()->addMinutes((int) config('leitner.unsure_minutes', 10));
                break;

            case self::UNKNOWN:
                $card->leitner_box = 1;
                $card->due_at = now();
                break;

            default:
                throw new \InvalidArgumentException("Unknown rating: {$rating}");
        }

        $card->last_rating = $rating;
        $card->last_reviewed_at = now();
        $card->save();

        return $card;
    }

    private function boxInterval(int $box): int
    {
        $boxes = config('leitner.boxes', []);

        return (int) ($boxes[$box] ?? end($boxes));
    }
}
