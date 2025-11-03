<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getDistrict($state_code)
    {
        $states = getState();
        foreach ($states as $state) {
            if ($state['state_code'] === $state_code) {
                $districts =  $state['districts'];
            }
        }

        $option ='';
        foreach($districts as $district ){
            $option.='<option>'.$district.'</option>';
        }


        return $option;
    }

    /**
     * Method successResponse
     *
     * @param mixed  $data    [explicite description]
     * @param string $message [explicite description]
     *
     * @return JsonResponse
     */
    function successResponse($data = [], $message = '')
    {
        return response()->json(
            [
                'success' => true, 
                'data' => $data, 
                'message' => $message
            ]
        );
    }

    /**
     * Method errorResponse
     *
     * @param string $message   [explicite description]
     * @param int    $errorCode [explicite description]
     * @param mixed  $data      [explicite description]
     *
     * @return JsonResponse
     */
    function errorResponse(
        string $message = '',
        int $errorCode = 422,
        $data = []
    ) {
        $response = [
            'success' => false, 
            'error' => [
                'message' => $message
            ]
        ];
        if ($data) {
            $response['data'] = $data;
        }
        return response()->json($response, $errorCode);
    }
}
