<?php

namespace App\Adapters;

use App\Adapters\ResponseInterface;
use Response as GlobalResponse;

/* It returns a JSON response with the given data and status */

class Response
{
  
  
  /**
   * It takes in a data, status, and code and returns a json response
   * 
   * @param data The data you want to return.
   * @param status OK or ERROR
   * @param code HTTP status code
   */
  public static function responseJson($data, $status, $code)
  {
    if($status == 'OK') {
      $response = [
        'status' => $status,
        'item'   => $data  
      ];
    } else {
      $response = [
        'status' => $status,
        'msg'    => $data  
      ];
    }

    return response()->json($response, $code);
  }
}
