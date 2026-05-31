<?php

namespace App\Console\Commands;

use App\Models\Medicament;
use App\Support\MedicamentParser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:import-medicaments {--path= : Directory of medicament markdown files (defaults to base_path(.data/medikamente))}')]
#[Description('Import medicament markdown files into the medicaments table (idempotent, keyed on source_path).')]
class ImportMedicaments extends Command
{
    public function handle(MedicamentParser $parser): int
    {
        $dir = $this->option('path') ?: base_path('.data/medikamente');

        if (! is_dir($dir)) {
            $this->error("Directory not found: {$dir}");

            return self::FAILURE;
        }

        // All *.md except the index file.
        $files = collect(glob(rtrim($dir, '/').'/*.md'))
            ->reject(fn (string $f) => basename($f) === '_index.md')
            ->values();

        if ($files->isEmpty()) {
            $this->warn("No medicament files found in {$dir}");

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $cards = 0;

        foreach ($files as $file) {
            $raw = file_get_contents($file);
            $data = $parser->parse($raw);

            // source_path is the stable idempotency key; keep it relative to
            // the project root so the DB is portable across machines.
            $sourcePath = Str::after($file, base_path().DIRECTORY_SEPARATOR);
            $slug = Str::slug($data['name']);

            $medicament = Medicament::updateOrCreate(
                ['source_path' => $sourcePath],
                $data + ['slug' => $slug],
            );

            $medicament->wasRecentlyCreated ? $created++ : $updated++;

            $cards += $this->syncCards($medicament);
        }

        $this->info("Imported {$files->count()} medicament(s): {$created} created, {$updated} updated.");
        $this->info("Synced cards: {$cards} present section(s) across all medicaments.");

        return self::SUCCESS;
    }

    /**
     * Ensure exactly one card per present section for this medicament.
     * Creating with firstOrCreate preserves Leitner progress on re-import;
     * sections that disappeared from the source get their stale cards pruned.
     *
     * @return int Number of present sections (i.e. cards that should exist).
     */
    private function syncCards(Medicament $medicament): int
    {
        $present = $medicament->presentSectionKeys();

        foreach ($present as $key) {
            $medicament->cardStates()->firstOrCreate(['section_key' => $key]);
        }

        // Prune cards whose section no longer has content.
        $medicament->cardStates()
            ->whereNotIn('section_key', $present ?: [''])
            ->delete();

        return count($present);
    }
}
