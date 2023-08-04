<?php

/**
 * | Author - Anshu Kumar
 * | Created On-17-07-2023 
 * | Created for the user defined helper functions
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * | Response Msg Version2 with apiMetaData
 */
if (!function_exists("responseMsgs")) {
    function responseMsgs($status, $msg, $data, $apiId = null, $version = null, $deviceId = null)
    {
        return response()->json([
            'status' => $status,
            'message' => $msg,
            'meta-data' => [
                'apiId' => $apiId,
                'version' => $version,
                'responsetime' => responseTime(),
                'epoch' => Carbon::now()->format('Y-m-d H:i:m'),
                'action' => "POST",
                'deviceId' => $deviceId
            ],
            'data' => $data
        ]);
    }
}

/**
 * | To through Validation Error
 */
if (!function_exists("validationError")) {
    function validationError($validator)
    {
        return response()->json([
            'status'  => false,
            'message' => 'validation error',
            'errors'  => $validator->errors()
        ], 422);
    }
}



if (!function_exists("remove_null")) {
    function remove_null($data, $encrypt = false, array $key = ["id"])
    {
        $collection = collect($data)->map(function ($name, $index) use ($encrypt, $key) {
            if (is_object($name) || is_array($name)) {
                return remove_null($name, $encrypt, $key);
            } else {
                if ($encrypt && (in_array(strtolower($index), array_map(function ($keys) {
                    return strtolower($keys);
                }, $key)))) {
                    return Crypt::encrypt($name);
                } elseif (is_null($name))
                    return "";
                else
                    return $name;
            }
        });
        return $collection;
    }
}


if (!function_exists('roundFigure')) {
    function roundFigure(float $number)
    {
        $round = round($number, 2);
        return number_format((float)$round, 2, '.', '');
    }
}


if (!function_exists("responseTime")) {
    function responseTime()
    {
        $responseTime = (microtime(true) - LARAVEL_START) * 1000;
        return round($responseTime, 2);
    }
}


if (!function_exists("tableSchema")) {
    function tableSchema($table)
    {
        $response = array();
        $response['payload'] = array();
        $schema = Schema::getColumnListing($table);
        foreach ($schema as $value) {
            $field_name = str_replace('_', '', ucwords($value, '_'));
            $field_name = str_replace(' ', '', $field_name);
            $field_name = lcfirst($field_name);
            array_push($response['payload'], $field_name);
        }
        $response['schema'] = $schema;
        return $response;
    }
}
