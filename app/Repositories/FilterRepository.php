<?php

namespace App\Repositories;

use App\Models\Solution;
use App\Models\SubSolution;
use App\Models\User;
use App\Models\Provinces;
use App\Models\Geography;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\Table;
use Throwable;

/* It's a class that contains static methods that return data from the database */

class FilterRepository
{

  public static function region($id)
  {
    $shopId = $id;
    try {
      $query = User::where('shop_id', $shopId)
        ->whereNotNull('department')
        ->where('department', '!=', '')
        ->groupBy('department')
        ->orderBy('department', 'asc')
        ->get('department as name');

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function user($id)
  {
    $shopId = $id;
    try {
      $query = User::where('shop_id', $shopId)
        ->select('id', 'name')
        ->get();

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function userAdmin($id, $search)
  {
    $shopId = $id;
    try {
      $query = User::where('shop_id', $shopId)
        ->select('id', 'name');
      if ($search) {
        $query->where('name', 'LIKE', "%$search%");
      }
      $query = $query->get();

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function solution($shopId, $userId, $search = '')
  {
    try {
      $queryUser = User::where('id', $userId)->select('department')->first();

      $query = Solution::where('shop_id', $shopId)->where('active', '1');

      if ($queryUser['department'] == 'South') {
        $query->where('status_chain', 2);
      } else {
        $query->where('status_chain', 1);
      }

      if (!empty($search)) {
        $query->where('name', 'LIKE', "%{$search}%");
      }

      $query = $query->orderBy('name', 'asc')->get();

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function getSolutionAll($shopId)
  {
    $data = [];
    $query = Solution::where('shop_id', $shopId)
      ->select('id', 'name')
      ->where('active', '1')
      ->where('type_data', '1')
      ->get();

    foreach ($query as $key => $val) {
      $data[$key] = [
        'solution_id'       => $val->id,
        'solution_name'     => $val->name,
        'sub_solution'      => self::getsubsolutionproject($val->id)
      ];
    }

    return ($data);
  }

  public function getsubsolutionproject($id)
  {
    $query = DB::table('rudy_sub_solution as rss')
      ->join('rudy_solution as rs', 'rss.solution_id', 'rs.id')
      ->where('rss.solution_id', $id)
      ->where('rss.active', '1')
      ->where('rs.type_data', '1')
      ->select('rss.*', 'rs.name AS solution_name')
      ->get();

    return ($query);
  }

  public static function provinces($search, $geoId)
  {
    try {
      $query = Provinces::where('GEO_id', '<', 7);
      if (!empty($search)) {
        $query->where('PROVINCE_NAME', 'LIKE', "%{$search}%");
      }
      if (!empty($geoId)) {
        $query->where('GEO_ID', $geoId);
      }

      $query = $query->select('GEO_ID', 'PROVINCE_ID', 'PROVINCE_NAME')->get();
      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function provincesGroup()
  {

    try {
      $query = DB::table('rudy_provinces AS rp')
        ->join('rudy_geography AS rg', 'rg.GEO_ID', 'rp.GEO_ID')
        ->where('rp.GEO_id', '<', 7)
        ->get();

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function geoGraphy()
  {
    try {
      $query = Geography::whereIn('geo_id', [1, 2, 3, 4, 5, 6])
        ->select('GEO_ID AS geo_id', 'GEO_NAME AS geo_name')
        ->orderBy('geo_name', 'asc')
        ->get();

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function subSolution($shopId, $solutionId)
  {
    // AND rss.active = '1' AND rs.type_data = '1'
    try {
      $query = DB::table('rudy_sub_solution AS rss')
        ->join('rudy_solution AS rs', 'rs.id', 'rss.solution_id')
        ->where('rss.shop_id', $shopId)
        ->where('rss.solution_id', $solutionId)
        ->where('rss.active', '1')
        ->where('rs.type_data', '1')
        ->select('rss.id', 'rss.name', 'rs.id as solution_id', 'rs.name as solution_name')
        ->get();

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public static function csc($shopId, $region)
  {
    try {
      $query = DB::table('rudy_users as ru')
        ->join('rudy_csc_name as rcn', function ($join) use ($region) {
          $join->on('rcn.csc_code', 'ru.user_group')
            ->where('rcn.chain_name', '=',  $region);
        })
        ->select('ru.user_group as name', 'rcn.csc_name');

      if (!empty($shopId)) {
        $query->where('ru.shop_id', '=', $shopId);
      }

      $query = $query->where('ru.department', '=', $region)
        ->whereNotNull('ru.user_group')
        ->whereNotNull('rcn.csc_name')
        ->distinct('ru.user_group')
        ->orderBy('ru.user_group', 'asc')
        ->get()->toArray();

      $data = [];
      if ($query) {
        foreach ($query as $value) {
          if (!empty($value->name)) {
            $data[] = [
              'name'     => $value->name,
              'csc_name' => $value->csc_name
            ];
          }
        }
      }

      return $data;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function getSubsegment($shopId, $search, $segments)
  {
    try {
      $itemsSub = [];
      if ($segments) {
        foreach ($segments as $key => $segment) {
          array_push($itemsSub, $segment['segment_id']);
        }

        $query = DB::table('rudy_eco_subsegment')
          ->where('shop_id', $shopId)
          ->whereIn('segment', $itemsSub)
          ->where('status', 1);

        if (!empty($search)) {
          $query->where('name', 'LIKE', "%{$search}%");
        }
        $query = $query->get();
      }

      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function getStaff()
  {
    try {
      $query = DB::table('rudy_eco_staff')->select('id', 'name')->get();
      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function getWorkExp()
  {
    try {
      $query = DB::table('rudy_eco_work_exp')->select('id', 'name')->get();
      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function getCapital()
  {
    try {
      $query = DB::table('rudy_eco_capital')->select('id', 'name')->get();
      return $query;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function dataCompany($shopId)
  {
    try {
      $dataArray = [];
      $query = DB::table('rudy_vendor as rv')
        ->select('id', 'company', 'name')
        ->where('rv.shop_id', $shopId)
        ->where('rv.vendor_type', 1)

        ->groupBy('rv.company')
        ->get();

      foreach ($query as $val) {
        $dataArray[] = [
          'id'          => $val->id,
          'geo_name'    => $val->company,
          'vendor_name' => $val->name,
        ];
      }


      return $dataArray;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }

  public function entityType()
  {
    $query = DB::table('rudy_entity_type_eco')->select('id', 'name')->get();
    return $query;
  }

  public function customerType($shopId)
  {

    $query = DB::table('rudy_vendor_type')
      ->select('id', 'name')
      ->where('shop_id', $shopId)
      ->where('active', '1')
      ->get();
    return $query;
  }

  public function getWorkType($shopId, $search)
  {
    $query = DB::table('rudy_eco_work_type')
      ->where('shop_id', $shopId)
      ->where('status', 1);
    if (!empty($search)) {
      $query->where('name', 'LIKE', "%{$search}%");
    }
    $query = $query->get();

    return $query;
  }
}
