<?php

namespace App\Http\Middleware;

use App\Services\Reporting\ActiveReportingYear;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveReportingYear
{
    public function __construct(
        private readonly ActiveReportingYear $activeReportingYear,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        config([
            'seims.active_reporting_year' => $this->activeReportingYear->current($request->user()),
        ]);

        return $next($request);
    }
}
