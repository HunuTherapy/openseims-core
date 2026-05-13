<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    public function hasConcreteSubject(): bool
    {
        return filled($this->subject_type) && ($this->subject_id !== null);
    }

    public function getModuleAttribute(): string
    {
        return (string) ($this->getExtraProperty('module')
            ?? Str::headline(class_basename((string) $this->subject_type)));
    }

    public function getSubjectTypeLabelAttribute(): string
    {
        return (string) ($this->getExtraProperty('subject_type_label')
            ?? Str::headline(class_basename((string) $this->subject_type)));
    }

    public function getSubjectLabelAttribute(): string
    {
        return (string) ($this->getExtraProperty('subject_label')
            ?? $this->subject_type_label);
    }

    public function getSubjectIdentifierAttribute(): string
    {
        $identifier = $this->getExtraProperty('subject_identifier');

        if (filled($identifier)) {
            return (string) $identifier;
        }

        if ($this->subject_id !== null) {
            return '#'.$this->subject_id;
        }

        return 'System';
    }

    public function getActorLabelAttribute(): string
    {
        if ($this->causer instanceof User) {
            return $this->causer->name;
        }

        return (string) ($this->getExtraProperty('actor_label') ?? 'System');
    }

    public function getActorEmailAttribute(): ?string
    {
        if ($this->causer instanceof User) {
            return $this->causer->email;
        }

        return $this->getExtraProperty('actor_email');
    }

    public function getOldValuesAttribute(): array
    {
        return $this->normalizeChangeSet(data_get($this->properties?->toArray() ?? [], 'old', []));
    }

    public function getNewValuesAttribute(): array
    {
        return $this->normalizeChangeSet(data_get($this->properties?->toArray() ?? [], 'attributes', []));
    }

    public function getDiffRowsAttribute(): array
    {
        $old = $this->old_values;
        $new = $this->new_values;
        $keys = collect(array_keys($old))
            ->merge(array_keys($new))
            ->unique()
            ->sort()
            ->values();

        return $keys
            ->map(fn (string $key): array => [
                'field' => Str::headline(str_replace(['_', '.'], ' ', $key)),
                'old' => $old[$key] ?? '—',
                'new' => $new[$key] ?? '—',
            ])
            ->all();
    }

    public function getMetadataAttribute(): array
    {
        return Arr::except(
            $this->properties?->toArray() ?? [],
            ['attributes', 'old']
        );
    }

    public function getTimelineSummaryAttribute(): string
    {
        $actor = $this->actor_label;
        $event = Str::headline((string) $this->event);
        $label = $this->subject_label;

        return trim("{$actor} {$event} {$label}");
    }

    public function getTimelineGroupDateAttribute(): string
    {
        return (string) $this->created_at?->toDateString();
    }

    public function getEventBadgeColorAttribute(): string
    {
        return match (Str::lower((string) $this->event)) {
            'created' => 'success',
            'deleted' => 'danger',
            default => 'warning',
        };
    }

    protected function normalizeChangeSet(array $values): array
    {
        return collect($values)
            ->mapWithKeys(function (mixed $value, string $key): array {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }

                if ($value instanceof Collection) {
                    $value = json_encode($value->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }

                if ($value === null || $value === '') {
                    $value = '—';
                }

                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return [$key => (string) $value];
            })
            ->all();
    }
}
