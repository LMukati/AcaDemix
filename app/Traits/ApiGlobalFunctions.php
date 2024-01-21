<?php

namespace App\Traits;

trait ApiGlobalFunctions
{

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'status' => true,
            'code' => 200,
            'message' => $message,
            'data' => $result

        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'status' => false,
            'code' => 200,
            'message' => !empty($errorMessages) ? $errorMessages : $error,
            'data' => (object)[]
        ];
        return response()->json($response, $code);
    }
    
    public function checkemail($str) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    public function checkmobile($mobile)
    {
        return preg_match('/^[0-9]{10}+$/', $mobile);
    }
}
