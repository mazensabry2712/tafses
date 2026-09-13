<?php

namespace App\Actions;

use App\Models\ColdStore;
use App\Models\CustodyTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CloseColdStoreAction
{
    public function execute(ColdStore $coldStore): ColdStore
    {
        return DB::transaction(function () use ($coldStore) {
            $coldStore = ColdStore::query()->lockForUpdate()->findOrFail($coldStore->id);

            if (!$coldStore->is_active) {
                throw new RuntimeException('The cold store is already closed.');
            }

            if ($coldStore->current_crates_count > 0 || (float) $coldStore->current_weight_kg > 0) {
                throw new RuntimeException('A cold store cannot be closed while it still contains stock.');
            }

            $outstandingCustody = CustodyTransaction::query()
                ->where('cold_store_id', $coldStore->id)
                ->selectRaw("SUM(CASE WHEN type = 'issue' THEN crates_count ELSE -crates_count END) AS crates")
                ->value('crates');

            if ((int) $outstandingCustody > 0) {
                throw new RuntimeException('A cold store cannot be closed while custody crates are still outstanding.');
            }

            $coldStore->is_active = false;
            $coldStore->save();

            return $coldStore;
        });
    }
}
