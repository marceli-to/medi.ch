<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicament extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'handelsnamen' => 'array',
        'nummer' => 'integer',
    ];

    public function cardStates(): HasMany
    {
        return $this->hasMany(CardState::class);
    }

    /**
     * Section keys (from config/sections.php) for which this medicament
     * actually has content. These are the sections that become flashcards.
     *
     * @return array<int, string>
     */
    public function presentSectionKeys(): array
    {
        return array_values(array_filter(
            array_keys(config('sections')),
            fn (string $key) => filled($this->{$key}),
        ));
    }
}
