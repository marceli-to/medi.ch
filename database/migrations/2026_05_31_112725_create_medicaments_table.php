<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicaments', function (Blueprint $table) {
            $table->id();

            // Identity / front-matter fields
            $table->string('slug', 191)->unique();
            $table->string('name');
            $table->string('wirkstoffgruppe')->nullable();
            $table->json('handelsnamen')->nullable();
            $table->unsignedInteger('nummer')->nullable();

            // Clinical sections (rendered HTML, nullable — not every drug has each).
            // Keys mirror config/sections.php.
            $table->text('stoffklasse')->nullable();
            $table->text('indikationen')->nullable();
            $table->text('dosierung')->nullable();
            $table->text('kontraindikationen')->nullable();
            $table->text('warnhinweise')->nullable();
            $table->text('interaktionen')->nullable();
            $table->text('schwangerschaft')->nullable();
            $table->text('nebenwirkungen')->nullable();
            $table->text('eigenschaften')->nullable();
            $table->text('pharmakokinetik')->nullable();
            $table->text('sonstiges')->nullable();

            // Raw source + provenance for idempotent re-imports
            $table->longText('raw_markdown');
            $table->string('source_path', 191)->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicaments');
    }
};
