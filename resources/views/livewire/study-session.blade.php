<div
    @class([
        'mx-auto flex min-h-dvh w-full max-w-2xl flex-col px-4 pt-5 antialiased sm:pt-8',
        'pb-28' => $revealed,
        'pb-10 sm:pb-16' => ! $revealed,
    ])
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
    @if ($card)
        {{-- Session progress --}}
        @php
            $total = $remaining + $answered;
        @endphp
        <div class="mb-6 flex justify-end">
            <span class="rounded-full bg-stone-900 px-2.5 py-1 text-xs font-medium tabular-nums text-white dark:bg-white dark:text-stone-900">
                {{ $answered + 1 }}/{{ $total }}
            </span>
        </div>

        {{-- Card — tinted identity band over a clean body --}}
        <div class="flex flex-1 flex-col" wire:key="card-{{ $card->id }}">
            <article
                class="flex flex-1 flex-col overflow-hidden rounded-xl bg-white shadow-xl shadow-stone-950/5 ring-1 ring-stone-950/5 dark:bg-stone-900 dark:shadow-none dark:ring-white/10"
            >
                {{-- Identity band --}}
                <div class="bg-stone-50 px-6 pt-6 pb-7 dark:bg-stone-950/40">
                    {{-- Category --}}
                    @if ($card->medicament->wirkstoffgruppe)
                        <p class="text-xs font-semibold tracking-wide text-stone-400 uppercase dark:text-stone-500">
                            {{ $card->medicament->wirkstoffgruppe }}
                        </p>
                    @endif

                    {{-- Title --}}
                    <h2 class="mt-4 text-pretty text-4xl font-semibold tracking-tight break-words hyphens-auto text-stone-900 sm:text-5xl dark:text-white">
                        {{ $card->medicament->name }}
                    </h2>

                    {{-- Product pills --}}
                    @if ($card->medicament->handelsnamen)
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($card->medicament->handelsnamen as $brand)
                                <span class="rounded-full bg-stone-900 px-2.5 py-1 text-xs font-medium text-white dark:bg-white dark:text-stone-900">
                                    {{ $brand }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="flex flex-1 flex-col px-6 pt-7 pb-7">
                    {{-- Question --}}
                    <p class="text-pretty text-3xl font-medium tracking-tight break-words hyphens-auto text-stone-800 dark:text-stone-200">
                        {{ $card->sectionLabel() }}?
                    </p>

                    @if ($revealed)
                        <div class="prose-section mt-7 flex-1 border-t border-stone-950/5 pt-7 text-base text-stone-700 dark:border-white/5 dark:text-stone-300">
                            {!! $card->medicament->{$card->section_key} !!}
                        </div>
                    @else
                        <div class="flex flex-1 items-center justify-center py-12">
                            <button
                                type="button"
                                wire:click="reveal"
                                class="flex items-center gap-2 rounded-lg bg-stone-100 py-3 pr-4 pl-3.5 text-base font-semibold text-stone-700 transition-colors hover:bg-stone-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-500 dark:bg-stone-800 dark:text-stone-200 dark:hover:bg-stone-700"
                            >
                                <svg class="size-5 shrink-0 fill-stone-500 dark:fill-stone-400" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M10 4c-3.6 0-6.7 2.1-8.2 5.2a.9.9 0 0 0 0 .8C3.3 13.9 6.4 16 10 16s6.7-2.1 8.2-5.2a.9.9 0 0 0 0-.8C16.7 6.1 13.6 4 10 4Zm0 9.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7Zm0-5.5a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" />
                                </svg>
                                Aufdecken
                            </button>
                        </div>
                    @endif
                </div>
            </article>
        </div>

        {{-- Rating controls — sticky bottom bar --}}
        @if ($revealed)
            <div class="fixed inset-x-0 bottom-0 z-10 grid grid-cols-3 border-t border-stone-950/10 bg-white/80 backdrop-blur pb-[env(safe-area-inset-bottom)] dark:border-white/10 dark:bg-stone-950/80">
                <button
                    type="button"
                    wire:click="rate('know')"
                    class="py-5 text-base font-semibold text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-emerald-500 dark:text-emerald-300 dark:hover:bg-emerald-950/40"
                >
                    Kann ich
                </button>
                <button
                    type="button"
                    wire:click="rate('unsure')"
                    class="border-x border-stone-950/10 py-5 text-base font-semibold text-amber-700 transition-colors hover:bg-amber-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-amber-500 dark:border-white/10 dark:text-amber-300 dark:hover:bg-amber-950/40"
                >
                    Unsicher
                </button>
                <button
                    type="button"
                    wire:click="rate('unknown')"
                    class="py-5 text-base font-semibold text-rose-700 transition-colors hover:bg-rose-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-rose-500 dark:text-rose-300 dark:hover:bg-rose-950/40"
                >
                    Gar nicht
                </button>
            </div>
        @endif
    @else
        {{-- Session complete --}}
        <div class="flex flex-1 flex-col items-center justify-center text-center">
            <div class="w-full rounded-xl bg-white p-10 shadow-xl shadow-stone-950/5 ring-1 ring-stone-950/5 dark:bg-stone-900 dark:shadow-none dark:ring-white/10">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 ring-1 ring-emerald-600/15 dark:bg-emerald-950/40 dark:ring-emerald-400/15">
                    <svg class="size-6 fill-emerald-600 dark:fill-emerald-400" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M20.03 6.97a.75.75 0 0 1 0 1.06l-9.5 9.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.97 3.97 8.97-8.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-semibold tracking-tight text-stone-900 dark:text-white">
                    Sitzung abgeschlossen
                </h1>
                <p class="mx-auto mt-2 max-w-[40ch] text-pretty text-stone-500 dark:text-stone-400">
                    @if ($answered > 0)
                        Du hast {{ $answered }} {{ $answered === 1 ? 'Karte' : 'Karten' }} bearbeitet. Gut gemacht.
                    @else
                        Aktuell sind keine Karten fällig — schau später wieder vorbei.
                    @endif
                </p>

                @if ($answered > 0)
                    <dl class="mt-8 grid grid-cols-3">
                        <div class="px-3">
                            <dd class="text-3xl font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $tally['know'] }}</dd>
                            <dt class="mt-1 text-sm text-stone-500 dark:text-stone-400">Kann ich</dt>
                        </div>
                        <div class="border-x border-stone-950/5 px-3 dark:border-white/10">
                            <dd class="text-3xl font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ $tally['unsure'] }}</dd>
                            <dt class="mt-1 text-sm text-stone-500 dark:text-stone-400">Unsicher</dt>
                        </div>
                        <div class="px-3">
                            <dd class="text-3xl font-semibold tabular-nums text-rose-600 dark:text-rose-400">{{ $tally['unknown'] }}</dd>
                            <dt class="mt-1 text-sm text-stone-500 dark:text-stone-400">Gar nicht</dt>
                        </div>
                    </dl>
                @endif

                <button
                    type="button"
                    wire:click="restart"
                    class="mt-9 rounded-lg bg-stone-900 px-6 py-2.5 text-sm font-semibold text-white transition-transform hover:scale-[1.02] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-500 active:scale-100 dark:bg-white dark:text-stone-900"
                >
                    Alle Karten neu lernen
                </button>
            </div>
        </div>
    @endif
</div>
