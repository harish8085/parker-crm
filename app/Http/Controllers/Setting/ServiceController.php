<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankProduct;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $Route = 'Settlement';
        $user = Auth::user();

        $services = Service::paginate(25);
        if ($request->ajax()) {

            $query = Service::query();
            if ($request->date) {
                $now = Carbon::now();
                if ($request->date == 'today') {
                    $today = Carbon::today()->toDateString();
                    $query = $query->whereDate('created_at', $today);
                } elseif ($request->date == 'yesterday') {
                    $yesterday = Carbon::yesterday()->toDateString();
                    $query = $query->whereDate('created_at', $yesterday);
                } elseif ($request->date == 'this_week') {
                    $weekStartDate = $now->startOfWeek()->toDateString();
                    $weekEndDate = $now->endOfWeek()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $weekStartDate)
                                  ->whereDate('created_at', '<=', $weekEndDate);
                } elseif ($request->date == 'last_week') {
                    $subWeek = $now->subWeek();
                    $lastWeekStartDate = $subWeek->startOfWeek()->toDateString();
                    $lastWeekEndDate = $subWeek->endOfWeek()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $lastWeekStartDate)
                                  ->whereDate('created_at', '<=', $lastWeekEndDate);
                } elseif ($request->date == 'this_month') {
                    $startOfMonth = $now->startOfMonth()->toDateString();
                    $endOfMonth = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $startOfMonth)
                                  ->whereDate('created_at', '<=', $endOfMonth);
                } elseif ($request->date == 'last_month') {
                    $subMonth = $now->subMonth();
                    $startOfMonth = $subMonth->startOfMonth()->toDateString();
                    $endOfMonth = $subMonth->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $startOfMonth)
                                  ->whereDate('created_at', '<=', $endOfMonth);
                } elseif ($request->date == 'last_3_months') {
                    $thirdLastMonthStart = $now->subMonths(2)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thirdLastMonthStart)
                                  ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'last_6_months') {
                    $Last6thMonthStart = $now->subMonths(5)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $Last6thMonthStart)
                                  ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'this_year') {
                    $thisYearStart = $now->startOfYear()->toDateString();
                    $thisYearEnd = $now->endOfYear()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thisYearStart)
                                  ->whereDate('created_at', '<=', $thisYearEnd);
                } elseif ($request->date == 'last_year') {
                    $lastYear = $now->subYear();
                    $lastYearStart = $lastYear->startOfYear()->toDateString();
                    $lastYearEnd = $lastYear->endOfYear()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $lastYearStart)
                                  ->whereDate('created_at', '<=', $lastYearEnd);
                } elseif ($request->date == 'custom' && isset($request->date_range)) {
                    if (strpos($request->date_range, 'to') !== false) {
                        $dates = explode('to', $request->date_range);
                        $startDate = trim($dates[0]);
                        $endDate = trim($dates[1]);
                        $query = $query->whereDate('created_at', '>=', $startDate)
                                      ->whereDate('created_at', '<=', $endDate);
                    } else {
                        throw new \Exception('Date range is not provided or is incorrectly formatted.');
                    }
                }
            }
            
            if ($request->service_name) {
                $query->where('service_name',$request->service_name);
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('service_name', function ($row) {
                    return $row->service_name ? $row->service_name : '-'; 
                })
                ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/services/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('application', 'delete')) {
                            
                                $btn .= "<img class='delete-service-btn' data-service-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('Frontend.Setting.Service.index', compact('Route', 'services'));
    }

    public function create()
    {
        $Route = 'Settlement';
        $user = Auth::user();
        $products = Product::all();
        return view('Frontend.Setting.Service.create', compact('Route', 'products'));
    }


    public function getServiceProduct(Request $request)
    {
        $bank_id = $request->bank_id;
        $products = BankProduct::where('bank_id', $bank_id)->get();
        return view('Frontend.Setting.Service.component.product', compact('products'));
    }


    public function store(Request $request)
    {
        $service_name = $request->service_name;

        if (!$service_name) {
            return response()->json(['message' => 'Please enter service name'], 400);
        }
        // Check if the service already exists for the given bank
        $checkExist = Service::where('service_name', $service_name)->exists();
        if ($checkExist) {
            return response()->json(['message' => 'Service already exists for this bank'], 400);
        }

        // Create a new service
        $service = new Service();
        $service->service_name = $service_name;
        $service->save();

        // Retrieve the ID of the newly created service
        $service_id = $service->id;

        // Store service details
        foreach ($request->data as $item) {
            foreach ($item['details'] as $detail) {
                // Create a new ServiceDetail instance
                $serviceDetail = new ServiceDetail();
                $serviceDetail->service_id = $service_id; // Associate the service detail with the newly created service
                $serviceDetail->product_name = $item['productId'];
                $serviceDetail->type = $item['serviceType'];
                if ($detail['max_value']) {
                    $serviceDetail->max_value = $detail['max_value'];
                }
                if ($detail['min_value']) {
                    $serviceDetail->min_value = $detail['min_value'];
                }

                if ($detail['percentage']) {
                    $serviceDetail->percentage = $detail['percentage'];
                }

                // Save the service detail
                $serviceDetail->save();
            }
        }

        // Return a success response
        flash()
            ->success('Service created successfully ')
            ->flash();
        return response()->json(['message' => 'Service created successful'], 200);
    }

    public function edit($id)
    {
        $Route = 'Service';
        $user = Auth::user();
        $service = Service::findorfail($id);
        $service_details = ServiceDetail::where('service_id', $id);
        $products = Product::all();
        return view('Frontend.Setting.Service.edit', compact('Route', 'service', 'service_details', 'products'));
    }

    public function update(Request $request, $id)
    {
        $service_name = $request->service_name;

        if (!$service_name) {
            return response()->json(['message' => 'Please enter service name'], 400);
        }

        // Find the existing service
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Check if the service name already exists for another bank
        $checkExist = Service::where('service_name', $service_name)->where('id', '!=', $id)->exists();
        if ($checkExist) {
            return response()->json(['message' => 'Service name already exists for another bank'], 400);
        }

        // Update the service
        $service->service_name = $service_name;
        $service->save();

        // Clear existing service details for the service
        ServiceDetail::where('service_id', $id)->delete();

        // Store updated service details
        foreach ($request->data as $item) {
            foreach ($item['details'] as $detail) {
                // Create a new ServiceDetail instance
                $serviceDetail = new ServiceDetail();
                $serviceDetail->service_id = $service->id; // Associate the service detail with the updated service
                $serviceDetail->product_name = $item['productId'];
                $serviceDetail->type = $item['serviceType'];

                if (isset($detail['max_value'])) {
                    $serviceDetail->max_value = $detail['max_value'];
                }
                if (isset($detail['min_value'])) {
                    $serviceDetail->min_value = $detail['min_value'];
                }
                if (isset($detail['percentage'])) {
                    $serviceDetail->percentage = $detail['percentage'];
                }

                // Save the service detail
                $serviceDetail->save();
            }
        }

        // Return a success response
        return response()->json(['message' => 'Service updated successfully'], 200);
    }
    
    public function destory(Service $service)
    {
        $checkExist = User::where('service_type', $service->id)->get();

        if ($checkExist->isNotEmpty()) {
            return response()->json(['message' => 'This service is assigned to an user'], 400);
        }

        $service->delete();
        return  true;
    }
}
