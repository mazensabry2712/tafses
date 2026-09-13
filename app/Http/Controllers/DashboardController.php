<?php

namespace App\Http\Controllers;

use App\Actions\GetOperationalReportAction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(GetOperationalReportAction $reportAction): View
    {
        $today = Carbon::today();

        return view('dashboard', [
            'report' => $reportAction->execute($today, $today),
        ]);
    }
}
