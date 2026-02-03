<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'master-data';

        if ($request->ajax()) {

            $query = Settings::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', fn($row) => $row->name ?? '-')
                ->editColumn('value', fn($row) => $row->value ?? '-')
                ->editColumn('created_by', fn($row) => $row->created_by ?? '-')
                ->editColumn('updated_by', fn($row) => $row->updated_by ?? '-')
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->hasPermission('dsa-code', 'update')) {
                        $btn .= '<img 
                    src="' . asset('assets/images/Edit.svg') . '" 
                    style="cursor:pointer"
                    onclick="updateMasterData(' . $row->id . ')">';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Frontend.MasterData.index', compact('Route'));
    }

    public function edit($id)
    {
        $data = Settings::findOrFail($id);
        return response()->json($data);
    }



    public function create(Request $request)
    {

        $request->validate([
            'name'  => 'required|string',
            'value' => 'required|string',
        ]);

        $user = Auth::user();

        $fullName = trim($user->first_name . ' ' . $user->last_name);

        $masterData = new Settings();
        $masterData->name = $request->name;
        $masterData->value = $request->value;
        $masterData->created_by = $fullName;
        $masterData->updated_by = $fullName;

        $masterData->save();

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string',
            'value' => 'required|string',
        ]);
        $fullName = trim($user->first_name . ' ' . $user->last_name);

        $data = Settings::findOrFail($id);
        $data->name = $request->name;
        $data->value = $request->value;
        $data->updated_by = $fullName;
        $data->save();

        return response()->json(['success' => true]);
    }
}
