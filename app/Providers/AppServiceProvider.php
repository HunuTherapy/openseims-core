<?php

namespace App\Providers;

use App\Filament\Imports\AttendanceRecordImporter;
use App\Filament\Imports\IepGoalImporter;
use App\Filament\Imports\LearnerImporter;
use App\Filament\Imports\OfficerImporter;
use App\Filament\Imports\SchoolImporter;
use App\Filament\Imports\TeacherImporter;
use App\Models\User;
use App\Support\AuditLogger;
use Filament\Actions\Action;
use Filament\Actions\Imports\Events\ImportCompleted;
use Filament\Actions\Imports\Events\ImportStarted;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Filament\Support\View\Components\ModalComponent;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            static fn () => view('filament.pollers.import-failure-toast-poller'),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Select::configureUsing(function (Select $component): void {
            $component->native(false);
        });

        SelectFilter::configureUsing(function (SelectFilter $component): void {
            $component->native(false);
        });

        ModalComponent::closedByClickingAway(false);
        ModalComponent::closedByEscaping(false);

        Table::configureUsing(function (Table $table): void {
            //            $table->defaultSort('created_at', 'desc');
            $table->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            );
        });

        Model::unguard();

        Event::listen(Login::class, function (Login $event): void {
            AuditLogger::log('login', $event->user, [
                'module' => 'Authentication',
                'subject_label' => $event->user->name,
                'subject_identifier' => $event->user->email,
                'subject_type_label' => 'User',
                'guard' => $event->guard,
            ], $event->user);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditLogger::log('logout', $event->user, [
                'module' => 'Authentication',
                'subject_label' => $event->user?->name ?? 'User',
                'subject_identifier' => $event->user?->email ?? 'User',
                'subject_type_label' => 'User',
                'guard' => $event->guard,
            ], $event->user);
        });

        Event::listen(ImportStarted::class, function (ImportStarted $event): void {
            $import = $event->getImport();

            if (! $this->shouldAuditImport($import)) {
                return;
            }

            AuditLogger::log('import_started', null, $this->importProperties($import), $import->user);
        });

        // check if recent import had any failures, if yes, delete entire batch
        Event::listen(ImportCompleted::class, function (ImportCompleted $event): void {
            $import = $event->getImport();

            if (! $this->shouldAuditImport($import)) {
                return;
            }

            $auditEvent = $import->getFailedRowsCount() > 0 ? 'import_failed' : 'import_completed';
            AuditLogger::log($auditEvent, null, array_merge(
                $this->importProperties($import),
                [
                    'processed_rows' => $import->processed_rows,
                    'successful_rows' => $import->successful_rows,
                    'failed_rows' => $import->getFailedRowsCount(),
                ],
            ), $import->user);

            if (! $import->user) {
                return;
            }

            Notification::make()
                ->title($import->importer::getCompletedNotificationTitle($import))
                ->body($import->importer::getCompletedNotificationBody($import))
                ->danger()
                ->broadcast($import->user);
        });

        Gate::define('viewApiDocs', function (?User $user = null) {
            return $user !== null;   // true  → logged-in
        });
    }

    protected function shouldAuditImport(Import $import): bool
    {
        return in_array($import->importer, [
            SchoolImporter::class,
            TeacherImporter::class,
            OfficerImporter::class,
            AttendanceRecordImporter::class,
            LearnerImporter::class,
            IepGoalImporter::class,
        ], true);
    }

    protected function importProperties(Import $import): array
    {
        return [
            'module' => 'Imports',
            'subject_label' => class_basename($import->importer),
            'subject_identifier' => $import->file_name,
            'subject_type_label' => 'Import',
            'importer' => $import->importer,
            'file_name' => $import->file_name,
            'total_rows' => $import->total_rows,
        ];
    }
}
