<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_states', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medicament_id')
                ->constrained()
                ->cascadeOnDelete();

            // A card = one drug + one clinical section (key from config/sections.php).
            $table->string('section_key', 32);

            // Leitner spaced-repetition state
            $table->unsignedTinyInteger('leitner_box')->default(1);
            $table->enum('last_rating', ['know', 'unsure', 'unknown'])->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('due_at')->nullable();

            $table->timestamps();

            // One Leitner row per (drug, section). Structured so a `user_id`
            // could later extend this uniqueness without a rewrite.
            $table->unique(['medicament_id', 'section_key']);

            // Study queue reads by due date.
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_states');
    }
};
