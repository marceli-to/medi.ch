<?php

namespace App\Support;

use League\CommonMark\CommonMarkConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Parses a single medicament markdown file into a normalised array ready
 * for upsert into the `medicaments` table.
 *
 * Front matter supplies name / handelsnamen / wirkstoffgruppe / nummer.
 * The body is split on `## ` headings; each heading is matched against the
 * SectionMap (config/sections.php) and its content rendered to HTML, stored
 * under the section's stable key. Unknown headings are ignored. Missing
 * sections are simply absent (null) — parsing never throws on a missing one.
 */
class MedicamentParser
{
    private CommonMarkConverter $converter;

    public function __construct(?CommonMarkConverter $converter = null)
    {
        $this->converter = $converter ?? new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * @return array<string, mixed> Column => value for the medicaments table
     *                              (excluding slug / source_path, set by caller).
     */
    public function parse(string $raw): array
    {
        $document = YamlFrontMatter::parse($raw);
        $matter = $document->matter();
        $body = $document->body();

        $data = [
            'name' => $this->resolveName($matter, $body),
            'wirkstoffgruppe' => $matter['wirkstoffgruppe'] ?? null,
            'handelsnamen' => $this->normaliseHandelsnamen($matter['handelsnamen'] ?? null),
            'nummer' => isset($matter['nummer']) ? (int) $matter['nummer'] : null,
            'raw_markdown' => $raw,
        ];

        // All section columns default to null so a re-import that lost a
        // section clears it rather than leaving stale content.
        foreach (array_keys(config('sections')) as $key) {
            $data[$key] = null;
        }

        foreach ($this->splitSections($body) as $heading => $content) {
            $key = $this->matchSection($heading);
            if ($key !== null && $content !== '') {
                $data[$key] = $this->render($content);
            }
        }

        return $data;
    }

    private function resolveName(array $matter, string $body): string
    {
        if (! empty($matter['name'])) {
            return (string) $matter['name'];
        }

        // Fall back to the first H1 in the body.
        if (preg_match('/^#\s+(.+)$/m', $body, $m)) {
            return trim($m[1]);
        }

        return 'Unbenannt';
    }

    /**
     * @return array<int, string>|null
     */
    private function normaliseHandelsnamen(mixed $value): ?array
    {
        if (is_array($value)) {
            $names = array_values(array_filter(array_map('trim', $value)));

            return $names === [] ? null : $names;
        }

        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }

        return null;
    }

    /**
     * Split the markdown body into [heading => content] keyed by the raw
     * `## ` heading text. The leading H1 and intro lines are discarded.
     *
     * @return array<string, string>
     */
    private function splitSections(string $body): array
    {
        $sections = [];
        $currentHeading = null;
        $buffer = [];

        foreach (preg_split('/\R/', $body) as $line) {
            if (preg_match('/^##\s+(.+?)\s*$/', $line, $m)) {
                if ($currentHeading !== null) {
                    $sections[$currentHeading] = trim(implode("\n", $buffer));
                }
                $currentHeading = $m[1];
                $buffer = [];
                continue;
            }

            if ($currentHeading !== null) {
                $buffer[] = $line;
            }
        }

        if ($currentHeading !== null) {
            $sections[$currentHeading] = trim(implode("\n", $buffer));
        }

        return $sections;
    }

    /**
     * Match a raw heading against SectionMap headings (case- and
     * whitespace-insensitive). Returns the section key or null.
     */
    private function matchSection(string $heading): ?string
    {
        $needle = $this->canonicalise($heading);

        foreach (config('sections') as $key => $section) {
            foreach ($section['headings'] as $variant) {
                if ($this->canonicalise($variant) === $needle) {
                    return $key;
                }
            }
        }

        return null;
    }

    private function canonicalise(string $value): string
    {
        // Collapse whitespace, strip a trailing colon, lowercase.
        $value = preg_replace('/\s+/u', ' ', trim($value));
        $value = rtrim($value, ':');

        return mb_strtolower($value);
    }

    private function render(string $markdown): string
    {
        return trim($this->converter->convert($markdown)->getContent());
    }
}
