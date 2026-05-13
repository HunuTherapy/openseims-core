<x-filament-panels::page>
    <div class="mx-auto flex w-full max-w-none flex-col gap-6 px-1 pb-6 pt-2 text-black lg:gap-7">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-semibold tracking-[-0.04em] text-black">
                        Special Education Data <span class="font-normal text-grey-400">| Overview</span>
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="sr-only" for="dashboard-active-year">Year selector</label>
                    <div class="relative">
                        <select
                            id="dashboard-active-year"
                            wire:model.live="activeYear"
                            class="h-[34px] appearance-none rounded-lg border border-[var(--color-grey-350)] bg-white py-0 pl-3 pr-10 text-[1.05rem] font-medium text-grey-850 ring-0 shadow-none focus:border-[var(--color-grey-350)] focus:ring-0"
                            aria-label="Year selector"
                        >
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <x-heroicon-m-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-grey-850" />
                    </div>
                </div>
            </div>

            <div class="rounded-[10px] border border-primary-400 bg-primary-50 px-5 py-4 text-sm text-grey-800 font-medium">
                <span>Reporting Period: {{ $reportingContext['reportingPeriod'] }}</span>
                <span class="mx-3 text-grey-400">|</span>
                <span>Data Coverage: {{ $reportingContext['coverage'] }}</span>
                <span class="mx-3 text-grey-400">|</span>
                <span>Last updated: {{ $reportingContext['lastUpdated'] }} <span class="text-grey-400">({{ $reportingContext['cadence'] }})</span></span>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4 lg:grid-rows-[auto_auto_auto_auto]">
                @foreach ($summaryCards as $card)
                    <article class="grid h-full gap-y-4 rounded-2xl border border-grey-200 bg-white px-6 py-6 shadow-[0_4px_18px_rgba(15,23,42,0.04)] lg:row-span-4 lg:grid-rows-subgrid lg:gap-y-[10px]">
                        <p class="text-sm font-normal leading-7 tracking-[-0.15px] text-grey-850">{!! $card['title'] !!}</p>
                        <p class="text-3xl leading-none font-medium tracking-[-0.04em] text-black">{{ $card['value'] }}</p>
                        <p class="text-sm leading-7 text-grey-400">{{ $card['caption'] }}</p>
                        <div class="inline-flex items-center gap-2 text-sm {{ $card['trend'] === 'No comparison available' ? 'text-grey-500' : ($card['trendDirection'] === 'up' ? 'text-success-600' : 'text-danger-600') }}">
                            @if ($card['trend'] === 'No comparison available')
                                <x-heroicon-c-minus-circle class="h-4 w-4" />
                            @elseif ($card['trendDirection'] === 'up')
                                <x-heroicon-o-arrow-trending-up class="h-4 w-4" />
                            @else
                                <x-heroicon-o-arrow-trending-down class="h-4 w-4" />
                            @endif

                            <span>{{ $card['trend'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
