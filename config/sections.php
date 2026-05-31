<?php

/*
|--------------------------------------------------------------------------
| Medikamenten-Abschnitte (SectionMap)
|--------------------------------------------------------------------------
|
| Single source of truth for the clinical sections parsed out of each
| medicament markdown file. Each entry maps a stable `key` (used as the
| DB column on `medicaments`, as `card_states.section_key`, and in the
| study queue) to:
|
|   - 'label'    The German heading shown on the flashcard front as the
|                question, and as the section title on the back.
|   - 'headings' The markdown `## ` heading variants found in the source
|                files that should be parsed into this section. Matching
|                is case-insensitive and whitespace-normalised.
|
| Order here defines the order sections are rendered and the order cards
| are generated per drug. Add a new section by adding an entry; the
| importer and study session pick it up without further changes (a new
| matching DB column must also be added via migration).
|
*/

return [

    'stoffklasse' => [
        'label' => 'Stoffklasse/Wirkstoff',
        'headings' => ['Stoffklasse/Wirkstoff', 'Stoffklasse', 'Wirkstoff'],
    ],

    'indikationen' => [
        'label' => 'Indikationen/Anwendungsmöglichkeiten',
        'headings' => ['Indikationen/Anwendungsmöglichkeiten', 'Indikationen', 'Anwendungsmöglichkeiten'],
    ],

    'dosierung' => [
        'label' => 'Dosierung/Anwendung',
        'headings' => ['Dosierung/Anwendung', 'Dosierung', 'Anwendung'],
    ],

    'kontraindikationen' => [
        'label' => 'Kontraindikationen',
        'headings' => ['Kontraindikationen'],
    ],

    'warnhinweise' => [
        'label' => 'Warnhinweise und Vorsichtsmassnahmen',
        'headings' => ['Warnhinweise und Vorsichtsmassnahmen', 'Warnhinweise', 'Vorsichtsmassnahmen'],
    ],

    'interaktionen' => [
        'label' => 'Interaktionen',
        'headings' => ['Interaktionen'],
    ],

    'schwangerschaft' => [
        'label' => 'Schwangerschaft/Stillzeit',
        'headings' => ['Schwangerschaft/Stillzeit', 'Schwangerschaft', 'Stillzeit'],
    ],

    'nebenwirkungen' => [
        'label' => 'Unerwünschte Wirkungen',
        'headings' => ['Unerwünschte Wirkungen', 'Nebenwirkungen'],
    ],

    'eigenschaften' => [
        'label' => 'Eigenschaften/Wirkungen',
        'headings' => ['Eigenschaften/Wirkungen', 'Eigenschaften', 'Wirkungen'],
    ],

    'pharmakokinetik' => [
        'label' => 'Pharmakokinetik',
        'headings' => ['Pharmakokinetik'],
    ],

    'sonstiges' => [
        'label' => 'Sonstige Hinweise',
        'headings' => ['Sonstige Hinweise', 'Sonstiges'],
    ],

];
