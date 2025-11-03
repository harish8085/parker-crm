<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterCodeRequest;
use App\Models\MasterCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class MasterCodeController extends Controller
{

    protected $masterCode;

    public function __construct(MasterCode $masterCode)
    {
        $this->masterCode = $masterCode;
    }

    /**
     * Method index
     *
     * @return void
     */
    public function index()
    {
        $masterCodes = $this->masterCode->where('user_id', auth()->user()->id)->first();
        return view('Frontend.master-code.index', compact('masterCodes'));
    }

    public function update(MasterCodeRequest $request)
    {
        $masterCode = $this->masterCode->where('user_id', auth()->user()->id)->first();
        
        if (!$masterCode) {
            $masterCode = $this->masterCode->createCode($request->all());
        } else {
            $masterCode->update($request->all());
        }

        return redirect()->back()->with('success', 'Master code updated successfully');
    }

    public function download(Request $request)
    {
        $masterCode = $this->masterCode->where('user_id', auth()->user()->id)->first();
        $code = $request->query('code', $masterCode?->code ?? '');
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data=' . urlencode($code);

        $response = Http::get($url);
        if (!$response->ok()) {
            abort(404);
        }

        return response($response->body(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="master-code.png"'
        ]);
    }
}