<?php

namespace App\Traits;

trait ApiResponser
{
    public function success($data, $status, $message = null)
    {
        $response = ['status' => $status, 'content' => $data];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, 200);
    }

    public function goReturn($data, $status, $message = null)
    {

        $response = ['status' => $status, 'content' => $data];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, 200);
    }

    protected function error($message, $status = 500)
    {
        return response()->json(['status' => $status, 'error' => $message], $status);
    }

    protected function errorValidations($message, $status = 400)
    {
        return response()->json(['status' => $status, 'error' => $message], $status);
    }

    protected function message($message, $status = 200)
    {
        return $this->success(null, $status, $message);
    }
    protected function successResponse($data, $status = 200, $message = null)
    {
        return $this->success($data, $status, $message);
    }

}
