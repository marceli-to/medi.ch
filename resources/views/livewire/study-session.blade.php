<div
    class="mx-auto flex min-h-screen w-full max-w-2xl flex-col px-4 py-8 sm:py-12"
    x-data="{
        get revealed() { return $wire.revealed },
        get hasCard() { return @js((bool) $card) },
        reveal() { if (this.hasCard && !this.revealed) { $wire.reveal() } },
        rate(r) { if (this.revealed) { $wire.rate(r) } },
    }"
    @keydown.window.space.prevent="reveal()"
    @keydown.window.1="rate('know')"
    @keydown.window.2="rate('unsure')"
    @keydown.window.3="rate('unknown')"
>
    {{-- Header / progress --}}
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-lg font-semibold tracking-tight text-stone-700 dark:text-stone-200">
            Medikamente lernen
        </h1>
        @if ($card)
            <span class="rounded-full bg-stone-200 px-3 py-1 text-sm font-medium text-stone-600 dark:bg-stone-800 dark:text-stone-300">
                {{ $remaining }} {{ $remaining === 1 ? 'Karte' : 'Karten' }} heute
            </span>
        @endif
    </header>

    @if ($card)
        {{-- Card --}}
        <div class="flex flex-1 flex-col" wire:key="card-{{ $card->id }}">
            <article
                class="flex flex-1 flex-col rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition dark:border-stone-800 dark:bg-stone-900 sm:p-8"
            >
                {{-- Drug identity --}}
                <div class="mb-6 flex flex-wrap items-center gap-2">
                    @if ($card->medicament->wirkstoffgruppe)
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            {{ $card->medicament->wirkstoffgruppe }}
                        </span>
                    @endif
                    @foreach (($card->medicament->handelsnamen ?? []) as $brand)
                        <span class="rounded-full border border-stone-200 px-2.5 py-0.5 text-xs text-stone-500 dark:border-stone-700 dark:text-stone-400">
                            {{ $brand }}
                        </span>
                    @endforeach
                </div>

                {{-- Drug name + the section question --}}
                <div class="mb-6">
                    <h2 class="text-3xl font-bold tracking-tight text-stone-900 dark:text-white sm:text-4xl">
                        {{ $card->medicament->name }}
                    </h2>
                    <p class="mt-2 text-lg font-medium text-stone-500 dark:text-stone-400">
                        {{ $card->sectionLabel() }}?
                    </p>
                </div>

                {{-- Answer (back) --}}
                @if ($revealed)
                    <div class="prose-section mt-2 flex-1 border-t border-stone-100 pt-6 text-stone-700 dark:border-stone-800 dark:text-stone-300">
                        {!! $card->medicament->{$card->section_key} !!}
                    </div>
                @else
                    <div class="flex flex-1 items-center justify-center py-10">
                        <button
                            type="button"
                            wire:click="reveal"
                            class="rounded-xl bg-stone-900 px-8 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-stone-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-500 focus-visible:ring-offset-2 dark:bg-white dark:text-stone-900 dark:hover:bg-stone-200"
                        >
                            Aufdecken
                            <span class="ml-2 text-xs font-normal opacity-60">Leertaste</span>
                        </button>
                    </div>
                @endif
            </article>

            {{-- Rating buttons --}}
            @if ($revealed)
                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <button
                        type="button"
                        wire:click="rate('know')"
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-emerald-200 bg-emerald-50 px-4 py-4 font-semibold text-emerald-800 transition hover:border-emerald-400 hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300"
                    >
                        <span class="text-base">Kann ich</span>
                        <span class="mt-0.5 text-xs font-normal opacity-60">Taste 1</span>
                    </button>
                    <button
                        type="button"
                        wire:click="rate('unsure')"
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-amber-200 bg-amber-50 px-4 py-4 font-semibold text-amber-800 transition hover:border-amber-400 hover:bg-amber-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-300"
                    >
                        <span class="text-base">Unsicher</span>
                        <span class="mt-0.5 text-xs font-normal opacity-60">Taste 2</span>
                    </button>
                    <button
                        type="button"
                        wire:click="rate('unknown')"
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-rose-200 bg-rose-50 px-4 py-4 font-semibold text-rose-800 transition hover:border-rose-400 hover:bg-rose-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300"
                    >
                        <span class="text-base">Gar nicht</span>
                        <span class="mt-0.5 text-xs font-normal opacity-60">Taste 3</span>
                    </button>
                </div>
            @endif
        </div>
    @else
        {{-- Session complete --}}
        <div class="flex flex-1 flex-col items-center justify-center text-center">
            <div class="rounded-2xl border border-stone-200 bg-white p-10 shadow-sm dark:border-stone-800 dark:bg-stone-900">
                <div class="mb-2 text-4xl">✓</div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">
                    Sitzung abgeschlossen
                </h2>
                <p class="mt-2 text-stone-500 dark:text-stone-400">
                    @if ($answered > 0)
                        {{ $answered }} {{ $answered === 1 ? 'Karte' : 'Karten' }} bearbeitet.
                    @else
                        Aktuell sind keine Karten fällig.
                    @endif
                </p>

                @if ($answered > 0)
                    <dl class="mt-6 grid grid-cols-3 gap-4 text-sm">
                        <div class="rounded-lg bg-emerald-50 px-3 py-3 dark:bg-emerald-950/50">
                            <dt class="text-emerald-700 dark:text-emerald-300">Kann ich</dt>
                            <dd class="mt-1 text-2xl font-bold text-emerald-800 dark:text-emerald-200">{{ $tally['know'] }}</dd>
                        </div>
                        <div class="rounded-lg bg-amber-50 px-3 py-3 dark:bg-amber-950/50">
                            <dt class="text-amber-700 dark:text-amber-300">Unsicher</dt>
                            <dd class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200">{{ $tally['unsure'] }}</dd>
                        </div>
                        <div class="rounded-lg bg-rose-50 px-3 py-3 dark:bg-rose-950/50">
                            <dt class="text-rose-700 dark:text-rose-300">Gar nicht</dt>
                            <dd class="mt-1 text-2xl font-bold text-rose-800 dark:text-rose-200">{{ $tally['unknown'] }}</dd>
                        </div>
                    </dl>
                @endif

                <button
                    type="button"
                    wire:click="restart"
                    class="mt-8 rounded-xl bg-stone-900 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-700 dark:bg-white dark:text-stone-900 dark:hover:bg-stone-200"
                >
                    Alle Karten neu lernen
                </button>
            </div>
        </div>
    @endif
</div>
