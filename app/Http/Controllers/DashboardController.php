<?php

namespace App\Http\Controllers;

use App\Actions\GetOperationalReportAction;
use App\Models\ColdStore;
use App\Models\Customer;
use App\Models\Custodian;
use App\Models\FinishedProduct;
use App\Models\PomegranateLoad;
use App\Models\PomegranatePurchase;
use App\Models\Supplier;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(GetOperationalReportAction $reportAction): View
    {
        $today = Carbon::today();

        return view('dashboard', [
            'report' => $reportAction->execute($today, $today),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'vehicles' => Vehicle::query()->where('is_active', true)->orderBy('plate_number')->get(),
            'coldStores' => ColdStore::query()->with(['crateStandard', 'stocks.pomegranateLoad'])->where('is_active', true)->orderBy('name')->get(),
            'loadsOnVehicle' => PomegranateLoad::query()->with(['supplier', 'vehicle'])->where('on_vehicle_crates_count', '>', 0)->orderByDesc('received_at')->get(),
            'custodians' => Custodian::query()->where('is_active', true)->orderBy('name')->get(),
            'processingLoads' => PomegranateLoad::query()->with(['coldStoreStocks.coldStore'])->where('available_crates_count', '>', 0)->where('available_weight_kg', '>', 0)->orderByDesc('received_at')->get(),
            'products' => FinishedProduct::query()->with('stock')->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
            'sales' => \App\Models\FinishedProductSale::query()->with('customer')->whereColumn('total_amount', '>', 'paid_amount')->orderByDesc('sold_at')->limit(20)->get(),
            'purchases' => PomegranatePurchase::query()->with(['supplier', 'pomegranateLoad'])->whereColumn('total_amount', '>', 'paid_amount')->orderByDesc('purchased_at')->limit(20)->get(),
        ]);
    }
}
