<?php

namespace App\Actions;

use App\Models\ColdStore;
use App\Models\CustodyTransaction;
use Illuminate\Support\Facades\DB;

class GetCustodySummaryAction
{
    public function execute(ColdStore $coldStore): array
    {
        $aggregateRows = DB::table('custodians')
            ->join('custody_transactions', 'custodians.id', '=', 'custody_transactions.custodian_id')
            ->where('custody_transactions.cold_store_id', $coldStore->id)
            ->where('custodians.is_active', true)
            ->groupBy('custodians.id', 'custodians.name')
            ->selectRaw("custodians.id, custodians.name,
                SUM(CASE WHEN custody_transactions.type = 'issue' THEN custody_transactions.crates_count ELSE 0 END) AS issued_crates,
                SUM(CASE WHEN custody_transactions.type = 'issue' THEN custody_transactions.weight_kg ELSE 0 END) AS issued_weight_kg,
                SUM(CASE WHEN custody_transactions.type IN ('return_to_vehicle', 'return_to_store') THEN custody_transactions.crates_count ELSE 0 END) AS returned_crates,
                SUM(CASE WHEN custody_transactions.type IN ('return_to_vehicle', 'return_to_store') THEN custody_transactions.weight_kg ELSE 0 END) AS returned_weight_kg")
            ->orderBy('custodians.name')
            ->get();

        $transactions = CustodyTransaction::query()
            ->with('pomegranateLoad:id,load_number')
            ->where('cold_store_id', $coldStore->id)
            ->orderBy('custodian_id')
            ->orderBy('moved_at')
            ->orderBy('id')
            ->get()
            ->groupBy('custodian_id');

        $rows = $aggregateRows->map(function ($row) use ($transactions) {
            $entries = ($transactions->get($row->id) ?? collect())->map(fn (CustodyTransaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'crates_count' => $transaction->crates_count,
                'weight_kg' => (float) $transaction->weight_kg,
                'load_number' => $transaction->pomegranateLoad?->load_number,
                'moved_at' => $transaction->moved_at?->toDateTimeString(),
                'notes' => $transaction->notes,
            ])->values()->all();

            return [
                'custodian_id' => (int) $row->id,
                'name' => $row->name,
                'issued_crates' => (int) $row->issued_crates,
                'returned_crates' => (int) $row->returned_crates,
                'outstanding_crates' => (int) $row->issued_crates - (int) $row->returned_crates,
                'issued_weight_kg' => (float) $row->issued_weight_kg,
                'returned_weight_kg' => (float) $row->returned_weight_kg,
                'outstanding_weight_kg' => round((float) $row->issued_weight_kg - (float) $row->returned_weight_kg, 3),
                'entries' => $entries,
            ];
        })->values()->all();

        return [
            'cold_store' => $coldStore->name,
            'current' => [
                'crates_count' => $coldStore->current_crates_count,
                'weight_kg' => (float) $coldStore->current_weight_kg,
            ],
            'custodians' => $rows,
            'total' => [
                'issued_crates' => array_sum(array_column($rows, 'issued_crates')),
                'returned_crates' => array_sum(array_column($rows, 'returned_crates')),
                'outstanding_crates' => array_sum(array_column($rows, 'outstanding_crates')),
            ],
        ];
    }
}
