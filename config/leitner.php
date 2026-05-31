<?php

/*
|--------------------------------------------------------------------------
| Leitner Spaced-Repetition Configuration
|--------------------------------------------------------------------------
|
| Five boxes with increasing review intervals. On rating a card:
|
|   - "Kann ich"  (know)    -> move up one box (max 5), due in new box's interval
|   - "Unsicher"  (unsure)  -> stay in box, due after a short interval
|   - "Gar nicht" (unknown) -> reset to box 1, due now (resurfaces this session)
|
| Intervals are expressed in minutes and are easy to tweak here. `boxes`
| is keyed by box number (1-5). `unsure_minutes` is the short interval used
| for the "Unsicher" rating regardless of box.
|
*/

return [

    'max_box' => 5,

    // Interval (minutes) until a card is due again after rating it "Kann ich"
    // in the resulting box.
    'boxes' => [
        1 => 10,        // 10 minutes
        2 => 1440,      // 1 day
        3 => 4320,      // 3 days
        4 => 10080,     // 7 days
        5 => 20160,     // 14 days
    ],

    // "Unsicher" — short interval so the card comes back soon.
    'unsure_minutes' => 10,

];
