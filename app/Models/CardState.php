<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardState extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'leitner_box' => 'integer',
        'last_reviewed_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(Medicament::class);
    }

    /**
     * Cards that are due for review now (due_at in the past or null).
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('due_at')->orWhere('due_at', '<=', now());
        });
    }

    /**
     * The German label for this card's section, from config/sections.php.
     */
    public function sectionLabel(): string
    {
        return config("sections.{$this->section_key}.label", $this->section_key);
    }
}
