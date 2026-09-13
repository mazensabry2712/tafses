<?php

namespace App\Http\Controllers;

use App\Actions\CreatePomegranateLoadAction;
use App\Models\Supplier;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReceivingController extends Controller
{
    public function index(): View
    {
        return view('receiving.index', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'vehicles' => Vehicle::query()->where('is_active', true)->orderBy('plate_number')->get(),
        ]);
    }

    public function store(Request $request, CreatePomegranateLoadAction $createLoad): RedirectResponse
    {
        $data = $request->validate([
            'load_number' => ['required', 'string', 'max:100'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'crates_count' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['required', 'numeric', 'gt:0'],
            'received_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $createLoad->execute(
            $data['load_number'],
            $data['supplier_id'] ?? null,
            $data['vehicle_id'] ?? null,
            (int) $data['crates_count'],
            (float) $data['weight_kg'],
            isset($data['received_at']) ? Carbon::parse($data['received_at']) : null,
            $data['notes'] ?? null,
        );

        return back()->with('success', 'تم تسجيل الشحنة بنجاح.');
    }
}
