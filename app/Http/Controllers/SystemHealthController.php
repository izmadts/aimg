<?php

namespace App\Http\Controllers;

use App\Services\SystemHealthService;
use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    protected $healthService;

    public function __construct(SystemHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only administrators can run the system health check.');

        $result = $this->healthService->run();

        return view('system-health.index', [
            'issues' => $result['issues'],
            'summary' => $result['summary'],
        ]);
    }

    public function reconcile(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only administrators can reconcile.');

        $applied = $this->healthService->reconcile();

        if (empty($applied)) {
            return redirect()->route('system-health.index')
                ->with('success', 'Nothing to reconcile — no auto-fixable issues were found.');
        }

        return redirect()->route('system-health.index')
            ->with('success', 'Reconciled ' . count($applied) . ' item(s): ' . implode('; ', $applied) . '.');
    }
}
