<?php

namespace App\Models\Concerns;

use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait Auditable
{
    use LogsActivity {
        shouldLogEvent as protected logsActivityShouldLogEvent;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('audit')
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept($this->getAuditExcludedAttributes())
            ->dontLogIfAttributesChangedOnly($this->getAuditIgnoredOnlyAttributes())
            ->setDescriptionForEvent(fn (string $eventName): string => $eventName);
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $properties = $activity->properties?->toArray() ?? [];

        $activity->properties = collect(array_merge([
            'module' => $this->getAuditModuleName(),
            'subject_label' => $this->getAuditSubjectLabel(),
            'subject_identifier' => $this->getAuditSubjectIdentifier(),
            'subject_type_label' => Str::headline(class_basename(static::class)),
        ], $properties));

        $activity->event = $eventName;
        $activity->description = $eventName;
    }

    protected function shouldLogEvent(string $eventName): bool
    {
        return $this->logsActivityShouldLogEvent($eventName);
    }

    protected function getAuditExcludedAttributes(): array
    {
        return array_values(array_unique(array_merge([
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
            'password',
        ], $this->auditExcept ?? [])));
    }

    protected function getAuditIgnoredOnlyAttributes(): array
    {
        return array_values(array_unique(array_merge([
            'created_at',
            'updated_at',
        ], $this->auditOnlyIgnore ?? [])));
    }

    protected function getAuditModuleName(): string
    {
        return Str::headline(class_basename(static::class));
    }

    protected function getAuditSubjectLabel(): string
    {
        $candidates = [
            'display_name',
            'full_name',
            'name',
            'title',
            'email',
            'emis_code',
            'code',
        ];

        foreach ($candidates as $attribute) {
            $value = data_get($this, $attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return Str::headline(class_basename(static::class));
    }

    protected function getAuditSubjectIdentifier(): string
    {
        $candidates = [
            'emis_code',
            'email',
            'code',
            'name',
        ];

        foreach ($candidates as $attribute) {
            $value = Arr::get($this->getAttributes(), $attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return '#'.$this->getKey();
    }
}
