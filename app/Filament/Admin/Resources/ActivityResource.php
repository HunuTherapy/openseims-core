<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityResource\Pages\ListActivities;
use App\Models\Activity;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?int $navigationSort = 90;

    public static function getNavigationLabel(): string
    {
        return 'Audit Logs';
    }

    public static function getModelLabel(): string
    {
        return 'Audit log';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Audit logs';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('national_admin') ?? false;
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->sinceTooltip()
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('actor_label')
                    ->label('Actor')
                    ->description(fn (Activity $record): ?string => $record->actor_email)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->where('properties->actor_label', 'like', "%{$search}%")
                                ->orWhere('properties->actor_email', 'like', "%{$search}%")
                                ->orWhereHasMorph('causer', [User::class], function (Builder $query) use ($search): void {
                                    $query
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn (Activity $record): string => $record->event_badge_color)
                    ->searchable(),
                TextColumn::make('module')
                    ->badge()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('properties->module', 'like', "%{$search}%")),
                TextColumn::make('subject_type_label')
                    ->label('Entity')
                    ->toggleable()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('properties->subject_type_label', 'like', "%{$search}%")),
                TextColumn::make('subject_label')
                    ->label('Record')
                    ->description(fn (Activity $record): string => $record->subject_identifier)
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->where('properties->subject_label', 'like', "%{$search}%")
                                ->orWhere('properties->subject_identifier', 'like', "%{$search}%")
                                ->orWhere('subject_type', 'like', "%{$search}%");
                        });
                    }),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Action')
                    ->options(fn (): array => Activity::query()->pluck('event')->filter()->unique()->sort()->mapWithKeys(fn (string $event): array => [$event => $event])->all()),
                SelectFilter::make('module')
                    ->options(fn (): array => Activity::query()->get()->pluck('module')->filter()->unique()->sort()->mapWithKeys(fn (string $module): array => [$module => $module])->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'] ?? null, fn (Builder $query, string $value): Builder => $query->where('properties->module', $value))),
                SelectFilter::make('subject_type')
                    ->label('Entity')
                    ->options(fn (): array => Activity::query()->get()->pluck('subject_type_label')->filter()->unique()->sort()->mapWithKeys(fn (string $type): array => [$type => $type])->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'] ?? null, fn (Builder $query, string $value): Builder => $query->where('properties->subject_type_label', $value))),
                Filter::make('actor')
                    ->schema([
                        Select::make('causer_id')
                            ->label('Actor')
                            ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['causer_id'] ?? null,
                        fn (Builder $query, int $value): Builder => $query
                            ->where('causer_type', app(User::class)->getMorphClass())
                            ->where('causer_id', $value)
                    )),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
            ])
            ->toolbarActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
