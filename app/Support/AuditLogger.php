<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditLogger
{
    public static function log(
        string $event,
        ?Model $subject = null,
        array $properties = [],
        Model|Authenticatable|null $causer = null,
    ): ?Activity {
        $causer = $causer instanceof Model ? $causer : auth()->user();

        $activity = activity('audit')
            ->event($event);

        if ($subject) {
            $activity->performedOn($subject);
        }

        if ($causer instanceof Model) {
            $activity->causedBy($causer);
        }

        $activity->withProperties(array_merge(
            static::subjectProperties($subject),
            static::actorProperties($causer),
            $properties,
        ));

        return $activity->log($event);
    }

    protected static function actorProperties(Model|Authenticatable|null $causer): array
    {
        if (! $causer instanceof User) {
            return [
                'actor_label' => 'System',
            ];
        }

        return [
            'actor_label' => $causer->name,
            'actor_email' => $causer->email,
        ];
    }

    protected static function subjectProperties(?Model $subject): array
    {
        if (! $subject) {
            return [
                'module' => 'System',
                'subject_label' => 'System',
                'subject_identifier' => 'System',
                'subject_type_label' => 'System',
            ];
        }

        return [
            'module' => Str::headline(class_basename($subject)),
            'subject_label' => static::subjectLabel($subject),
            'subject_identifier' => static::subjectIdentifier($subject),
            'subject_type_label' => Str::headline(class_basename($subject)),
        ];
    }

    protected static function subjectLabel(Model $subject): string
    {
        foreach (['display_name', 'full_name', 'name', 'title', 'email', 'emis_code', 'code'] as $attribute) {
            $value = data_get($subject, $attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return class_basename($subject);
    }

    protected static function subjectIdentifier(Model $subject): string
    {
        foreach (['emis_code', 'email', 'code', 'name'] as $attribute) {
            $value = Arr::get($subject->getAttributes(), $attribute);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return '#'.$subject->getKey();
    }
}
