<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function sendSuccess($msg,$data=array(),$status=200)
    {
        return response()->json(['status' => true,'message' => $msg,'response' => $data],$status,[JSON_NUMERIC_CHECK]);
    }

    function sendError($msg,$status=200)
    {
        return response()->json(['status' => false,'message' => $msg],$status);
    }
}
