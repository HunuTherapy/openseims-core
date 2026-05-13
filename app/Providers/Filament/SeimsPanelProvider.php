<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\OfficerResource;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\InclusiveEducationPolicies;
use App\Filament\Resources\AssessmentFormResource;
use App\Filament\Resources\AssessmentResource;
use App\Filament\Resources\AttendanceRecordResource;
use App\Filament\Resources\CpdModuleResource;
use App\Filament\Resources\IepGoalResource;
use App\Filament\Resources\LearnerResource;
use App\Filament\Resources\SchoolResource;
use App\Filament\Resources\SupervisionResource;
use App\Filament\Resources\TeacherResource;
use App\Http\Middleware\SetActiveReportingYear;
use App\Models\SupervisionReport;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

use Rupadana\ApiService\ApiServicePlugin;

class SeimsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('seims')
            ->path('')
            ->brandLogo(asset('img/logo.png'))
            ->brandLogoHeight('60px')
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): string => Blade::render(<<<'BLADE'
                    <div class="flex items-center">
                        <span aria-hidden="true" class="mx-5 h-[31px] border-l-2 border-[#D4D4D4]"></span>
                        <span class="whitespace-nowrap font-[Inter,ui-sans-serif,system-ui,sans-serif] text-base font-semibold leading-9 tracking-[0.4px] text-[var(--color-warning-500)]">
                            Special Education Information Management System
                        </span>
                    </div>
                BLADE),
            )
            ->login()
            ->colors([
                'primary' => Color::generateV3Palette('#F59E0B'),
                'warning' => [
                    50 => '#fff7ed',
                    100 => '#ffedd5',
                    200 => '#fed7aa',
                    300 => '#f59e0b',
                    400 => '#f59e0b',
                    500 => '#f59e0b',
                    600 => '#d97706',
                    700 => '#b45309',
                    800 => '#92400e',
                    900 => '#78350f',
                    950 => '#451a03',
                ],
            ])
            ->breadcrumbs(false)
            ->maxContentWidth(Width::Full)
            ->sidebarWidth('18rem')
            ->sidebarCollapsibleOnDesktop()
            ->viteTheme('resources/css/filament/seims/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([OfficerResource::class])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->homeUrl('/')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => $builder->groups([
                NavigationGroup::make()
                    ->collapsible(false)
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-o-rectangle-group')
                            ->url(Dashboard::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.pages.dashboard'))
                            ->visible(fn (): bool => Dashboard::canAccess()),
                        NavigationItem::make('Assessments')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->url(AssessmentResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.assessments.*'))
                            ->visible(fn (): bool => AssessmentResource::canAccess()),
                        NavigationItem::make('Individual Education Plans')
                            ->icon('heroicon-o-academic-cap')
                            ->url(IepGoalResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.iep-goals.*'))
                            ->visible(fn (): bool => IepGoalResource::canAccess()),
                        NavigationItem::make('Supervision Reports')
                            ->icon('heroicon-o-document-chart-bar')
                            ->url(SupervisionResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.supervisions.*') && ! request()->routeIs('filament.seims.resources.supervisions.my-reports'))
                            ->visible(fn (): bool => auth()->user()?->hasPermissionTo('view all reports') ?? false),
                        NavigationItem::make('My Reports')
                            ->icon('heroicon-o-inbox-arrow-down')
                            ->url(SupervisionResource::getUrl('my-reports'))
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.supervisions.my-reports'))
                            ->badge(fn (): string => (string) SupervisionReport::query()
                                ->where('recipient_id', auth()->id())
                                ->count())
                            ->visible(fn (): bool => auth()->user()?->hasPermissionTo('view assigned reports') ?? false),
                    ]),
                NavigationGroup::make('Lists')
                    ->collapsible()
                    ->items([
                        NavigationItem::make('Learners')
                            ->icon('heroicon-o-user-group')
                            ->url(LearnerResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.learners.*'))
                            ->visible(fn (): bool => LearnerResource::canAccess()),
                        NavigationItem::make('Special Education Officers')
                            ->icon('heroicon-o-user-plus')
                            ->url(OfficerResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.officers.*'))
                            ->visible(fn (): bool => OfficerResource::canAccess()),
                        NavigationItem::make('Teacher Lists')
                            ->icon('heroicon-o-book-open')
                            ->url(TeacherResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.teachers.*'))
                            ->visible(fn (): bool => TeacherResource::canAccess()),
                        NavigationItem::make('Schools')
                            ->icon('heroicon-o-building-library')
                            ->url(SchoolResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.schools.*'))
                            ->visible(fn (): bool => SchoolResource::canAccess()),
                        NavigationItem::make('Attendance')
                            ->icon('heroicon-o-calendar-days')
                            ->url(AttendanceRecordResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.attendance-records.*'))
                            ->visible(fn (): bool => AttendanceRecordResource::canAccess()),
                    ]),
                NavigationGroup::make('Resources')
                    ->collapsible()
                    ->items([
                        NavigationItem::make('Assessment Forms')
                            ->icon('heroicon-o-folder')
                            ->url(AssessmentFormResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.assessment-forms.*'))
                            ->visible(fn (): bool => AssessmentFormResource::canAccess()),
                        NavigationItem::make('Training Modules')
                            ->icon('heroicon-o-queue-list')
                            ->url(CpdModuleResource::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.resources.cpd-modules.*'))
                            ->visible(fn (): bool => CpdModuleResource::canAccess()),
                        NavigationItem::make('Inclusive Education Policies')
                            ->icon('heroicon-o-scale')
                            ->url(InclusiveEducationPolicies::getUrl())
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.seims.pages.inclusive-education-policies'))
                            ->visible(fn (): bool => IepGoalResource::canAccess()),
                    ]),
            ]))
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SetActiveReportingYear::class,
            ])
            ->plugins([
                FilamentApexChartsPlugin::make(),
                ApiServicePlugin::make(),
            ])
            ->unsavedChangesAlerts(! app()->isLocal());
    }
}
