<?php

namespace App\Actions;

use App\Models\FinishedProduct;

class GetFinishedProductStockAction
{
    public function execute(FinishedProduct $product): array
    {
        $stock = $product->stock()->first();

        return [
            'product' => $product->name,
            'code' => $product->code,
            'type' => $product->type,
            'unit' => $product->unit,
            'quantity' => (float) ($stock?->quantity ?? 0),
        ];
    }
}
