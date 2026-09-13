<?php

namespace App\Http\Controllers;

use App\Actions\CloseColdStoreAction;
use App\Models\ColdStore;
use App\Models\CrateStandard;
use App\Models\Custodian;
use App\Models\FinishedProduct;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function index()
    {
        return view('master-data.index', [
            'vehicles' => Vehicle::query()->orderBy('plate_number')->get(),
            'coldStores' => ColdStore::query()->with('crateStandard')->orderBy('name')->get(),
            'custodians' => Custodian::query()->orderBy('name')->get(),
            'crateStandards' => CrateStandard::query()->orderBy('name')->get(),
            'products' => FinishedProduct::query()->with('stock')->orderBy('name')->get(),
        ]);
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', 'unique:vehicles,plate_number'],
            'type' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:150'],
            'driver_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        Vehicle::create($data);

        return back()->with('success', 'تم إضافة المركبة بنجاح.');
    }

    public function storeColdStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:cold_stores,code'],
            'crate_standard_id' => ['nullable', 'integer', 'exists:crate_standards,id'],
            'notes' => ['nullable', 'string'],
        ]);

        ColdStore::create([
            ...$data,
            'current_crates_count' => 0,
            'current_weight_kg' => 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة البراد بنجاح.');
    }

    public function closeColdStore(ColdStore $coldStore, CloseColdStoreAction $action): RedirectResponse
    {
        $action->execute($coldStore);

        return back()->with('success', 'تم إغلاق البراد بعد تسوية المخزون والعُهد.');
    }

    public function storeCustodian(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        Custodian::create([
            ...$data,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة صاحب العهدة بنجاح.');
    }

    public function toggleCustodian(Custodian $custodian): RedirectResponse
    {
        $custodian->update(['is_active' => !$custodian->is_active]);

        return back()->with('success', $custodian->is_active ? 'تم تفعيل صاحب العهدة.' : 'تم تعطيل صاحب العهدة.');
    }

    public function storeCrateStandard(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'gross_weight_kg' => ['required', 'numeric', 'gt:0'],
            'tare_weight_kg' => ['required', 'numeric', 'gte:0', 'lt:gross_weight_kg'],
            'notes' => ['nullable', 'string'],
        ]);

        CrateStandard::create([
            ...$data,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة معيار القفص بنجاح.');
    }

    public function toggleCrateStandard(CrateStandard $crateStandard): RedirectResponse
    {
        $crateStandard->update(['is_active' => !$crateStandard->is_active]);

        return back()->with('success', $crateStandard->is_active ? 'تم تفعيل معيار القفص.' : 'تم تعطيل معيار القفص.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', 'unique:finished_products,code'],
            'type' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
        ]);

        FinishedProduct::create([
            ...$data,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة المنتج النهائي بنجاح.');
    }

    public function toggleProduct(FinishedProduct $product): RedirectResponse
    {
        $product->update(['is_active' => !$product->is_active]);

        return back()->with('success', $product->is_active ? 'تم تفعيل المنتج.' : 'تم تعطيل المنتج.');
    }
}
