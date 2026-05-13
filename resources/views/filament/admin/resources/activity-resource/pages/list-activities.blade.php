<x-filament-panels::page>
    @php
        $dateGroups = $this->getVisibleDateGroups();
        $totalDateGroups = $this->getTotalDateGroupsCount();
        $table = $this->getTable();
        $filtersApplyAction = $table->getFiltersApplyAction();
        $filtersForm = $table->getFiltersForm();
        $filtersFormMaxHeight = $table->getFiltersFormMaxHeight();
        $filtersFormWidth = $table->getFiltersFormWidth();
        $filtersResetActionPosition = $table->getFiltersResetActionPosition();
        $filtersTriggerAction = $table->getFiltersTriggerAction();
    @endphp

    <style>
        .audit-activity-row {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 1024px) {
            .audit-activity-row {
                display: grid;
                grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.3fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 2fr);
                align-items: flex-start;
                gap: 1.25rem;
                width: 100%;
            }
        }

        .audit-activity-row > div {
            min-width: 0;
        }

        .audit-log-table-shell {
            overflow: visible !important;
        }

        .audit-log-table-shell .fi-ta-header-toolbar {
            position: relative;
            z-index: 20;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <section
            class="fi-ta-ctn fi-ta-ctn-with-header audit-log-table-shell"
            style="display: block; border-radius: 1rem; box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);"
        >
            <div class="fi-ta-header-toolbar">
                <div class="fi-ta-actions fi-align-start fi-wrapped"></div>

                <div>
                    <x-filament-tables::search-field class="w-full sm:max-w-md" />

                    <x-filament::dropdown
                        :max-height="$filtersFormMaxHeight"
                        placement="bottom-end"
                        shift
                        :flip="false"
                        :width="$filtersFormWidth ?? \Filament\Support\Enums\Width::ExtraSmall"
                        :wire:key="$this->getId() . '.table.filters'"
                        class="fi-ta-filters-dropdown"
                    >
                        <x-slot name="trigger">
                            {{ $filtersTriggerAction->badge($this->getActiveFiltersCount()) }}
                        </x-slot>

                        <x-filament-tables::filters
                            :apply-action="$filtersApplyAction"
                            :form="$filtersForm"
                            :reset-action-position="$filtersResetActionPosition"
                        />
                    </x-filament::dropdown>
                </div>
            </div>

            <div style="padding: 1.5rem;">
                @if ($totalDateGroups > 0)
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem; padding: 0 0.5rem;">
                        <p style="margin: 0; font-size: 0.95rem; color: #6b7280;">
                            Showing {{ $dateGroups->count() }} of {{ $totalDateGroups }} {{ \Illuminate\Support\Str::plural('date', $totalDateGroups) }}
                        </p>

                        @if ($this->hasMoreDateGroups())
                            <p style="margin: 0; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: #9ca3af;">
                                Scroll for more
                            </p>
                        @endif
                    </div>
                @endif

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @forelse ($dateGroups as $group)
                        @php
                            $activityDate = $group->activity_date;
                            $isExpanded = $this->isDateExpanded($activityDate);
                        @endphp

                        <section
                            wire:key="activity-date-group-{{ $activityDate }}"
                            style="overflow: hidden; border: 1px solid #e5e7eb; border-radius: 1rem; background: #fff;"
                        >
                            <button
                                style="display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; text-align: left; background: transparent; border: 0; cursor: pointer;"
                                type="button"
                                wire:click="toggleDate('{{ $activityDate }}')"
                            >
                                <div style="display: flex; min-width: 0; align-items: center; gap: 1rem;">
                                    <span style="display: flex; height: 2.5rem; width: 2.5rem; flex-shrink: 0; align-items: center; justify-content: center; border: 1px solid #d1d5db; border-radius: 0.75rem; background: #fff; color: #374151;">
                                        <x-filament::icon
                                            :icon="$isExpanded ? 'heroicon-o-chevron-down' : 'heroicon-o-chevron-right'"
                                            class="h-5 w-5"
                                        />
                                    </span>

                                    <div style="min-width: 0;">
                                        <div style="font-size: 1.05rem; font-weight: 600; color: #111827;">
                                            {{ $this->formatActivityDate($activityDate) }}
                                        </div>
                                        <div style="margin-top: 0.2rem; font-size: 0.9rem; color: #6b7280;">
                                            {{ $isExpanded ? 'Expanded' : 'Collapsed' }}
                                        </div>
                                    </div>
                                </div>

                                <div style="flex-shrink: 0; border: 1px solid #c7d2fe; border-radius: 999px; background: #eef2ff; padding: 0.35rem 0.85rem; font-size: 0.9rem; font-weight: 600; color: #4338ca;">
                                    {{ number_format((int) $group->total) }} {{ \Illuminate\Support\Str::plural('entry', (int) $group->total) }}
                                </div>
                            </button>

                            @if ($isExpanded)
                                <div style="border-top: 1px solid #e5e7eb; background: #f9fafb; padding: 1rem;">
                                    <div style="overflow: hidden; border: 1px solid #e5e7eb; border-radius: 0.875rem; background: #fff;">
                                        <div style="display: flex; flex-direction: column; gap: 0;">
                                            @foreach ($this->getActivitiesForDate($activityDate) as $activity)
                                                <article wire:key="activity-log-{{ $activity->getKey() }}" style="padding: 1rem 1.25rem; border-top: {{ $loop->first ? '0' : '1px solid #e5e7eb' }};">
                                                    <div class="audit-activity-row">
                                                        <div class="audit-activity-cell--when">
                                                            <div style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af;">When</div>
                                                            <div style="margin-top: 0.3rem; font-size: 0.95rem; color: #374151;">
                                                                {{ $this->formatActivityTime($activity->created_at) }}
                                                            </div>
                                                        </div>

                                                        <div class="audit-activity-cell--actor">
                                                            <div style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af;">Actor</div>
                                                            <div style="margin-top: 0.3rem; font-size: 0.95rem; font-weight: 600; color: #111827;">{{ $activity->actor_label }}</div>
                                                            @if ($activity->actor_email)
                                                                <div style="font-size: 0.8rem; color: #6b7280;">{{ $activity->actor_email }}</div>
                                                            @endif
                                                        </div>

                                                        <div class="audit-activity-cell--action">
                                                            <div style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af;">Action</div>
                                                            <div style="margin-top: 0.3rem;">
                                                                <x-filament::badge :color="$activity->event_badge_color">
                                                                    {{ $activity->event }}
                                                                </x-filament::badge>
                                                            </div>
                                                        </div>

                                                        <div class="audit-activity-cell--module">
                                                            <div style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af;">Module</div>
                                                            <div style="margin-top: 0.3rem;">
                                                                <x-filament::badge color="gray">
                                                                    {{ $activity->module }}
                                                                </x-filament::badge>
                                                            </div>
                                                        </div>

                                                        <div class="audit-activity-cell--entity">
                                                            <div style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af;">Entity</div>
                                                            <div style="margin-top: 0.3rem; font-size: 0.95rem; color: #374151;">{{ $activity->subject_type_label }}</div>
                                                        </div>

                                                        <div class="audit-activity-cell--record">
                                                            <div style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af;">Record</div>
                                                            <div style="margin-top: 0.3rem; font-size: 0.95rem; font-weight: 600; color: #111827;">{{ $activity->subject_label }}</div>
                                                            <div style="font-size: 0.8rem; color: #6b7280;">{{ $activity->subject_identifier }}</div>
                                                        </div>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </section>
                    @empty
                        <div style="border: 1px dashed #d1d5db; border-radius: 1rem; background: #fff; padding: 3rem 1.5rem; text-align: center;">
                            <p style="margin: 0; font-size: 1rem; font-weight: 600; color: #111827;">No audit logs found</p>
                            <p style="margin: 0.5rem 0 0; font-size: 0.95rem; color: #6b7280;">Try adjusting the current search or filters.</p>
                        </div>
                    @endforelse
                </div>

                @if ($this->hasMoreDateGroups())
                    <div
                        class="mt-6 flex justify-center"
                        x-intersect.margin.200px="$wire.loadMoreDateGroups()"
                    >
                        <x-filament::button
                            color="gray"
                            outlined
                            size="sm"
                            type="button"
                            wire:click="loadMoreDateGroups"
                            wire:loading.attr="disabled"
                            wire:target="loadMoreDateGroups"
                        >
                            <span wire:loading.remove wire:target="loadMoreDateGroups">Load more dates</span>
                            <span wire:loading wire:target="loadMoreDateGroups">Loading…</span>
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
