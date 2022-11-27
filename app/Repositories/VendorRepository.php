<?php

namespace App\Repository;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor ;
use Throwable;

class VendorRepo {

  public static function getVendor($id) {
    try {
      $data = [];
      
      return $data;
    } catch(Throwable $e) {
      return $e->getMessage();
    }
  }
}