<?php

namespace App\Http\Controllers;

use App\Actions\CloseColdStoreAction;
use App\Actions\GetCustodySummaryAction;
use App\Actions\IssueCratesToCustodianAction;
use App\Actions\MoveLoadToColdStoreAction;
use App\Actions\ReturnCustodyAction;
use App\Models\ColdStore;
use App\Models\Custodian;
use App\Models\CustodyTransaction;
use App\Models\PomegranateLoad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouse.index', [
            'coldStores' => ColdStore::query()
                ->with('crateStandard')
                ->orderBy('name')
                ->get(),
            'loads' => PomegranateLoad::query()
                ->with(['supplier', 'vehicle'])
                ->where('on_vehicle_crates_count', '>', 0)
                ->orderByDesc('received_at')
                ->get(),
            'custodians' => Custodian::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function custody(GetCustodySummaryAction $summaryAction)
    {
        $coldStores = ColdStore::query()
            ->with('crateStandard')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $summaries = $coldStores
            ->mapWithKeys(fn (ColdStore $store) => [$store->id => $summaryAction->execute($store)])
            ->all();

        $openHoldings = CustodyTransaction::query()
            ->with(['custodian:id,name', 'coldStore:id,name', 'pomegranateLoad:id,load_number'])
            ->where('type', 'issue')
            ->selectRaw('custodian_id, cold_store_id, pomegranate_load_id,
                SUM(crates_count) AS issued_crates,
                SUM(weight_kg) AS issued_weight_kg')
            ->groupBy('custodian_id', 'cold_store_id', 'pomegranate_load_id')
            ->get()
            ->map(function (CustodyTransaction $row) {
                $returned = CustodyTransaction::query()
                    ->where('custodian_id', $row->custodian_id)
                    ->where('cold_store_id', $row->cold_store_id)
                    ->where('pomegranate_load_id', $row->pomegranate_load_id)
                    ->whereIn('type', ['return_to_vehicle', 'return_to_store'])
                    ->selectRaw('COALESCE(SUM(crates_count), 0) AS crates, COALESCE(SUM(weight_kg), 0) AS weight')
                    ->first();

                $crates = (int) $row->issued_crates - (int) ($returned?->crates ?? 0);
                $weight = round((float) $row->issued_weight_kg - (float) ($returned?->weight ?? 0), 3);

                return [
                    'custodian' => $row->custodian,
                    'cold_store' => $row->coldStore,
                    'load' => $row->pomegranateLoad,
                    'crates_count' => max(0, $crates),
                    'weight_kg' => max(0, $weight),
                ];
            })
            ->filter(fn (array $row) => $row['crates_count'] > 0 && $row['weight_kg'] > 0)
            ->values();

        return view('warehouse.custody', [
            'summaries' => $summaries,
            'openHoldings' => $openHoldings,
        ]);
    }

    public function moveToColdStore(Request $request, PomegranateLoad $load, MoveLoadToColdStoreAction $action): RedirectResponse
    {
        $data = $request->validate([
            'cold_store_id' => ['required', 'integer', 'exists:cold_stores,id'],
            'crates_count' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['nullable', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $action->execute(
            $load,
            ColdStore::findOrFail($data['cold_store_id']),
            (int) $data['crates_count'],
            isset($data['weight_kg']) ? (float) $data['weight_kg'] : null,
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم نقل الحمولة إلى البراد بنجاح.');
    }

    public function issue(Request $request, ColdStore $coldStore, PomegranateLoad $load, Custodian $custodian, IssueCratesToCustodianAction $action): RedirectResponse
    {
        $data = $request->validate([
            'crates_count' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['nullable', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $action->execute(
            $coldStore,
            $load,
            $custodian,
            (int) $data['crates_count'],
            isset($data['weight_kg']) ? (float) $data['weight_kg'] : null,
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم تسجيل صرف العُهدة بنجاح.');
    }

    public function returnCustody(Request $request, ColdStore $coldStore, PomegranateLoad $load, Custodian $custodian, ReturnCustodyAction $action): RedirectResponse
    {
        $data = $request->validate([
            'crates_count' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['nullable', 'numeric', 'gt:0'],
            'destination' => ['required', 'in:vehicle,cold_store'],
            'notes' => ['nullable', 'string'],
        ]);

        $action->execute(
            $coldStore,
            $load,
            $custodian,
            (int) $data['crates_count'],
            isset($data['weight_kg']) ? (float) $data['weight_kg'] : null,
            $data['destination'],
            $request->user()->id,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم تسجيل إرجاع العُهدة بنجاح.');
    }

    public function close(ColdStore $coldStore, CloseColdStoreAction $action): RedirectResponse
    {
        $action->execute($coldStore);

        return back()->with('success', 'تم إغلاق البراد بنجاح.');
    }
}
