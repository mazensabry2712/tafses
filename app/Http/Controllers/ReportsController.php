<?php

namespace App\Http\Controllers;

use App\Actions\GetOperationalReportAction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request, GetOperationalReportAction $report)
    {
        $from = Carbon::parse($request->input('from', now()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        if ($from->greaterThan($to)) {
            abort(422, 'The report start date must be before or equal to the end date.');
        }

        return view('reports.index', [
            'summary' => $report->execute($from, $to),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }
}
