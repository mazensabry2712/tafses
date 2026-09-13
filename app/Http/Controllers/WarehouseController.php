<?php

namespace App\Http\Controllers;

use App\Actions\CloseColdStoreAction;
use App\Actions\IssueCratesToCustodianAction;
use App\Actions\MoveLoadToColdStoreAction;
use App\Actions\ReturnCustodyAction;
use App\Models\ColdStore;
use App\Models\Custodian;
use App\Models\PomegranateLoad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
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

        return back()->with('success', 'Load moved to cold store successfully.');
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

        return back()->with('success', 'Custody issue recorded successfully.');
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

        return back()->with('success', 'Custody return recorded successfully.');
    }

    public function close(ColdStore $coldStore, CloseColdStoreAction $action): RedirectResponse
    {
        $action->execute($coldStore);

        return back()->with('success', 'Cold store closed successfully.');
    }
}
