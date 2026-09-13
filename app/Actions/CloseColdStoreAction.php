<?php

namespace App\Actions;

use App\Models\ColdStore;
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

            $coldStore->is_active = false;
            $coldStore->save();

            return $coldStore;
        });
    }
}
