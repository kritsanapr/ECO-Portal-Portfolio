<?php

namespace App\Repositories;

use App\Models\Provinces;
use App\Models\Projects;
use App\Models\ProjectSolution;
use App\Models\Solution;
use App\Models\VendorType;
use App\Models\Vendor;
use App\Services\FileServices as File;
use Illuminate\Support\Facades\DB;

use Throwable;

ini_set('memory_limit', '-1');

class ProjectsRepository
{

    /**
     * This function is used to get the list of vendors.
     *
     * @param array args an array of arguments
     */
    public static function getProjectList(array $args)
    {

        // print_r($args);
        $shopId                = $args['shopId'];
        $page                  = $args['page'];
        $search                = $args['search'];
        $searchQuestion        = $args['searchQuestion'];
        $grade                 = $args['grade'];
        $area                  = $args['area'];
        $region                = $args['region'];
        $csc                   = $args['csc'];
        $solution              = $args['solution'];
        $adminECO              = $args['admin_eco'];
        $assessor              = $args['assessor'];
        $choose                = $args['choose'];
        $status                = $args['status'];
        $zone                  = $args['zone'];
        $projectId             = $args['projectId'];
        $solutionCWork         = $args['solutionCWork'];
        $subSolutionCWork      = $args['subSolutionCWork'];
        $popupStauts           = $args['popupStauts'];
        $cwork                 = $args['cwork'];
        $subSolution           = $args['subSolution'];

        if (empty($page)) {
            $page = 1;
        }

        $perPage = 10;
        $skip = ($page * $perPage) - $perPage;

        try {
            $query = DB::table('rudy_vendor as rv')
                ->leftJoin('rudy_vendor_evaluation_eco as rvee', 'rv.id', 'rvee.vendor_id')
                ->leftJoin('rudy_vendor_solution_eco as rvs', 'rv.id', 'rvs.vendor_id')
                ->leftJoin('rudy_eco_evaluation_answer as rea', 'rvee.answer', 'rvs.vendor_id')
                ->leftJoin('rudy_vendor_subsolution_eco as rvse', 'rvse.vendor_id', 'rv.id')
                ->leftJoin('rudy_vendor_work_province as rvp', 'rvp.vendor_id', 'rv.id')
                ->leftJoin('rudy_users as ru', 'ru.id', 'rv.admin_eco')
                ->leftJoin('rudy_solution as rs', 'rvs.solution', 'rs.id')
                ->leftJoin('rudy_sub_solution as rss', 'rss.id', 'rvse.subsolution')
                ->leftJoin('rudy_provinces as rp', 'rp.PROVINCE_ID', 'rvp.province_id')
                ->leftJoin('rudy_geography as rg', 'rg.GEO_ID', 'rp.GEO_ID')
                ->select(
                    'rv.id',
                    'rv.vendor_type',
                    'rv.type_id',
                    'rv.name',
                    'rv.company',
                    'rv.region',
                    'rv.csc',
                    DB::raw("
                CASE
                  WHEN rv.csc != 'Metro' THEN (select csc_name from rudy_csc_name rcn where rcn.chain_name = rv.region and rcn.csc_code = rv.csc)
                  ELSE rv.csc
                END AS cscs"),
                    'rv.status',
                    'rv.status_maomao',
                    'rv.status_comment',
                    'rv.added_date',
                    'rv.status_question',
                    'rv.pic',
                    'rv.shop_id',
                    'rv.admin_eco',
                    'rv.starmark',
                    'ru.name as users_name',
                    'rv.username',
                    'rvs.solution',
                    'rvse.subsolution',
                    'rs.name as so_name',
                    'rss.name as sub_name',
                    'rss.id as sub_solution_id',
                    DB::raw("
                CASE
                  WHEN rv.grade_eco = 1 THEN 'A'
                  WHEN rv.grade_eco = 2 THEN 'B'
                  WHEN rv.grade_eco = 3 THEN 'C'
                  WHEN rv.grade_eco = 4 THEN 'D'
                  ELSE null
                END AS grade"),
                    'rvp.province_id',
                    'rp.PROVINCE_NAME',
                    'rg.GEO_NAME',
                    DB::raw("
            CASE
              WHEN rv.status = '1' THEN 1
              WHEN rv.status = '2' THEN 2
              WHEN rv.status = '0' THEN 3
              WHEN rv.status = '3' THEN 4
              ELSE 5
            END AS status_vendor
          "),
                    'rv.status2',
                    DB::raw("CASE
              WHEN rv.status2 = 1 THEN 'Active'
              WHEN rv.status2 = 3 THEN 'InActive'
          ELSE ''
          END AS status2_under"),
                    DB::raw("SUM(rea.score) AS score")
                );

            if (!empty($choose)) {
                $province = Projects::where('id', $projectId)->select('provinces')->first();

                $querySolution = ProjectSolution::where('project_id', $projectId)->select('solution_id')->get()->toArray();
                $querySolutions = $querySolution;
                $i = ',';
                $solutionIn = "";
                $keys = array_keys($querySolutions);
                $newArr = [];
                array_push($newArr, $keys);
                $lastKey = end($newArr);

                foreach ($querySolutions as $key => $val) {
                    $solutionId = $val->solution_id;
                    if ($key == $lastKey) {
                        $solutionIn .= "" . $solutionId . "";
                    } else {
                        $solutionIn .= "" . $solutionId . $i . "";
                    }
                }

                if ($choose == 1) { //พื้นที่รับงานตรงกับโครงการ
                    $query->where('rvp.province_id', '=', $province);
                } else if ($choose == 2) { //ความสามารถตรงกับโครงการ
                    $query->whereIn('rvs.solution', $solutionIn);
                } else {
                    $query->where('rvp.province_id', '=', $province)->whereIn('rvs.solution', $solutionIn);
                }
            }

            if (!empty($subSolution)) {
                $query->where('rss.id', $subSolution);
            }

            if ($popupStauts == 1) { //switch ข้อมูล=1
                $query->whereNotIn('rv.status', ['3', '4']);
            }

            if (!empty($search)) {
                $query->where('rv.name', 'like', '%' . $search . '%')
                    ->orWhere('rv.company', 'like', '%' . $search . '%')
                    ->orWhere('rv.phone', 'like', '%' . $search . '%')
                    ->orWhere('rv.tax_no', 'like', '%' . $search . '%');
            }


            if (!empty($grade)) {
                if ($grade == 'A') {
                    $query->where('rv.grade_eco', '=', 1);
                } else if ($grade == 'B') {
                    $query->where('rv.grade_eco', '=', 2);
                } else if ($grade == 'C') {
                    $query->where('rv.grade_eco', '=', 3);
                } else if ($grade == 'D') {
                    $query->where('rv.grade_eco', '=', 4);
                }
            }

            if (!empty($region)) {
                $query->where('rv.region', '=', $region);
            }

            if (!empty($zone)) {
                $query->where('rg.GEO_NAME', 'like', "%$zone%");
            }

            if (!empty($area)) {
                $query->where('rvp.province_id', '=', $area);
            }

            if (!empty($csc)) {
                $query->where('rv.csc', '=', $csc);
            }

            if (!empty($solution)) {
                $query->where('rvs.solution', '=', $solution);
            }

            if (!empty($solution_cwork)) {
                $query->where('rvs.solution', '=', $solutionCWork);
            }

            if (!empty($sub_solution_cwork)) {
                $query->where('rvse.subsolution', '=', $subSolutionCWork);
            }

            if (!empty($adminECO)) {
                $query->where('rv.admin_eco', '=', $adminECO);
            }

            if ($status != null) {
                $query->where('rv.status', '=', $status);
            }

            if ($cwork == 1) {
                $query->where('rv.status', '!=', 3)->where('rv.status', '!=', 4);
            }

            $query = $query->where('rv.shop_id', $shopId)
                ->where('rvs.active', 1)
                ->orderBy('rv.starmark', 'desc')
                ->orderBy('rss.name', 'asc')
                ->orderBy('rs.name', 'asc')
                ->orderBy('grade', 'asc')
                ->orderBy('rp.PROVINCE_NAME', 'asc')
                ->orderBy('status_vendor', 'asc')
                ->orderBy('rv.name', 'asc')
                ->orderBy('score', 'asc')
                ->groupBy('rv.id');

            $total = $query->get()->count();
            $query = $query->skip($skip)
                ->take($perPage)
                ->get();

            $dataArray = [];
            foreach ($query as $val) {
                $queryAddedate = !empty($val->added_date) ? $val->added_date : date('Y-m-d H:i:s');
                $yearAdded = substr($queryAddedate, 0, 4) + 543;
                $addedDate = date('d/m', strtotime($val->added_date)) . '/' . $yearAdded . date(' H:i', strtotime($val->added_date));

                if (!empty($grade)) {
                    if ($grade == $val->grade) {
                        $dataArray[] = [
                            'shop_id'           => $val->shop_id,
                            'vendor_type'       => $val->vendor_type,
                            'type_id'           => $val->type_id,
                            'type_name'         => self::getType($val->type_id),
                            'name'              => !empty($val->name) ? $val->name : $val->company,
                            'region'            => $val->region,
                            'csc'               => $val->cscs,
                            'id'                => $val->id,
                            'grade'             => $val->grade,
                            'province_id'       => self::getWorkProvince_($val->id, $val->province_id),
                            'solution'          => (($popupStauts == 1) ? self::getSolution_data($val->id, $val->solution) : self::getSolution($val->id)),
                            'sub_solution'      => (($popupStauts == 1) ? self::getSub_solution_cwork($val->id, $val->solution, $val->subsolution) : self::getSub_solution($val->id, $val->solution)),
                            'evaluation'        => self::get_point_evaluation($val->shop_id, $val->id),
                            'added_date'        => $addedDate,
                            'status_question'   => $val->status_question,
                            'profile_img'       => (empty($val->pic) ? 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg' : 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/' . $val->pic),
                            'admin_eco'         => $val->users_name,
                            'username'          => $val->username,
                            'status'            => $val->status,
                            'status_comment'    => $val->status_comment,
                            'starmark'          => $val->starmark,
                            'status2'           => $val->status2,
                            'status2_under'     => $val->status2_under,
                            'price'             => 0,
                            'company'           => $val->company,
                            'sub_solution_id'   => $val->sub_solution_id
                        ];
                    }
                } else {
                    $dataArray[] = [
                        'shop_id'             => $val->shop_id,
                        'vendor_type'         => $val->vendor_type,
                        'type_id'             => $val->type_id,
                        'type_name'           => self::getType($val->type_id),
                        'name'                => !empty($val->name) ? $val->name : $val->company,
                        'region'              => $val->region,
                        'csc'                 => $val->cscs,
                        'id'                  => $val->id,
                        'grade'               => $val->grade,
                        'province_id'         => self::getWorkProvince_($val->id, $val->province_id),
                        'solution'            => (($popupStauts == 1) ? self::getSolution_data($val->id, $val->solution) : self::getSolution($val->id)),
                        'sub_solution'        => (($popupStauts == 1) ? self::getSub_solution_cwork($val->id, $val->solution, $val->subsolution) : self::getSub_solution($val->id, $val->solution)),
                        'evaluation'          => self::get_point_evaluation($val->shop_id, $val->id),
                        'added_date'          => $addedDate,
                        'status_question'     => $val->status_question,
                        'profile_img'         => (empty($val->pic) ? 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg' : 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/' . $val->pic),
                        'admin_eco'           => $val->users_name,
                        'username'            => $val->username,
                        'status'              => $val->status,
                        'status_comment'      => $val->status_comment,
                        'starmark'            => $val->starmark,
                        'status2'             => $val->status2,
                        'status2_under'       => $val->status2_under,
                        'price'               => 0,
                        'company'             => $val->company,
                        'sub_solution_id'     => $val->sub_solution_id
                    ];
                }
            }

            $returnData = [
                'list'                => $dataArray,
                'total'               => $total,
                'page_number'         => $page,
                'total_pages'         => ceil(count($dataArray) / $perPage),
                'total_pages_decimal' => $total / $perPage
            ];

            return $returnData;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    /**
     * It takes an id, finds the name of the vendor type with that id, and returns the name.
     *
     * @param id The id of the vendor type you want to get the name of.
     *
     * @return The name of the vendor type.
     */
    public function getType($id)
    {
        if ($id == 0) {
            return '';
        }
        $query = VendorType::where('id', $id)->first();
        return !empty($query->name) ? $query->name : '';
    }


    /**
     * It's a function that queries the database and returns an array of objects
     * แค่ province_id เรียงเอาตัวที่ค้นหาขึ้นก่อน
     * @param id vendor id
     * @param provinceId The province id that you want to be the first province.
     */
    public function getWorkProvince_($id, $provinceId)
    {
        // Query first province
        $queryFirstProv = DB::table('rudy_vendor_work_province as rvwp')
            ->join('rudy_provinces as rp', 'rp.PROVINCE_ID', 'rvwp.province_id')
            ->select('rvwp.province_id', 'rvwp.type', 'rp.PROVINCE_NAME', 'rp.PROVINCE_NAME_ENG')
            ->where('rvwp.vendor_id', $id);
        if (!empty($province)) {
            $queryFirstProv->where('rp.PROVINCE_ID', '!=', $provinceId);
        }
        $queryFirstProv = $queryFirstProv->get();


        // Query second provinces
        $querySecProv = DB::table('rudy_vendor_work_province as rvwp')
            ->join('rudy_provinces as rp', 'rp.PROVINCE_ID', 'rvwp.province_id')
            ->select('rvwp.province_id', 'rvwp.type', 'rp.PROVINCE_NAME', 'rp.PROVINCE_NAME_ENG')
            ->where('rvwp.vendor_id', $id);
        if (!empty($province)) {
            $querySecProv->where('rp.PROVINCE_ID', '=', $provinceId);
        }
        $querySecProv = $querySecProv->first();

        $dataArray = [];
        if ($queryFirstProv) {
            foreach ($queryFirstProv as $key => $rs) {
                if ($key == 0) {
                    $dataArray[0] = [
                        "province_id"       => $querySecProv->province_id,
                        "type"              => $querySecProv->type,
                        "PROVINCE_NAME"     => $querySecProv->PROVINCE_NAME,
                        "PROVINCE_NAME_ENG" => $querySecProv->PROVINCE_NAME_ENG
                    ];
                } else {
                    $dataArray[$key] = [
                        "province_id"       => $rs->province_id,
                        "type"              => $rs->type,
                        "PROVINCE_NAME"     => $rs->PROVINCE_NAME,
                        "PROVINCE_NAME_ENG" => $rs->PROVINCE_NAME_ENG
                    ];
                }
            }
        } else {
            $dataArray[] = [
                "province_id"           => $querySecProv->province_id,
                "type"                  => $querySecProv->type,
                "PROVINCE_NAME"         => $querySecProv->PROVINCE_NAME,
                "PROVINCE_NAME_ENG"     => $querySecProv->PROVINCE_NAME_ENG
            ];
        }

        return $dataArray;
    }

    /**
     * It takes an id and a solution_id, and returns an array of arrays of strings.
     *
     * @param id vendor id
     * @param solution_id The id of the solution you want to search for.
     */
    public function getSolution_data($id, $solution_id) //แค่ solution ไม่เอา module และเรียงเอาตัวที่ค้นหาขึ้นก่อน
    {
        $queryFirst = DB::table('rudy_vendor_solution_eco as rvse')
            ->join('rudy_solution as rs', 'rs.id', 'rvse.solution')
            ->select('rvse.id', 'rvse.note', 'rs.name', 'rvse.solution')
            ->where('rvse.vendor_id', $id)
            ->where('rvse.active', 1)
            ->where('rs.id', '!=', $solution_id)
            ->get();

        $querySecond = Solution::where('id', $solution_id)->first();

        $dataArray = [];
        if ($queryFirst) {
            foreach ($queryFirst as $key => $rs) {
                if ($key == 0) {
                    $dataArray[0] = [$querySecond->name];
                } else {
                    $dataArray[$key] = [$rs->name];
                }
            }
        } else {
            $dataArray[] = [$querySecond->name];
        }

        return $dataArray;
    }

    /**
     * It returns an array of arrays, where the first array is the first result of the second query, and
     * the rest of the arrays are the results of the first query
     * แค่ module ภายใต้ solution และเรียงเอาตัวที่ค้นหาขึ้นก่อน
     *
     * @param id vendor id
     * @param solution_id The solution id that you want to get the sub-solution from.
     * @param sub_solution_id The id of the sub_solution you want to exclude from the query.
     */
    public function getSub_solution_cwork($id, $solution_id, $sub_solution_id)
    {
        $queryFirst = DB::table('rudy_vendor_subsolution_eco as rvsse')
            ->leftJoin('rudy_vendor_solution_eco rvse', 'rvse.solution', 'rvsse.solution')
            ->leftJoin('rudy_sub_solution as rss', 'rss.id', 'rvsse.subsolution')
            ->select('rvsse.id', 'rvsse.solution', 'rvsse.subsolution', 'rss.name', 'rvse.active', 'rvsse.active')
            ->where('rvsse.vendor_id', $id)
            ->where('rvse.active', 1)
            ->where('rvsse.activ', 1)
            ->where('rvsse.solution', '!=', $solution_id);
        if (!empty($sub_solution_id)) {
            $queryFirst->where('rvsse.subsolution', '!=', $sub_solution_id);
        }
        $queryFirst = $queryFirst->groupBy('rvsse.id')->get();

        $querySecond = DB::table('rudy_vendor_subsolution_eco as rvsse')
            ->leftJoin('rudy_vendor_solution_eco rvse', 'rvse.solution', 'rvsse.solution')
            ->leftJoin('rudy_sub_solution as rss', 'rss.id', 'rvsse.subsolution')
            ->select('rvsse.id', 'rvsse.solution', 'rvsse.subsolution', 'rss.name', 'rvse.active', 'rvsse.active')
            ->where('rvsse.vendor_id', $id)
            ->where('rvse.active', 1)
            ->where('rvsse.activ', 1)
            ->where('rvsse.solution', '=', $solution_id);
        if (!empty($sub_solution_id)) {
            $querySecond->where('rvsse.subsolution', '=', $sub_solution_id);
        }
        $querySecond = $querySecond->groupBy('rvsse.id')->first();

        $dataArray = [];
        if ($queryFirst) {
            foreach ($queryFirst as $key => $ar) {
                if ($key == 0) {
                    $dataArray[0] = [$querySecond->name];
                } else {
                    $dataArray[$key] = [$ar->name];
                }
            }
        } else {
            $dataArray[] = [$querySecond->name];
        }
        return $dataArray;
    }

    /**
     * It's a function that gets the sum of the score of the answers of the evaluation of the vendor
     *
     * @param shop_id The id of the shop
     * @param vendor_id The vendor id
     */
    public function get_point_evaluation($shop_id, $vendor_id)
    {

        $query = DB::table('rudy_vendor_evaluation_eco as rvee')
            ->join('rudy_eco_evaluation_answer as reen', function ($join) use ($shop_id, $vendor_id) {
                $join->on('reen.id', 'rvee.answer')
                    ->where('rvee.shop_id', '=',  $shop_id)
                    ->where('rvee.vendor_id', '=',  $vendor_id);
            })
            ->selectRaw("sum(reen.score) as point,rvee.updated_at")
            ->first();


        return $query->point;
    }

    /**
     * It's a function that returns an array of arrays
     *
     * @param id The id of the vendor
     *
     * @return An array of arrays.
     */
    public function getSolution($id)
    {

        $queryFirst = DB::table('rudy_vendor_solution_eco as rvse')
            ->join('rudy_solution as rs', 'rs.id', 'rvse.solution')
            ->where('rvse.vendor_id', $id)
            ->where('rvse.active', 1)
            ->get();

        $data_final = [];
        $data_sub   = [];
        $myarray    = [];
        if ($queryFirst) {
            foreach ($queryFirst as $rs) {
                $data_final[] = [
                    'id'    => $rs->solution,
                    'name'  => $rs->name
                ];
            }
        }

        $querySecond = DB::table('rudy_vendor_solution_eco as rvse')
            ->join('rudy_solution as rs', 'rs.id', 'rvse.solution')
            ->where('rvse.vendor_id', $id)
            ->where('rvse.active', 1)
            ->get();

        if ($querySecond) {
            foreach ($querySecond as $ar) {
                $data_sub[$ar->solution][] = [
                    'solution' => $ar->solution,
                    'name'     => $ar->name,
                ];
            }
        }


        if ($data_final) {
            foreach ($data_final as $key => $r) {
                if (count($data_sub[$r['id']]) > 0) {
                    for ($x = 0; $x < count($data_sub[$r['id']]); $x++) {
                        if ($data_sub[$r['id']][$x]['solution'] == $r['id']) {
                            $myarray[] = $r['name'] . '- ' . $data_sub[$r['id']][$x]['name'];
                        }
                    }
                } else {
                    $myarray[] = $r['name'];
                }
            }
        }

        return $myarray;
    }

    /**
     * It's a function that returns an array of arrays, where the first array is the first result of a
     * query, and the rest of the arrays are the results of a different query
     * แค่ module ภายใต้ solution และเรียงเอาตัวที่ค้นหาขึ้นก่อน ,$sub_solution_id
     *
     * @param id vendor id
     * @param solution_id The solution id that you want to exclude from the query.
     *
     * @return ค่าที่ส่งกลับมาจะเป็นค่าที่อยู่ในตาราง rudy_
     */
    public function getSub_solution($id, $solution_id)
    {
        $queryFirst = DB::table('rudy_vendor_subsolution_eco as rvse')
            ->join('rudy_sub_solution as rss', 'rss.id', 'rvse.subsolution')
            ->select('rvse.id', 'rvse.solution', 'rvse.subsolution', 'rss.name')
            ->where('rvse.vendor_id', $id)
            ->where('rvse.active', 1)
            ->where('rvse.solution', '!=', $solution_id)
            ->get();

        $querySecond = DB::table('rudy_vendor_subsolution_eco as rvse')
            ->join('rudy_sub_solution as rss', 'rss.id', 'rvse.subsolution')
            ->select('rss.name')
            ->first();

        $dataArray = [];
        if ($queryFirst) {
            foreach ($queryFirst as $key => $ar) {
                if ($key == 0) {
                    $dataArray[0] = [$querySecond->name];
                } else {
                    $dataArray[$key] = [$ar->name];
                }
            }
        } else {
            $dataArray[] = [$querySecond->name];
        }
        return $dataArray;
    }



    ########################################### START ECO Detail ##################################
    public function getVendorView($shopId, $vendorId)
    {

        $query = DB::table('rudy_vendor as rv')
            ->join('rudy_vendor_details_eco as rvd', 'rv.id', 'rvd.vendor_id')
            ->leftJoin('rudy_eco_capital as rec', 'rec.id', 'rvd.capital')
            ->leftJoin('rudy_eco_staff as res', 'res.id', 'rvd.staff')
            ->leftJoin('rudy_eco_work_exp as rewe', 'rewe.id', 'rvd.experience')
            ->leftJoin('rudy_users as ru', 'ru.id', 'rv.admin_eco')
            ->leftJoin('rudy_vendor_type as rvt', 'rvt.id', 'rv.type_id')
            ->leftJoin('rudy_entity_type_eco as rete', 'rete.id', 'rv.entity_type')
            ->select(
                'rv.id',
                'rv.vendor_code',
                'rv.vendor_type',
                'rv.gender',
                'rv.prefix_name',
                'rv.name',
                'rv.nickname',
                'rv.company',
                'rv.region',
                'rv.csc',
                'rv.company_id',
                'rv.entity_type as entity_type_id',
                'rete.name as entity_type_name',
                DB::raw("
          CASE
            WHEN rv.grade_eco = 1 THEN 'A'
            WHEN rv.grade_eco = 2 THEN 'B'
            WHEN rv.grade_eco = 3 THEN 'C'
            WHEN rv.grade_eco = 4 THEN 'D'
            ELSE null
          END AS grade_eco
        "),
                DB::raw("
          CASE
             WHEN rv.csc != 'Metro' then (select csc_name from rudy_csc_name rcn where rcn.chain_name = rv.region and rcn.csc_code = rv.csc)
             ELSE rv.csc
          END AS cscs
        "),
                'rv.tax_no',
                'rv.line_id',
                'rv.phone',
                'rv.phone_extension',
                'rv.email',
                'rv.address',
                'rv.note',
                'rv.grade',
                'rv.admin_eco',
                'rv.status',
                'rv.status2',
                'rv.status_comment',
                'rv.type_id',
                DB::raw("
         CASE
             WHEN rv.pic is null THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
             WHEN rv.pic = '' THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
             ELSE CONCAT('https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/',rv.pic)
         END AS pic
        "),
                'rvd.boq',
                'rvd.design',
                'rvd.join_scg',
                'rvd.typeget_work',
                'rvd.staff',
                'rvd.capital',
                'rvd.experience',
                'rvd.machine',
                'rvd.size',
                'rvd.register_date',
                'rvd.viewpoint',
                'rvd.qualify',
                'rvd.month_qualify',
                'rec.name as capital_name',
                DB::raw("
        CASE
             WHEN rvd.staff > 0 THEN res.name
             ELSE rvd.staff_name
        END as staff_name"),
                'rvd.staff_type',
                'rewe.name as experience_name',
                'ru.name as admin_eco_name',
                'ru.phone as admin_phone',
                'ru.email as admin_email',
                'rvt.name as type_cusname'
            )->where('rv.shop_id', $shopId)
            ->where('rv.id', $vendorId)
            ->first();

        $queryBefore = DB::table('rudy_vendor_history_eco as re')
            ->join('rudy_users as rs', 'rs.id', 're.user_id')
            ->select('re.vendor_id', 're.user_id', 'rs.name', 're.status', 're.comment', 're.updated_at')
            ->where('re.vendor_id', $vendorId)
            ->where('re.status', '!=', 4)
            ->orderBy('re.id', 'desc');

        $dataBefore = $queryBefore->get();
        $count_ = $queryBefore->count();

        $queryContactors = DB::table('rudy_eco_contactors')
            ->select('id', 'shop_id', 'vendor_id', 'name', 'nickname', 'position', 'contact_type_id', 'contact_detail', 'extension_number', 'profile_image as profile_img', 'created_at')
            ->where('shop_id', $shopId)
            ->where('vendor_id', $vendorId)
            ->get();
        // print_r($query);
        // echo "-------" . "\n";
        // echo "Count : " . $count_ . "\n";
        // print_r($dataBefore);

        $cArray = 0;
        if ($count_ > 1) {
            $cArray = 1;
            $beforeStatus = $dataBefore[$cArray]->status;
        } else {
            $beforeStatus = 0;
        }


        // echo $beforeStatus;
        if ($query) {
            $s_status = "";
            if ($query->status2 == 1) {
                $s_status = "Active";
            } else if ($query->status2 == 3) {
                $s_status = "InActive";
            }
            $data_array = [
                'id'                    => $query->id,
                'vendor_code'           => $query->vendor_code,
                'vendor_type'           => $query->vendor_type,
                'type_id'               => $query->type_id,
                'vendor_type'           => $query->vendor_type,
                'type_cusname'          => $query->type_cusname,
                'gender'                => $query->gender,
                'prefix_name'           => $query->prefix_name,
                'name'                  => $query->name,
                'nickname'              => $query->nickname,
                'company_id'            => $query->company_id,
                'company'               => $query->company,
                'region'                => $query->region,
                'csc'                   => $query->csc,
                'csc_name'              => $query->cscs,
                'tax_no'                => $query->tax_no,
                'line_id'               => $query->line_id,
                'phone'                 => $query->phone,
                'phone_extension'       => $query->phone_extension,
                'email'                 => $query->email,
                'address'               => $query->address,
                'note'                  => $query->note,
                'grade'                 => $query->grade_eco,
                'admin_eco'             => $query->admin_eco,
                'admin_eco_name'        => $query->admin_eco_name,
                'admin_phone'           => $query->admin_phone,
                'admin_email'           => $query->admin_email,
                'profile_img'           => $query->pic,
                'boq'                   => $query->boq,
                'design'                => $query->design,
                'join_scg'              => $query->join_scg,
                'typeget_work'          => $query->typeget_work,
                'staff'                 => $query->staff,
                'staff_name'            => $query->staff_name,
                'staff_type'            => $query->staff_type,
                'capital'               => $query->capital,
                'capital_name'          => $query->capital_name,
                'experience'            => $query->experience,
                'experience_name'       => $query->experience_name,
                'machine'               => $query->machine,
                'size'                  => $query->size,
                'register_date'         => $query->register_date,
                'viewpoint'             => $query->viewpoint,
                'qualify'               => $query->qualify,
                'month_qualify'         => $query->month_qualify,
                'status'                => $query->status,
                'before_status'         => $beforeStatus,
                'status_comment'        => $query->status_comment,
                'status2'               => $s_status,
                'file_banned'           => self::getFileBanned($query->id),
                'segment'               => self::getSegment($query->id),
                'subsegment'            => self::getSubsegment($query->id),
                'solution'              => self::getSolution($query->id),
                'solutionAndsub'        => self::getSolution_sub($query->id),
                'solution_all'          => self::getSolution_all($query->id),
                'worktype'              => self::getWorktype($query->id),
                'province_id'           => self::getWorkProvince($query->id),
                'file_upload'           => self::getFileupload($query->id),
                'entity_type_id'        => $query->entity_type_id,
                'entity_type_name'      => $query->entity_type_name,
                'contactors'            => $queryContactors
            ];
        }

        return $data_array;
    }

    /**
     * It returns an array of arrays, each of which contains an id, file, and link_file key
     *
     * @param id The id of the vendor
     */
    public function getFileBanned($id)
    {
        try {
            $dataFile = [];
            $img_file_cwork = 'https://merudy.s3-ap-southeast-1.amazonaws.com/projects/';
            $queryVendor = Vendor::where('id', $id)
                ->select('status')
                ->first();

            if ($queryVendor->status == 4) {
                $queryScop = DB::table('rudy_vendor_banned_eco as rvbe')
                    ->join('rudy_file_banned_eco as rbe', 'rbe.banned_id', 'rvbe.id')
                    ->select('rbe.banned_id', 'rbe.file_name')
                    ->where('rvbe.vendor_id', $id)
                    ->get();

                foreach ($queryScop as $files) {
                    $dataFile[] = [
                        'file'      => $files->file_name,
                        'link_file' => $img_file_cwork . $files->file_name
                    ];
                }
            }

            return $dataFile;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * It returns a list of segments for a given vendor
     *
     * @param id The id of the vendor
     */
    public function getSegment($id)
    {
        try {
            $query = DB::table('rudy_vendor_segment_eco as rvse')
                ->join('rudy_eco_segment as res', 'res.id', 'rvse.segment')
                ->select('rvse.id', 'res.id as segment_id', 'res.name', 'res.fullname', 'res.icon')

                ->where('rvse.vendor_id', $id)
                ->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * It gets the subsegment of a vendor.
     *
     * @param id vendor id
     */
    public function getSubsegment($id)
    {
        try {
            $query = DB::table('rudy_vendor_subsegment_eco as rvse')
                ->join('rudy_eco_subsegment as res', 'res.id', 'rvse.sub_segment')
                ->select('rvse.id', 'res.id as subseg_id', 'res.name')
                ->where('rvse.vendor_id', $id)
                ->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function getSolution_sub($id)
    {
        try {
            $query = DB::table('rudy_vendor_solution_eco as rvse')
                ->join('rudy_solution as rs', 'rs.id', 'rvse.solution')
                ->select('rvse.id', 'rvse.note', 'rs.name', 'rvse.solution')
                ->where('rvse.vendor_id', $id)
                ->where('rvse.active', 1)
                ->get();

            $dataFinal = [];
            $dataSub   = [];
            $dataArray    = [];
            if ($query) {
                foreach ($query as $rs) {
                    $dataFinal[] = [
                        'id'    => $rs->solution,
                        'name'  => $rs->name
                    ];
                }
            }

            $querySecond = DB::table('rudy_vendor_subsolution_eco as rvse')
                ->join('rudy_sub_solution as rss', 'rss.id', 'rvse.subsolution')
                ->select('rvse.id', 'rvse.solution', 'rvse.subsolution', 'rss.name')
                ->where('rvse.vendor_id', $id)
                ->where('rvse.active', 1)
                ->get();
            if ($querySecond) {
                foreach ($querySecond as $ar) {
                    $dataSub[$ar->solution][] = [
                        'solution_id' => $ar->solution,
                        'subso_id'    => $ar->subsolution,
                        'name'        => $ar->name,
                    ];
                }
            }

            $dataArray = [
                'solution'    => $dataFinal,
                'subsolution' => $dataSub
            ];

            return $dataArray;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function getSolution_all($id)
    {
        try {
            $query = DB::table('rudy_vendor_solution_eco as rvse')
                ->join('rudy_solution as rs', 'rs.id', 'rvse.solution')
                ->select('rvse.id', 'rvse.note', 'rs.name', 'rvse.solution')
                ->where('rvse.vendor_id', $id)
                ->where('rvse.active', 1)
                ->get();
            // return $query;
            if ($query) {
                $dataFinal = [];
                $dataSub   = [];
                foreach ($query as $rs) {
                    $querySecond = DB::table('rudy_vendor_subsolution_eco as rvse')
                        ->join('rudy_sub_solution as rss', 'rss.id', 'rvse.subsolution')
                        ->select('rvse.id', 'rvse.solution', 'rvse.subsolution', 'rss.name')
                        ->where('rvse.vendor_id', $id)
                        ->where('rvse.active', 1)
                        ->where('solution', $rs->solution)
                        ->get();

                    $checkSubSolution = empty($querySecond) ? '' : $querySecond;
                    if (!empty($checkSubSolution)) {
                        foreach ($querySecond as $ar) {
                            $dataSub[] = [
                                'solution_id'      => $ar->solution,
                                'subso_id'         => $ar->subsolution,
                                'solution_name'    => $rs->name,
                                'subsolution_name' => $ar->name,
                            ];
                        }
                    }
                    $dataFinal[] = [
                        'solution_id'      => $rs->solution,
                        'subso_id'         => "",
                        'solution_name'    => $rs->name,
                        'subsolution_name' => "",
                    ];
                }
                $merge = array_merge($dataSub, $dataFinal);
            }

            return $merge;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function getWorktype($id)
    {

        try {
            $query = DB::table('rudy_vendor_worktype_eco as rvwe')
                ->join('rudy_eco_work_type as rewt', 'rewt.id', 'rvwe.worktype')
                ->select('rvwe.id', 'rvwe.note', 'rewt.id as work_type_id', 'rewt.name')
                ->where('rvwe.vendor_id', $id)
                ->where('rewt.status', 1)
                ->orderBy('rewt.name')
                ->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function getWorkProvince($id)
    {
        try {
            $query = DB::table('rudy_vendor_work_province as rvwp')
                ->join('rudy_provinces as rp', 'rp.PROVINCE_ID', 'rvwp.province_id')
                ->select('rvwp.province_id', 'rvwp.type', 'rp.PROVINCE_NAME', 'rp.PROVINCE_NAME_ENG')
                ->where('rvwp.vendor_id', $id)
                ->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function getFileupload($id)
    {
        try {
            $queryRaw =  DB::raw("
              CASE
                WHEN name is null THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                WHEN name = '' THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                ELSE CONCAT('https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/files/',name)
              END AS link
          ");

            $query = DB::table('rudy_vendor_file_eco')
                ->select(
                    'id',
                    'name',
                    'filename',
                    $queryRaw
                )
                ->where('vendor_id', $id)
                ->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function listPortfolio($shopId, $vendorId, $statusProjectId, $statusReject)
    {
        $query = DB::table('rudy_vendor_portfolio_eco as rvpe')
            ->select(
                'rvpe.*',
                DB::raw("
          CASE
            WHEN rvpe.solution_id = 0 THEN (select rs.name from rudy_solution rs join rudy_project_solution_eco rpse ON rpse.solution = rs.id LIMIT 1)
            ELSE (select name from rudy_solution WHERE id = rvpe.solution_id)
          END AS solution_name
        "),
                DB::raw("
          CASE
            WHEN rvpe.project_id > 0 THEN CONCAT('SELECT * FROM rudy_project_galleries WHERE project_id', '=', (SELECT id FROM rudy_projects WHERE id = rvpe.project_id))
            ELSE CONCAT('SELECT * FROM rudy_vendor_portfolio_img_eco WHERE portfolio_id', '=', rvpe.id)
          END AS q_img"),
                DB::raw("
          CASE
              WHEN rvpe.project_id > 0 THEN (SELECT status FROM rudy_projects WHERE id = rvpe.project_id)
              ELSE 0
          END AS status_project"),
                DB::raw("
          CASE
            WHEN rvpe.project_id > 0 THEN (select project_code from rudy_projects where id = rvpe.project_id)
            ELSE ''
          END AS project_code"),
                DB::raw("
          CASE
              WHEN rvpe.project_id > 0 THEN 'cwork'
              ELSE ''
          END AS cwork_s")
            )
            ->where('rvpe.shop_id', $shopId)
            ->where('rvpe.vendor_id', $vendorId)
            ->where('rvpe.shop_id', $shopId);

        if ($statusReject == 1) {
            $query->where('rvpe.status', 1);
        } else {
            $query->where('rvpe.status', 0);
        }
        $query = $query->get();

        $dataCwork = [];
        $dataPort = [];
        $dataArray = [];
        foreach ($query as $val) {
            $status = $val->cwork_s;
            $statusProject = "";
            $projectCode = $val->project_code;
            $statusId = 0;
            $dataDocArray = [];
            $qStauts = '';
            $fileProjectId = $val->project_id;
            $queryCwork = DB::table('rudy_projects as rp')
                ->where('rp.shop_id', $shopId)
                ->where('rp.id', $fileProjectId);
            if ($statusProjectId == 1) //กำลังดำเนินการ
            {
                $queryCwork->whereNotIn("rp.status", [2, 3]);
            } else if ($statusProjectId == 2) { //ปิดโครงการ
                $queryCwork->whereIn("rp.status", [2]);
            } else if ($statusProjectId == 3)  //ยกเลิก cancle
            {
                $queryCwork->whereIn("rp.status", [3]);
            }
            $queryCwork = $queryCwork->first();


            if ($queryCwork) {
                $statusId = $queryCwork->status;
                if ($queryCwork->status == 1 || $queryCwork->status > 3 || $queryCwork->status != 2) {
                    $statusProject = "กำลังดำเนินการ";
                } else if ($queryCwork->status == 2) {
                    $statusProject = "ปิดโครงการ";
                } else if ($queryCwork->status == 3) {
                    $statusProject = "ยกเลิก";
                } else {
                    $statusProject = "";
                }
            }


            $filePortfolioId = $val->id;
            $queryDoc = DB::table('rudy_vendor_portfolio_img_eco')->where('portfolio_id', $filePortfolioId)->get();
            if ($queryDoc) {
                foreach ($queryDoc as $val_doc) {
                    $dataDocArray[] = [
                        'img_id'    => $val_doc->id,
                        'file_name' => $val_doc->filename,
                        'link'      => 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/files_solution/' . $val_doc->filename,
                    ];
                }
            }

            if ($status) {
                $file_project_id = $val->project_id;
                // $sql_doc = "SELECT id,name FROM rudy_project_galleries WHERE project_id = $file_project_id";
                // $data_doc = $db->rows($sql_doc);
                $data_doc = DB::table('rudy_project_galleries')->select('id', 'name')->where('project_id', $file_project_id)->get();
                if ($data_doc) {
                    $a = [];
                    foreach ($data_doc as $val_doc) {
                        $a[] = [
                            'img_id'    => 0, //cwork ห้ามลบหรือแก้ไข
                            'file_name' => $val_doc->name,
                            'link'      => 'https://merudy.s3-ap-southeast-1.amazonaws.com/projects/' . $val_doc->name,
                        ];
                    }
                    $dataDocArray = array_merge($a, $dataDocArray);
                }
            }

            if ($statusReject == 1) {
                $statusProject = "Reject";
                $dataPort[] = [
                    'portfolio_id'   => $val->id,
                    'project_name'   => $val->project_name,
                    'project_code'   => $statusReject,
                    'solution'       => self::solution_portfolio($val->id),
                    'details'        => $val->details,
                    'img_portfolio'  => $dataDocArray,
                    'status'         => $status,
                    'remarks'        => $val->remark,
                    'status_project' => $statusProject
                ];
            } else {
                if ($statusProjectId == $val->status_project || $val->status_project > 3 && $statusProjectId != 2 && $statusProjectId != 3) {
                    $dataPort[] = [
                        'portfolio_id'   => $val->id,
                        'project_name'   => $val->project_name,
                        'project_code'   => $projectCode,
                        'solution'       => self::solution_portfolio($val->id),
                        'details'        => $val->details,
                        'img_portfolio'  => $dataDocArray,
                        'status'         => $status,
                        'remarks'        => $val->remark,
                        'status_project' => $statusProject

                    ];
                } else if ($statusProjectId == 0) {
                    $dataPort[] = [
                        'portfolio_id'    => $val->id,
                        'project_name'    => $val->project_name,
                        'project_code'    => $projectCode,
                        'solution'        => self::solution_portfolio($val->id),
                        'details'         => $val->details,
                        'img_portfolio'   => $dataDocArray,
                        'status'          => $status,
                        'remarks'         => $val->remark,
                        'status_project'  => $statusProject

                    ];
                }
            }
        }

        $dataArray = [
            'count' => count($dataPort),
            // 'cwork' => $dataCwork,
            'list'  => $dataPort,
            // 'test'  => $this->getSolutions($vendorId, $shopId)
        ];

        return $dataArray;
    }


    public function solution_portfolio($portfolio_id)
    {
        $query = DB::table('rudy_project_solution_eco as rpse')
            ->join('rudy_solution as rs', 'rs.id', 'rpse.solution')
            ->where('rpse.portfolio_id', $portfolio_id)
            ->groupBy('rpse.solution')
            ->get();

        $array_so = [];
        foreach ($query as $val) {
            $array_sub = [];
            $so_name = $val->name;
            $data_r = DB::table('rudy_project_solution_eco as rpse')
                ->where('rpse.portfolio_id', $portfolio_id)
                ->where('rpse.solution', $val->solution)
                ->get();

            foreach ($data_r as $val_r) {
                $data_sub = DB::table('rudy_sub_solution')->select('id', 'name')->where('id', $val_r->subsolution)->where('solution_id', $val_r->solution)->get();

                foreach ($data_sub as $val_sub) {
                    $array_sub[] = [
                        'subsolution_id'   => $val_sub->id,
                        'subsolution_name' => $val_sub->name
                    ];
                }
            }

            $array_so[] = [
                'solution_id'   => $val->solution,
                'solution_name' => $so_name,
                'sub_name'      => $array_sub,

            ];
        }
        return $array_so;
    }

    // To DO
    public function listQuestionnaire($args)
    {

        try {
            $shop_id = $args['shop_id'];
            $type    = $args['type'];
            $chain   = $args['chain'];
            $csc     = $args['csc'];
            $status  = $args['status'];
            $search  = $args['search'];
            $page    = $args['page'];

            if (empty($page)) {
                $page = 1;
            }
            $perpage = 10;

            $skip = ($page * $perpage) - $perpage;

            $queryRaw = DB::raw('(select name from rudy_users where id = rqe.created_by) as created_by');

            $query = DB::table('rudy_questionnaire_eco as rqe')
                ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', 'rqe.id')
                ->select('rqe.*', $queryRaw)
                ->where('rqe.active', '=', 0);


            if ($type) {
                $query = $query->where('rqe.questionnaire_type', '=', $type);
            }
            if ($chain) {
                $query = $query->where('rqce.region', 'like',  '%' . $chain . '%');
            }
            if ($csc) {
                $query = $query->where('rqce.csc', 'like',  '%' . $csc . '%');
            }
            if ($status != '' || $status != null) {
                $query = $query->where('rqe.status', $status);
            }
            if ($search) {
                $query = $query->where('rqe.questionnaire_name', 'like',  '%' . $search . '%');
            }

            $query = $query->groupBy('rqe.id')
                ->orderBy('rqe.created_at', 'desc');

            $queryCount = $query->get()->count();

            $query = $query->skip($skip)
                ->take($perpage)
                ->get();


            $data_array = [];
            foreach ($query as $val) {
                $query_csc = DB::table('rudy_questionnaire_csc_eco')
                    ->where('questionnaire_id', $val->id)
                    ->get();

                $r_csc = [];
                foreach ($query_csc as $val_csc) {
                    $r_csc[] = [
                        'csc' => $val_csc->region //.' '.$val_csc['csc']
                    ];
                }

                $year_c = substr($val->created_at, 0, 4) + 543;
                $year_u = substr($val->updated_at, 0, 4) + 543;

                $created_at = date('d/m', strtotime($val->created_at)) . '/' . $year_c . date(' H:i', strtotime($val->created_at)); //date("d-m-Y H:i", strtotime($val['created_at']));
                $updated_at = date('d/m', strtotime($val->updated_at)) . '/' . $year_u . date(' H:i', strtotime($val->updated_at)); //date("d-m-Y H:i", strtotime($val['updated_at']));
                $data_array[] = [
                    'id'                 => $val->id,
                    'questionnaire_name' => $val->questionnaire_name,
                    'questionnaire_type' => self::questionnaire_types($val->questionnaire_type),
                    'csc'                => $r_csc,
                    'created_at'         => $created_at,
                    'created_by'         => $val->created_by,
                    'status'             => $val->status,
                    'updated_at'         => $updated_at,
                ];
            }

            $return_data = [
                'list'                => $data_array,
                'total'               => $queryCount,
                'total_pages'         => ceil($queryCount / $perpage),
                'total_pages_decimal' => $queryCount / $perpage
            ];

            return $return_data;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function questionnaire_types($q_id)
    {
        $q_name = "";
        if ($q_id == 1) {
            $q_name = "Sizing";
        } elseif ($q_id == 2) {
            $q_name = "ประเมินผลงาน";
        }
        return $q_name;
    }


    public function getMSolutioList($shopId)
    {
        try {
            $query = DB::table('rudy_solution')
                ->select('id', 'name')
                ->where('shop_id', $shopId)
                ->where('active', 1)->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Todo/
    public function list_solution_data($params)
    {
        $shop_id = '228';
        if ($_SERVER['HTTP_HOST'] == 'ecoportal-apiv2.merudy.com') {
            $shop_id = $params['shop_id'];
        }
        $vendor_id = $params['vendor_id'];
        $search = $params['search'];
        if ($search) {
            $w_search = " and rp.project_name like '%" . $search . "%'";
        }
        $page      = $params['page'];
        if (empty($page)) {
            $page = 1;
        }
        $perpage = 10;

        $skip = ($page * $perpage) - $perpage;

        $db = new DB; //(select admin_eco from rudy_vendor where id = $vendor_id) as admin_eco,
        /*   */
        $sql = "SELECT rvpe.id as portfolio_id,rvpe.vendor_id,rvpe.shop_id,rvpe.project_name as pro_name,rvpe.project_id,rvpe.cwork,rvpe.project_eco_id,rvpe.status,
       rp.project_name,rp.id,rp.status
       FROM rudy_vendor_portfolio_eco rvpe
       LEFT JOIN rudy_projects rp ON rp.id = rvpe.project_id
       WHERE rvpe.shop_id = '" . $shop_id . "' AND rvpe.status = 0 AND rvpe.vendor_id = '" . $vendor_id . "' $w_search ";
        $sql_count = $sql . $skip . "," . $perpage;

        $page_limit = "limit " . $skip . "," . $perpage;
        $sql_ql = $sql . $page_limit;
        //print_r($sql_ql);
        $q_data = $db->rows($sql_ql);
        $sql_count = $sql_q;
        $data_count = $db->count($sql);

        $data = $db->rows($sql_ql); //query ข้อมูลผลงานพร้อมโครงการ

        $data_array = [];
        foreach ($data as $val) {
            $portfolio_id = $val['portfolio_id'];
            $sqls = "select id from rudy_vendor_project_solution_eco where portfolio_id = $portfolio_id";

            $sql_so = "SELECT rvpse.id,rvpse.solution_id,rvpse.draf,rvpse.status_eva,rvpse.questionnaire_id,rvpse.score,rvpse.grade as grade_status,ru.name as assessor,rvpse.created_at,rvpse.solution_eva_id,rvpse.eva_number,rvpse.portfolio_id,
                (SELECT NAME FROM rudy_solution WHERE id = rvpse.solution_id) AS so_name
                FROM rudy_vendor_project_solution_eco rvpse
                JOIN rudy_vendor_portfolio_eco rvpe ON rvpe.id = rvpse.portfolio_id
                JOIN rudy_users ru ON ru.id = rvpse.created_by
                WHERE rvpse.shop_id = $shop_id AND rvpse.status = 1 AND rvpse.portfolio_id = $portfolio_id ORDER BY rvpse.created_at DESC LIMIT 1";
            $datas = $db->row($sql_so);
            $so_name = "";
            $array_so = [];
            $data_file = [];
            $count_file = 0;
            $ontime = "0";
            $draf = 0;
            $status = "ยังไม่ประเมิน";
            $grade = null;
            $questionnaire_name = null;
            $solu_date = "-";
            $data_so = [];
            if ($datas) { //มีการประเมิน
                $status = "";
                $so_name = $datas['so_name'];
                $so_id = $datas['solution_id'];
                $eva_number = $datas['eva_number'];
                $p = $datas['portfolio_id'];
                /* $sql_sub = "SELECT rvpse.sub_solution_id,rvpse.id,
                    (SELECT NAME FROM rudy_sub_solution WHERE id = rvpse.sub_solution_id) AS sub_name
                    FROM rudy_vendor_project_solution_eco rvpse
                    JOIN rudy_vendor_portfolio_eco rvpe ON rvpe.id = rvpse.portfolio_id
                    WHERE rvpse.shop_id = $shop_id AND rvpse.status = 1 AND rvpse.portfolio_id = $p
                    AND rvpse.solution_id = $so_id and rvpse.eva_number = $eva_number
                    group BY rvpse.sub_solution_id ORDER BY rvpse.created_at DESC ";
                 $datasub = $db->rows($sql_sub);

                 $array_sub = [];
                foreach($datasub as $val_sub)
                {

                    $array_sub[] = [
                        //'id' => $val_sub['id'],
                        'subsolution_id' => $val_sub['sub_solution_id'],
                        'subsolution_name' => $val_sub['sub_name']
                    ];

                }
                $array_so = [
                    'solution_id' => $so_id,
                    'solution_name' => $so_name,
                    'sub_name' =>  $array_sub,

                ]; */


                $so_name = "";
                $sql_solution = "SELECT rvpse.solution_id,rvpse.sub_solution_id,(SELECT NAME FROM rudy_solution WHERE id = rvpse.solution_id) AS solution_name FROM rudy_vendor_project_solution_eco rvpse
            WHERE rvpse.vendor_id = '" . $val['vendor_id'] . "' AND rvpse.portfolio_id = '" . $p . "' AND rvpse.eva_number = '" . $eva_number . "'
            GROUP BY rvpse.solution_id ORDER BY rvpse.created_at desc";
                $data_solution = $db->rows($sql_solution); //ได้ชื่อsolution
                foreach ($data_solution as $val_solution) { //วนหาsub so
                    //print_r($val_solution['solution_id']);
                    $sql1 = "SELECT rvpse.*,(SELECT NAME FROM rudy_sub_solution WHERE id = rvpse.sub_solution_id) AS subsolution_name
                ,case
                    when rvpse.questionnaire_id > 0 then rvpse.created_at
                    when rvpse.questionnaire_id = 0 then (SELECT evaluate_date FROM rudy_solution_evaluate_eco WHERE id = rvpse.solution_eva_id)
                    ELSE  ''
                    END AS createdat
                FROM rudy_vendor_project_solution_eco rvpse
                WHERE rvpse.created_at = '" . $datas['created_at'] . "' AND rvpse.solution_id = '" . $val_solution['solution_id'] . "'
                and rvpse.sub_solution_id = '" . $val_solution['sub_solution_id'] . "'   AND rvpse.eva_number = '" . $eva_number . "'
                 AND rvpse.vendor_id = '" . $vendor_id . "' AND rvpse.portfolio_id = '" . $p . "' AND rvpse.status=1
                 ORDER BY rvpse.created_at desc"; //ได้ sub solution ภายใต้solution
                    $data1 = $db->rows($sql1);
                    $data_sub = [];
                    foreach ($data1 as $val1) {

                        $data_sub[] = [
                            //'subsolution_name' => $val1['subsolution_name'],
                            //'subsolution_id' => $val1['sub_solution_id'],
                            'subsolution_name' => $val1['subsolution_name']
                        ];
                    }
                    $data_so[] = [
                        //'solution_name' => $val_solution['solution_name'],
                        //'module' => $data_sub
                        'solution_id' => $val_solution['solution_id'],
                        'solution_name' => $val_solution['solution_name'],
                        'sub_name' =>  $data_sub,
                    ];
                }

                if ($datas['questionnaire_id'] > 0) { //ประเมินมีฟอร์ม
                    $ontime_q = "SELECT reqe.questionnaire_id,reqe.ontime,rvps.portfolio_id,rvps.solution_id,rvps.sub_solution_id
                        FROM rudy_evaluate_questionnaire_eco reqe
                        JOIN rudy_vendor_project_solution_eco rvps ON rvps.questionnaire_id = reqe.questionnaire_id

                        WHERE reqe.shop_id = $shop_id and reqe.eva_number=$eva_number AND reqe.vendor_id = $vendor_id and reqe.questionnaire_id = '" . $datas['questionnaire_id'] . "' AND reqe.choice_id != 0 and reqe.portfolio_id = $p
                        GROUP BY reqe.questionnaire_id ORDER BY reqe.created_at DESC LIMIT 1";
                    $data_ontime = $db->row($ontime_q);
                    $ontime = $data_ontime['ontime'];

                    //print_r($ontime_q);
                    $sql_q = "select grade_type,questionnaire_name from rudy_questionnaire_eco where id = '" . $datas['questionnaire_id'] . "'";
                    $s_q = $db->row($sql_q);
                    /* if($s_q['grade_type'] ==2){
                        $grade = $this->average_grade($vendor_id,$portfolio_id,$datas['questionnaire_id'],$eva_number);
                    }else{
                        $grade = $datas['grade_status'];
                    } */
                    //print_r($sql_q);
                    $questionnaire_name = $s_q['questionnaire_name'];
                } else { //ไม่มีแบบฟอร์ม
                    $sql_q = "select grade_type from rudy_questionnaire_eco where id = '" . $datas['questionnaire_id'] . "'";
                    $s_q = $db->row($sql_q);
                    /*if($s_q['grade_type'] ==2){
                        $grade = $this->average_grade($vendor_id,$portfolio_id,$datas['questionnaire_id'],$eva_number);
                    }else{
                        $grade = $datas['grade_status'];
                    } */

                    $sql_file_solu = " SELECT * from rudy_solution_document_eco WHERE solution_evaluate_id = '" . $datas['solution_eva_id'] . "'";
                    //print_r($datas['solution_eva_id']);
                    $result_file = $db->rows($sql_file_solu);
                    $count_file = count($result_file);
                    foreach ($result_file as $files) {
                        $data_file[] = [
                            'doc_id' => $files['id'],
                            'file' => $files['file_name'],
                            'link_file' => $this->img_file . $files['file_name']
                        ];
                    }
                }
                //หา average ของเกรด (โครงการหรือผลงานแต่ละอัน)
                //$grade = $this->average_grade($vendor_id,$datas['questionnaire_id'],$portfolio_id);
                /* if($s_q['grade_type'] ==2){
                    $grade = $this->average_grade($vendor_id,$portfolio_id,$datas['questionnaire_id'],$eva_number);
                }else{
                    $grade = $datas['grade_status'];
                } */
                //$grade = $this->average_grade($vendor_id,$portfolio_id,$datas['questionnaire_id'],$eva_number);
                //print_r($datas['questionnaire_id']);print_r("<pre>");
                $year_c = substr($datas['created_at'], 0, 4) + 543;
                $solu_date = date('d/m', strtotime($datas['created_at'])) . '/' . $year_c;
                $grade = $datas['grade_status'];
            }
            $status_project = "";
            if ($val['status']) {
                $status_id = $val['status'];
                if ($val['status'] == 1 || $val['status'] > 3 || $val['status'] != 2) {
                    $status_project = "กำลังดำเนินการ";
                } else if ($val['status'] == 2) {
                    $status_project = "ปิดโครงการ";
                } else if ($val['status'] == 3) {
                    $status_project = "ยกเลิก";
                } else {
                    $status_project = "";
                }

                //$project_code = $_data['project_code'];
            }
            //print_r($datas['draf']);
            if ($datas['draf'] == 2) { //draf ไว้ไม่ต้องแสดงเกรด
                //print_r($datas['draf']);
                $grade = "";
            }
            $data_array[] = [
                'portfolio_id' => $val['portfolio_id'],
                'eva_num' => $eva_number,
                'questionnaire_id' => $datas['questionnaire_id'],
                'questionnaire_name' => $questionnaire_name,
                'project' => $val['pro_name'],
                'status_project' => $status_project,
                'solution' => $data_so, //$array_so,
                'ontime' => $ontime,
                "status" => $status,
                "grade" => $grade,
                "score" => $datas['score'],
                "assessor" => $datas['assessor'],
                "position" => "",
                "date_solution" => $solu_date,
                "count_file" => $count_file,
                "file_solution" => $data_file,
                "draf" => $datas['draf'],
                //"so_new" =>$data_so
            ];
        }
        //$Reference = $this->arrayPaginator($data_array, $params);
        $return_data = [
            'count' => $data_count, //count($data_array),
            'admin_eco' => $admin_eco,
            'list'      => $data_array,
            'total'               => $data_count,
            'total_pages'         => ceil($data_count / $perpage),
            'total_pages_decimal' => $data_count / $this->perpage

        ];
        return $return_data;
    }

    // Todo
    // public function listSolutionData($args)
    // {
    //   $shopId = $args['shop_id'] ? $args['shop_id'] : 228;
    //   $vendorId = $args['vendor_id'];
    //   $search = $args['search'];
    //   $page = $args['page'];

    //   try {
    //     if (empty($page)) {
    //       $page = 1;
    //     }
    //     $perPage = 10;
    //     $skip = ($page * $perPage) - $perPage;

    //     //     $sql = "SELECT rvpe.id as portfolio_id,rvpe.vendor_id,rvpe.shop_id,rvpe.project_name as pro_name,rvpe.project_id,rvpe.cwork,rvpe.project_eco_id,rvpe.status,
    //     //  rp.project_name,rp.id,rp.status
    //     //  FROM rudy_vendor_portfolio_eco rvpe
    //     //  LEFT JOIN rudy_projects rp ON rp.id = rvpe.project_id
    //     //  WHERE rvpe.shop_id = '" . $shop_id . "' AND rvpe.status = 0 AND rvpe.vendor_id = '" . $vendor_id . "' $w_search ";
    //     //     $sql_count = $sql . $skip . "," . $perpage;

    //     $query = DB::table('rudy_vendor_portfolio_eco as rvpe')
    //       ->leftJoin('rudy_projects as rp', 'rp.id', 'rvpe.project_id')
    //       ->select(
    //         'rvpe.id as portfolio_id',
    //         'rvpe.vendor_id',
    //         'rvpe.shop_id',
    //         'rvpe.project_name as pro_name',
    //         'rvpe.project_id',
    //         'rvpe.cwork',
    //         'rvpe.project_eco_id',
    //         'rvpe.status',
    //         'rp.project_name',
    //         'rp.id',
    //         'rp.status'
    //       )
    //       ->where('rvpe.shop_id', $shopId)
    //       ->where('rvpe.status', 0)
    //       ->where('rvpe.vendor_id', $vendorId);

    //     if (!empty($search)) {
    //       $query = $query->where('rp.project_name', 'like', "%{$search}%");
    //     }

    //     $query = $query->skip($skip)->take($perPage);

    //     $query = $query->get();
    //     $queryCount = $query->count();

    //     // return $query;

    //     // exit();

    //     $data_array = [];
    //     foreach ($query as $val) {
    //       $portfolio_id = $val->portfolio_id;
    //       // $querySoluiton = DB::table('rudy_vendor_project_solution_eco')->select('id')->get();
    //       //       $sql_so = "SELECT rvpse.id,rvpse.solution_id,rvpse.draf,rvpse.status_eva,rvpse.questionnaire_id,rvpse.score,rvpse.grade as grade_status,ru.name as assessor,rvpse.created_at,rvpse.solution_eva_id,rvpse.eva_number,rvpse.portfolio_id,
    //       // (SELECT NAME FROM rudy_solution WHERE id = rvpse.solution_id) AS so_name
    //       // FROM rudy_vendor_project_solution_eco rvpse
    //       // JOIN rudy_vendor_portfolio_eco rvpe ON rvpe.id = rvpse.portfolio_id
    //       // JOIN rudy_users ru ON ru.id = rvpse.created_by
    //       // WHERE rvpse.shop_id = $shop_id AND rvpse.status = 1 AND rvpse.portfolio_id = $portfolio_id ORDER BY rvpse.created_at DESC LIMIT 1";
    //       //       $datas = $db->row($sql_so);

    //       $queryProSolutRaw = DB::raw("(SELECT NAME FROM rudy_solution WHERE id = rvpse.solution_id) AS so_name");
    //       $queryProSolut = DB::table('rudy_vendor_project_solution_eco as rvpse')
    //         ->join('rudy_vendor_portfolio_eco as rvpe', 'rvpe.id', 'rvpse.portfolio_id')
    //         ->join('rudy_users as ru', 'ru.id', 'rvpse.created_by')
    //         ->select(
    //           'rvpse.id',
    //           'rvpse.solution_id',
    //           'rvpse.draf',
    //           'rvpse.status_eva',
    //           'rvpse.questionnaire_id',
    //           'rvpse.score',
    //           'rvpse.grade as grade_status',
    //           'ru.name as assessor',
    //           'rvpse.created_at',
    //           'rvpse.solution_eva_id',
    //           'rvpse.eva_number',
    //           'rvpse.portfolio_id',
    //           $queryProSolutRaw
    //         )
    //         ->where('rvpse.shop_id', $shopId)
    //         ->where('rvpse.status', 1)
    //         ->where('rvpse.portfolio_id', $portfolio_id)
    //         ->orderBy('rvpse.created_at', 'DESC')
    //         ->first();

    //       $so_name = "";
    //       $array_so = [];
    //       $data_file = [];
    //       $count_file = 0;
    //       $ontime = "0";
    //       $draf = 0;
    //       $status = "ยังไม่ประเมิน";
    //       $grade = null;
    //       $questionnaire_name = null;
    //       $solu_date = "-";
    //       $data_so = [];

    //       // print_r($queryProSolut);
    //       // exit;

    //       if (!empty($queryProSolut)) { //มีการประเมิน
    //         $status     = "";
    //         $so_name    = $queryProSolut->so_name;
    //         $so_id      = $queryProSolut->solution_id;
    //         $eva_number = $queryProSolut->eva_number;
    //         $p          = $queryProSolut->portfolio_id;
    //         $so_name    = "";

    //         // exit();
    //         // $sql_solution = "SELECT rvpse.solution_id,rvpse.sub_solution_id,(SELECT NAME FROM rudy_solution WHERE id = rvpse.solution_id) AS solution_name FROM rudy_vendor_project_solution_eco rvpse
    //         // WHERE rvpse.vendor_id = '" . $val['vendor_id'] . "' AND rvpse.portfolio_id = '" . $p . "' AND rvpse.eva_number = '" . $eva_number . "'
    //         // GROUP BY rvpse.solution_id ORDER BY rvpse.created_at desc";
    //         // $data_solution = $db->rows($sql_solution); //ได้ชื่อsolution

    //         $querySolution = DB::table('rudy_vendor_project_solution_eco as rvpse')
    //           ->select('rvpse.solution_id', 'rvpse.sub_solution_id', DB::raw("(SELECT NAME FROM rudy_solution WHERE id = rvpse.solution_id) AS solution_name"))
    //           ->where('rvpse.vendor_id', $val->vendor_id)
    //           ->where('rvpse.portfolio_id', $p)
    //           ->where('rvpse.eva_number', $eva_number)
    //           ->groupBy('rvpse.solution_id')
    //           ->orderBy('rvpse.created_at', 'desc')
    //           ->get();

    //         // print_r($querySolution);
    //         // exit();

    //         foreach ($querySolution as $val_solution) { //วนหาsub so
    //           //print_r($val_solution['solution_id']);
    //           // $sql1 = "SELECT rvpse.*,(SELECT NAME FROM rudy_sub_solution WHERE id = rvpse.sub_solution_id) AS subsolution_name
    //           //   ,case
    //           //       when rvpse.questionnaire_id > 0 then rvpse.created_at
    //           //       when rvpse.questionnaire_id = 0 then (SELECT evaluate_date FROM rudy_solution_evaluate_eco WHERE id = rvpse.solution_eva_id)
    //           //       ELSE  ''
    //           //       END AS createdat
    //           //   FROM rudy_vendor_project_solution_eco rvpse
    //           //   WHERE rvpse.created_at = '" . $datas['created_at'] . "' AND rvpse.solution_id = '" . $val_solution['solution_id'] . "'
    //           //   and rvpse.sub_solution_id = '" . $val_solution['sub_solution_id'] . "'   AND rvpse.eva_number = '" . $eva_number . "'
    //           //    AND rvpse.vendor_id = '" . $vendor_id . "' AND rvpse.portfolio_id = '" . $p . "' AND rvpse.status=1
    //           //    ORDER BY rvpse.created_at desc"; //ได้ sub solution ภายใต้solution
    //           // $data1 = $db->rows($sql1);

    //           $querySubSolutRaw1 = DB::raw("(SELECT NAME FROM rudy_sub_solution WHERE id = rvpse.sub_solution_id) AS subsolution_name");
    //           $querySubSolutRaw2 = DB::raw("
    //               CASE
    //                 WHEN rvpse.questionnaire_id > 0 THEN rvpse.created_at
    //                 WHEN rvpse.questionnaire_id = 0 THEN (SELECT evaluate_date FROM rudy_solution_evaluate_eco WHERE id = rvpse.solution_eva_id)
    //                 ELSE  ''
    //               END AS createdat
    //             ");

    //           $querySubSolut = DB::table('rudy_vendor_project_solution_eco as rvpse')
    //             ->select(
    //               'rvpse.*',
    //               $querySubSolutRaw1,
    //               $querySubSolutRaw2
    //             )
    //             ->where('rvpse.created_at', $queryProSolut->created_at)
    //             ->where('rvpse.solution_id', $val_solution->solution_id)
    //             ->where('rvpse.vendor_id', $vendorId)
    //             ->where('rvpse.portfolio_id', $p)
    //             ->where('rvpse.status', 1)
    //             ->orderBy('rvpse.created_at', 'desc')
    //             ->get();

    //           // return $querySubSolut;
    //           // exit();

    //           $data_sub = [];
    //           foreach ($querySubSolut as $val1) {

    //             $data_sub[] = [
    //               //'subsolution_name' => $val1['subsolution_name'],
    //               //'subsolution_id' => $val1['sub_solution_id'],
    //               'subsolution_name' => $val1->subsolution_name
    //             ];
    //           }
    //           $data_so[] = [
    //             //'solution_name' => $val_solution['solution_name'],
    //             //'module' => $data_sub
    //             'solution_id'   => $val_solution->solution_id,
    //             'solution_name' => $val_solution->solution_name,
    //             'sub_name'      =>  $data_sub
    //           ];
    //           // }

    //           if ($queryProSolut->questionnaire_id > 0) { //ประเมินมีฟอร์ม
    //             // $ontime_q = "SELECT reqe.questionnaire_id,reqe.ontime,rvps.portfolio_id,rvps.solution_id,rvps.sub_solution_id
    //             //           FROM rudy_evaluate_questionnaire_eco reqe
    //             //           JOIN rudy_vendor_project_solution_eco rvps ON rvps.questionnaire_id = reqe.questionnaire_id
    //             //           WHERE reqe.shop_id = $shop_id and reqe.eva_number=$eva_number AND reqe.vendor_id = $vendor_id and reqe.questionnaire_id = '" . $datas['questionnaire_id'] . "' AND reqe.choice_id != 0 and reqe.portfolio_id = $p
    //             //           GROUP BY reqe.questionnaire_id ORDER BY reqe.created_at DESC LIMIT 1";
    //             // $data_ontime = $db->row($ontime_q);
    //             // $ontime = $data_ontime['ontime'];

    //             $onTimeQ = DB::table('rudy_evaluate_questionnaire_eco as reqe')
    //               ->join('rudy_vendor_project_solution_eco as rvps', 'rvps.questionnaire_id', 'reqe.questionnaire_id')
    //               ->select('reqe.questionnaire_id', 'reqe.ontime', 'rvps.portfolio_id', 'rvps.solution_id', 'rvps.sub_solution_id')
    //               ->where('reqe.shop_id', $shopId)
    //               ->where('reqe.eva_number', $eva_number)
    //               ->where('reqe.vendor_id', $vendorId)
    //               ->where('reqe.questionnaire_id', $queryProSolut->questionnaire_id)
    //               ->where('reqe.choice_id', '!=', 0)
    //               ->where('reqe.portfolio_id', $p)
    //               ->groupBy()
    //               ->first();
    //             $ontime = $onTimeQ->ontime;

    //             // echo "Ontime : " . $ontime . "\n";

    //             // $sql_q = "select grade_type,questionnaire_name from rudy_questionnaire_eco where id = '" . $datas['questionnaire_id'] . "'";
    //             // $s_q = $db->row($sql_q);
    //             // $questionnaire_name = $s_q['questionnaire_name'];
    //             $queryQ = DB::table('rudy_questionnaire_eco')
    //               ->select('grade_type', 'questionnaire_name')
    //               ->where('id', $queryProSolut->questionnaire_id)
    //               ->first();
    //             $s_q = $queryQ->questionnaire_name;
    //           } else { //ไม่มีแบบฟอร์ม
    //             // $sql_q = "select grade_type from rudy_questionnaire_eco where id = '" . $datas['questionnaire_id'] . "'";
    //             // $s_q = $db->row($sql_q);
    //             $queryQ = DB::table('rudy_questionnaire_eco')
    //               ->select('grade_type')
    //               ->where('id', $queryProSolut->questionnaire_id)
    //               ->first();
    //             $s_q = $queryQ->questionnaire_name;


    //             // $sql_file_solu = " SELECT * from rudy_solution_document_eco WHERE solution_evaluate_id = '" . $datas['solution_eva_id'] . "'";
    //             // $result_file = $db->rows($sql_file_solu);
    //             // $count_file = count($result_file);
    //             $queryFileSolu = DB::table('rudy_solution_document_eco')
    //               ->where('solution_evaluate_id', $queryProSolut->solution_eva_id);

    //             $resultFile = $queryFileSolu->get();
    //             $count_file = $queryFileSolu->count();

    //             $img_file = 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/files_solution/';

    //             foreach ($resultFile as $files) {
    //               $data_file[] = [
    //                 'doc_id'    => $files->id,
    //                 'file'      => $files->file_name,
    //                 'link_file' => $img_file . $files->file_name
    //               ];
    //             }
    //           }
    //           // print_r($data_file);

    //           //หา average ของเกรด (โครงการหรือผลงานแต่ละอัน)
    //           $grade = self::average_grade($vendorId, $portfolio_id, $queryProSolut->questionnaire_id, $eva_number);
    //           $year_c = substr($queryProSolut->created_at, 0, 4) + 543;
    //           $solu_date = date('d/m', strtotime($queryProSolut->created_at)) . '/' . $year_c;
    //         }

    //         $status_project = "";
    //         if ($val->status) {
    //           $status_id = $val->status;
    //           if ($val->status == 1 || $val->status > 3 || $val->status != 2) {
    //             $status_project = "กำลังดำเนินการ";
    //           } else if ($val->status == 2) {
    //             $status_project = "ปิดโครงการ";
    //           } else if ($val->status == 3) {
    //             $status_project = "ยกเลิก";
    //           } else {
    //             $status_project = "";
    //           }
    //         }
    //         $draf = $queryProSolut->draf ? $queryProSolut->draf : 0;
    //         if ($draf == 2) { //draf ไว้ไม่ต้องแสดงเกรด
    //           $grade = "";
    //         }
    //       }
    //       $data_array[] = [
    //         'portfolio_id'       => $val->portfolio_id,
    //         'eva_num'            => $eva_number,
    //         'questionnaire_id'   => $queryProSolut->questionnaire_id,
    //         'questionnaire_name' => $questionnaire_name,
    //         'project'            => $val->pro_name,
    //         'status_project'     => $status_project,
    //         'solution'           => $data_so, //$array_so,
    //         'ontime'             => $ontime,
    //         "status"             => $status,
    //         "grade"              => $grade,
    //         "score"              => $queryProSolut->score,
    //         "assessor"           => $queryProSolut->assessor,
    //         "position"           => "",
    //         "date_solution"      => $solu_date,
    //         "count_file"         => $count_file,
    //         "file_solution"      => $data_file,
    //         "draf"               => $queryProSolut->draf
    //       ];
    //     }



    //     $return_data = [
    //       'count'               => $queryCount,
    //       'list'                => $data_array,
    //       'total'               => $queryCount,
    //       'total_pages'         => ceil($queryCount / $perPage),
    //       'total_pages_decimal' => $queryCount / $perPage

    //     ];
    //     // print_r($data_array);
    //     return $return_data;
    //   } catch (Throwable $e) {
    //     return $e->getMessage();
    //   }
    // }


    public function average_grade($vendor_id, $portfolio_id, $questionnaire_id, $eva_number)
    {
        $grade = "";
        $score = 0;

        $re_count = DB::table('rudy_vendor_project_solution_eco')
            ->where('vendor_id', $vendor_id)
            ->where('portfolio_id', $portfolio_id)
            ->whereNotIn('grade', ['ไม่ผ่านเกณฑ์', 'ผ่านเกณฑ์'])
            ->where('draf', '1')
            ->groupBy('created_at')
            ->get();

        $count_n = $re_count->count();
        // print_r($re_count);
        // echo $count_n . "\n";
        // echo "=======";
        // exit;

        $sum_aver = 0;
        foreach ($re_count as $val) {
            //print_r($val['grade']);
            if ($val->grade == 'A') {
                $sum_aver += 4;
            } else if ($val->grade == 'B') {
                $sum_aver += 3;
            } else if ($val->grade == 'C') {
                $sum_aver += 2;
            } else {
                $sum_aver += 1;
            }
        }

        $score = ($sum_aver / $count_n);
        // echo "sum :" . $sum_aver . "\n";
        // echo "count :" . $count_n . "\n";
        // echo "----------" . "\n";

        if (round($score) > 0 && round($score) <= 1) {
            $grade = "D"; //1
        } else if (round($score) > 1 && round($score) <= 2) {
            $grade = "C"; //2
        } else if (round($score) > 2 && round($score) <= 3) {
            $grade = "B"; //3
        } else if (round($score) >= 4) { //4
            $grade = "A";
        }
        // print_r($grade);
        return ($grade);
    }


    // Improve
    /* รายการ ประเมินทัศนคติ  */
    public function listEvaluateAttitude($args)
    {
        $shop_id = $args['shop_id'];
        $vendor_id = $args['vendor_id'];
        $search    = $args['search'];

        $myarray = [];
        $data_array = [];
        // $sql_vendor = "SELECT cv.id,cv.csc,cv.region,
        //     case
        //         when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
        //                 JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = cv.id) >= 70 then 'ผ่านเกณฑ์'
        //         when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
        //                 JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = cv.id) < 70 then 'ไม่ผ่านเกณฑ์'
        //         ELSE 'รอประเมิน'
        //         END AS evaluation
        //     FROM rudy_vendor cv
        //     WHERE cv.id =  $vendor_id ";
        // $data_vendor = $db->row($sql_vendor);

        $queryVendorRaw = DB::raw("
        CASE
            WHEN (SELECT SUM(reen.score) FROM rudy_vendor_evaluation_eco AS rvee
                    JOIN rudy_eco_evaluation_answer AS reen ON reen.id = rvee.answer WHERE vendor_id = cv.id) >= 70 THEN 'ผ่านเกณฑ์'
            WHEN (SELECT sum(reen.score) FROM rudy_vendor_evaluation_eco as rvee
                    JOIN rudy_eco_evaluation_answer AS reen ON reen.id = rvee.answer WHERE vendor_id = cv.id) < 70 THEN 'ไม่ผ่านเกณฑ์'
            ELSE 'รอประเมิน'
        END AS evaluation");

        $queryVendor = DB::table('rudy_vendor as cv')
            ->select('cv.id', 'cv.csc', 'cv.region', $queryVendorRaw)
            ->where('cv.id', $vendor_id)
            ->first();

        // print_r($queryVendor);
        // exit;

        if ($queryVendor) {
            // $sql_eva = "SELECT *,(SELECT cus.name FROM rudy_users cus WHERE cus.id = rvee.user_id) AS name_created
            //        FROM rudy_vendor_evaluation_eco rvee WHERE rvee.vendor_id = $vendor_id LIMIT 1";
            // $data_eva = $db->row($sql_eva);
            $queryEvaRaw = DB::raw("(SELECT cus.name FROM rudy_users cus WHERE cus.id = rvee.user_id) AS name_created");
            $queryEva = DB::table('rudy_vendor_evaluation_eco AS rvee')
                ->select('*', $queryEvaRaw)
                ->where('rvee.vendor_id', $vendor_id)->first();

            // print_r($queryEva);
            // exit;

            $id = 0; //ไม่มีการประเมินครั้งแรก แบบเก่า
            if ($queryEva) { //มีค่าประเมินเก่า
                // if ($search) {
                //   $w_search_old = " and questionnaire_name like '%" . $search . "%'";
                // }
                // $sql_name = "SELECT questionnaire_name FROM rudy_questionnaire_eco WHERE eva_answer_id = 1 AND active = 0 $w_search_old ";
                // $data_name = $db->row($sql_name);
                $queryName = DB::table('rudy_questionnaire_eco')
                    ->select('questionnaire_name')
                    ->where('eva_answer_id', 1)
                    ->where('active', 0);

                if ($search) {
                    $queryName->where('questionnaire_name', 'LIKE', "%${search}%");
                }

                $queryName = $queryName->first();

                //print_r($data_eva);
                if ($queryName) {
                    $year_o = substr($queryEva->updated_at, 0, 4) + 543;

                    // print_r($queryEva);
                    // echo $queryEva->created_at;

                    if ($queryVendor->evaluation == 'รอประเมิน') {
                        $create_at = "";
                        $update_at = "";
                        $user_created = "";
                    } else {
                        $create_at = date('d/m', strtotime($queryEva->created_at)) . '/' . $year_o . date(' H:i', strtotime($queryEva->created_at));
                        $update_at = date('d/m', strtotime($queryEva->updated_at)) . '/' . $year_o . date(' H:i', strtotime($queryEva->updated_at));
                        $user_created = $queryEva->name_created;
                    }

                    $data_old[] = [
                        //'system' => 0,
                        'vendor_id'           => $vendor_id,
                        'questionnaire_name'  => 'แบบประเมินทัศนคติ Ver.1',
                        'q_type_name'         => 'ทัศนคติ',
                        'csc'                 => $queryVendor->region,
                        'status'              => $queryVendor->evaluation,
                        'create_at'           => $create_at,
                        'user_created'        => $user_created,
                        'updated_at'          => $update_at,
                    ];

                    // print_r($data_old);
                    // exit();
                }
            }

            // if ($search) {
            //   $w_evamore = " and rqe.questionnaire_name like '%" . $search . "%'";
            // }
            // $sql_evamore = "SELECT *,reqe.created_at as create_eva,rqe.id AS ques_id,(SELECT cus.name FROM rudy_users cus WHERE cus.id = rqe.created_by) AS name_created,reqe.created_at
            //        FROM rudy_questionnaire_eco rqe
            //        INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
            //        JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqce.questionnaire_id
            //        WHERE rqce.region = '" . $data_vendor['region'] . "' AND rqe.active = 0 and rqe.questionnaire_type = 1 AND reqe.vendor_id = $vendor_id AND reqe.choice_id != 0 $w_evamore GROUP BY reqe.created_at ";

            // $data_evamore = $db->rows($sql_evamore);
            // $c_new = count($data_evamore);
            $queryEvaMoreRaw = DB::raw("(SELECT cus.name FROM rudy_users as cus WHERE cus.id = rqe.created_by) AS name_created");
            $queryEvaMore = DB::table("rudy_questionnaire_eco as rqe")
                ->select('*', 'reqe.created_at as create_eva', 'rqe.id AS ques_id', $queryEvaMoreRaw, 'reqe.created_at')
                ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', 'rqe.id')
                ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', 'rqce.questionnaire_id')
                ->where('rqce.region', $queryVendor->region)
                ->where('rqe.active', 0)
                ->where('rqe.questionnaire_type', 1)
                ->where('reqe.vendor_id', $vendor_id)
                ->where('reqe.choice_id', '!=', 0);
            if ($search) {
                $queryEvaMore->where('qe.questionnaire_name', 'LIKE', "%{$search}%");
            }
            $queryEvaMore = $queryEvaMore
                ->groupBy('reqe.created_at')
                ->get();

            // print_r($queryEvaMore);
            // return ($queryEvaMore);
            // exit;

            $data_port = [];
            foreach ($queryEvaMore as  $val) {
                $status_q = "รอประเมิน";
                if ($val->grade_type == 1) { //1=คำนวณผ่านเกณฑ์
                    $year_e = substr($val->updated_at, 0, 4) + 543;
                    // $sql_score = "SELECT sum(rqe.score) AS evaluation
                    //                   FROM rudy_evaluate_questionnaire_eco rqe
                    //                   JOIN rudy_questionnaire_eco rqee ON rqee.id = rqe.questionnaire_id
                    //                   WHERE rqe.vendor_id = $vendor_id AND rqee.grade_type = 1 AND rqe.choice_id != 0 AND rqe.created_at =  '" . $val['create_eva'] . "' AND rqe.questionnaire_id = " . $val['questionnaire_id'] . "";
                    // $data_score = $db->row($sql_score);
                    $queryScore = DB::table('rudy_evaluate_questionnaire_eco as rqe')
                        ->select(DB::raw("sum(rqe.score) AS evaluation"))
                        ->join('rudy_questionnaire_eco as rqee', 'rqee.id', 'rqe.questionnaire_id')
                        ->where('rqe.vendor_id', $vendor_id)
                        ->where('rqee.grade_type', 1)
                        ->where('rqe.choice_id', '!=', 0)
                        ->where('rqe.created_at', $val->create_eva)
                        ->where('rqe.questionnaire_id', $val->questionnaire_id)
                        ->first();

                    $status_q = "รอประเมิน";
                    if ($queryScore->evaluation >= $val->qualify_from) {
                        $status_q = "ผ่านเกณฑ์";
                    } else if ($queryScore->evaluation <= $val->fail_to) {
                        $status_q = "ไม่ผ่านเกณฑ์";
                    }
                } else if ($val->grade_type == 2) { //2=คำนวณตัดเกรด
                    $year_e = substr($val->updated_at, 0, 4) + 543;
                    // $sql_score = "SELECT sum(rqe.score) AS evaluation
                    //                   FROM rudy_evaluate_questionnaire_eco rqe
                    //                   JOIN rudy_questionnaire_eco rqee ON rqee.id = rqe.questionnaire_id
                    //                   WHERE rqe.vendor_id = $vendor_id AND rqee.grade_type = 2 AND rqe.choice_id != 0 AND rqe.created_at =  '" . $val['create_eva'] . "' AND rqe.questionnaire_id = " . $val['questionnaire_id'] . "";
                    // $data_score = $db->row($sql_score);
                    $queryScore = DB::table('rudy_evaluate_questionnaire_eco as rqe')
                        ->select(DB::raw("sum(rqe.score) AS evaluation"))
                        ->join('rudy_questionnaire_eco as rqee', 'rqee.id', 'rqe.questionnaire_id')
                        ->where('rqe.vendor_id', $vendor_id)
                        ->where('rqee.grade_type', 2)
                        ->where('rqe.choice_id', '!=', 0)
                        ->where('rqe.created_at', $val->create_eva)
                        ->where('rqe.questionnaire_id', $val->questionnaire_id)
                        ->first();

                    $grade = "รอประเมิน";
                    if ($queryScore) {
                        $score = $queryScore->evaluation;
                        if ($score <= $val->grade_d_to) {
                            $grade = "D";
                        } else if ($score >= $val->grade_c_from && $score <= $val->grade_c_to) {
                            $grade = "C";
                        } else if ($score >= $val->grade_b_from && $score <= $val->grade_b_to) {
                            $grade = "B";
                        } else if ($score >= $val->grade_a_from) {
                            $grade = "A";
                        }
                        $status_q = $grade;
                    }
                }

                $data_port[] = [
                    //'system' => 2,
                    'questionnaire_id'    => $val->questionnaire_id,
                    'questionnaire_name'  => $val->questionnaire_name,
                    'q_type_name'         => $this->questionnaire_types($val->questionnaire_type),
                    'csc'                 => $val->region, //.' '.$val->csc,
                    'status'              => $status_q,
                    'create_at'           => date('d/m', strtotime($val->created_at)) . '/' . $year_e . date(' H:i', strtotime($val->created_at)),
                    'user_created'        => $val->name_created,
                    'updated_at'          => date('d/m', strtotime($val->updated_at)) . '/' . $year_e . date(' H:i', strtotime($val->updated_at)),
                    'created'             => $val->created_at,
                ];
            }
        }

        $data_array = [
            'data_old' => $data_old,
            'data_new' => $data_port,
        ];
        return $data_array;
    }

    // Improve
    /* รายการ ประเมิน Professional  */
    public function listEvaluateProfessional($args)
    {
        $shop_id = $args['shop_id'];
        $vendor_id = $args['vendor_id'];
        $search    = $args['search'];

        $myarray = [];
        $data_array = [];
        // $sql_vendor = "SELECT cv.id,cv.csc,cv.region
        //     FROM rudy_vendor cv
        //     WHERE cv.id =  $vendor_id ";
        // $data_vendor = $db->row($sql_vendor);
        $queryVendor = DB::table('rudy_vendor as cv')
            ->select('cv.id', 'cv.csc', 'cv.region')
            ->where('cv.id', $vendor_id)
            ->first();

        // return $queryVendor;

        // if ($search) {
        //   $w_evamore = " and rqe.questionnaire_name like '%" . $search . "%'";
        // }

        // $sql_evamore = "SELECT *,(SELECT cus.name FROM rudy_users cus WHERE cus.id = reqe.created_by) AS name_created
        //     ,reqe.created_at AS createat
        //         FROM rudy_questionnaire_eco rqe
        //         INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
        //         JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqce.questionnaire_id
        //         WHERE rqce.region = '" . $data_vendor['region'] . "' AND reqe.choice_id != 0
        //            AND rqe.active = 0 and rqe.questionnaire_type = 2 and reqe.vendor_id = $vendor_id $w_evamore GROUP BY reqe.created_at ";
        // $data_evamore = $db->rows($sql_evamore);

        $queryEvaMoreRaw = DB::raw("(SELECT cus.name FROM rudy_users cus WHERE cus.id = reqe.created_by) AS name_created");
        $queryEvaMore = DB::table('rudy_questionnaire_eco as rqe')
            ->select('*', $queryEvaMoreRaw, 'reqe.created_at AS createat')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', 'rqce.questionnaire_id')
            ->where('rqce.region', $queryVendor->region)
            ->where('reqe.choice_id', '!=', 0)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 2)
            ->where('reqe.vendor_id', $vendor_id);
        if ($search) {
            $queryEvaMore->where('rqe.questionnaire_name', 'LIKE', "%${$search}%");
        }
        $queryEvaMore = $queryEvaMore->groupBy('reqe.created_at');

        $dataEvaMore = $queryEvaMore->get();
        $countEvaMore = $queryEvaMore->count();
        // return $dataEvaMore;

        $data_port = [];
        $c_new = $countEvaMore;
        foreach ($dataEvaMore as  $val) {
            $year_e = substr($val->updated_at, 0, 4) + 543;
            $status_pro = "รอประเมิน";
            if ($val->grade_type == 1) {
                $questionnaire_id = $val->questionnaire_id;
                //เริ่มหาการคำนวณแบบผ่านเกณฑ์
                // $sql_score_pro = "SELECT questionnaire_id,
                //             sum(reqe.score) AS evaluation
                //             FROM rudy_evaluate_questionnaire_eco reqe
                //             JOIN rudy_questionnaire_eco rqe ON rqe.id = reqe.questionnaire_id AND rqe.questionnaire_type = 2
                //                 AND rqe.grade_type = 1
                //             WHERE reqe.vendor_id = $vendor_id and reqe.questionnaire_id = $questionnaire_id AND reqe.choice_id != 0
                //              and reqe.created_at = '" . $val['createat'] . "' GROUP BY reqe.questionnaire_id ORDER BY reqe.created_at DESC ";
                // //print_r($sql_score_pro);
                // $data_score_pro = $db->row($sql_score_pro);

                $queryScoreProRaw = DB::raw("SUM(reqe.score) AS evaluation");
                $queryScorePro = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', $queryScoreProRaw)
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', 'reqe.questionnaire_id')
                            ->on('rqe.questionnaire_type', 2)
                            ->on('rqe.grade_type', 1);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('reqe.questionnaire_id', $questionnaire_id)
                    ->where('reqe.choice_id', '!=', 0)
                    ->where('reqe.created_at', $val->createat)
                    ->groupBy('reqe.questionnaire_id')
                    ->orderBy('reqe.created_at', 'desc')
                    ->first();

                // return $queryScorePro;

                if ($queryScorePro) {
                    if ($queryScorePro->evaluation >= $val->qualify_from) {
                        $status_pro = "ผ่านเกณฑ์";
                    } else if ($queryScorePro->evaluation <= $val->fail_to) {
                        $status_pro = "ไม่ผ่านเกณฑ์";
                    }/* else{
                            $status_pro = "ไม่ผ่านเกณฑ์".$data_score_pro['evaluation'];
                        } */
                }
            } elseif ($val->grade_type == 2) {
                //เริ่มหาการคำนวณแบบตัดเกรด
                // $sql_sum_pro = "SELECT questionnaire_id,sum(reqe.score) AS sum_score
                //             FROM rudy_evaluate_questionnaire_eco reqe
                //             JOIN rudy_questionnaire_eco rqe ON rqe.id = reqe.questionnaire_id AND rqe.questionnaire_type = 2
                //                 AND rqe.grade_type = 2
                //             WHERE reqe.vendor_id = $vendor_id and reqe.questionnaire_id = " . $val['questionnaire_id'] . " AND reqe.choice_id != 0
                //             and reqe.created_at = '" . $val['createat'] . "' GROUP BY reqe.questionnaire_id ORDER BY reqe.created_at DESC ";
                // $data_sum_pro = $db->row($sql_sum_pro);

                $querySumProRaw = DB::raw("SUM(reqe.score) AS sum_score");
                $querySumPro = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', $queryScoreProRaw)
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', 'reqe.questionnaire_id')
                            ->on('rqe.questionnaire_type', 2)
                            ->on('rqe.grade_type', 2);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('reqe.questionnaire_id', $questionnaire_id)
                    ->where('reqe.choice_id', '!=', 0)
                    ->where('reqe.created_at', $val->createat)
                    ->groupBy('reqe.questionnaire_id')
                    ->orderBy('reqe.created_at', 'desc')
                    ->first();

                // return $querySumPro;

                $grade = "รอประเมิน";
                if ($querySumPro) {
                    $score = $querySumPro->sum_score;
                    if ($score <= $val->grade_d_to) {
                        $grade = "D";
                    } else if ($score >= $val->grade_c_from && $score <= $val->grade_c_to) {
                        $grade = "C";
                    } else if ($score >= $val->grade_b_from && $score <= $val->grade_b_to) {
                        $grade = "B";
                    } else if ($score >= $val->grade_a_from) {
                        $grade = "A";
                    }
                    $status_pro = $grade;
                }
            }

            $data_port[] = [
                //'system' => 2,
                'questionnaire_id'    => $val->questionnaire_id,
                'questionnaire_name'  => $val->questionnaire_name,
                'type'                => $val->grade_type,
                'q_type_name'         => self::questionnaire_types($val->questionnaire_type),
                'csc'                 => $val->region, //.' '.$val->csc,
                'status'              =>  $status_pro,
                'create_at'           => date('d/m', strtotime($val->createat)) . '/' . $year_e . date(' H:i', strtotime($val->createat)),
                'user_created'        => $val->name_created,
                'updated_at'          => date('d/m', strtotime($val->createat)) . '/' . $year_e . date(' H:i', strtotime($val->createat)),
                'created'             => $val->created_at,
            ];
        }
        $data_array = [
            'count' => $c_new,
            'list'  => $data_port,
        ];

        return $data_array;
    }


    // Todo
    public function getEvaluateData($shop_id, $vendor_id)
    {
        // $sql_vendor = "SELECT * FROM rudy_vendor cv WHERE cv.id =  $vendor_id ";
        // $data_vendor = $db->row($sql_vendor);
        $queryVendor = DB::table('rudy_vendor as cv')->where('cv.id', $vendor_id)->frist();

        /* ประเมินทัศนคติ */
        // $sql_evamore = "SELECT *
        //             FROM rudy_questionnaire_eco rqe
        //             INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
        //             JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqce.questionnaire_id
        //             WHERE rqce.region = '" . $queryVendor->region . "' AND rqe.active = 0 and rqe.questionnaire_type = 1 AND reqe.vendor_id = $vendor_id ORDER BY reqe.created_at DESC LIMIT 1 ";
        // $data_evamore = $db->rows($sql_evamore);
        $queryEvaMore = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', 'rqce.questionnaire_id')
            ->where('rqce.region', $queryVendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 1)
            ->where('reqe.vendor_id', $vendor_id)
            ->orderBy('reqe.created_at', 'desc')
            ->limit(1)
            ->first();

        return $queryEvaMore;

        // $sql_c = "SELECT rqe.id
        //             FROM rudy_questionnaire_eco rqe
        //             INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
        //             JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqce.questionnaire_id
        //             WHERE rqce.region = '" . $data_vendor['region'] . "' AND rqe.active = 0 AND rqe.STATUS = 1 and rqe.questionnaire_type = 1 AND reqe.vendor_id = $vendor_id GROUP BY reqe.created_at";
        // $data_c = $db->rows($sql_c);
        // $c_new = count($data_c);
        $status_evamore = "";
        foreach ($data_evamore as $val_evamore) {
            if ($val_evamore['grade_type'] == 1) {
                $sql_score = "SELECT questionnaire_id,
                        SUM(reqe.score) AS evaluation
                         FROM rudy_evaluate_questionnaire_eco reqe
                         JOIN rudy_questionnaire_eco rqe ON rqe.id = reqe.questionnaire_id AND rqe.questionnaire_type = 1 AND rqe.grade_type = 1 AND reqe.choice_id != 0
                         WHERE reqe.vendor_id = $vendor_id and rqe.id = '" . $val_evamore['questionnaire_id'] . "'
                         GROUP BY reqe.questionnaire_id ORDER BY evaluation DESC LIMIT 1 ";
                $data_score = $db->row($sql_score);
                if ($data_score) {
                    if ($data_score['evaluation'] >= $val_evamore['qualify_from'] && $data_score['evaluation'] <= $val_evamore['qualify_to']) {
                        $status_evamore = "ผ่านเกณฑ์";
                    } else if ($data_score['evaluation'] >= $val_evamore['fail_from'] && $data_score['evaluation'] <= $val_evamore['fail_to']) {
                        $status_evamore = "ไม่ผ่านเกณฑ์";
                    }
                }
            } else if ($val_evamore['grade_type'] == 2) {
                $sql_score = "SELECT questionnaire_id,
                        SUM(reqe.score) AS evaluation
                         FROM rudy_evaluate_questionnaire_eco reqe
                         JOIN rudy_questionnaire_eco rqe ON rqe.id = reqe.questionnaire_id AND rqe.questionnaire_type = 1 AND rqe.grade_type = 2 AND reqe.choice_id != 0
                         WHERE reqe.vendor_id = $vendor_id and rqe.id = '" . $val_evamore['questionnaire_id'] . "'
                         GROUP BY reqe.questionnaire_id ORDER BY evaluation DESC LIMIT 1 ";
                $data_score = $db->row($sql_score);
                $score = 0;
                if ($data_score) {
                    $score = $data_score['evaluation'];

                    if ($score <= $val_evamore['grade_d_to']) {
                        $status_evamore = "D";
                    } else if ($score >= $val_evamore['grade_c_from'] && $score <= $val_evamore['grade_c_to']) {
                        $status_evamore = "C";
                    } else if ($score >= $val_evamore['grade_b_from'] && $score <= $val_evamore['grade_b_to']) {
                        $status_evamore = "B";
                    } else if ($score >= $val_evamore['grade_a_from']) {
                        $status_evamore = "A";
                    }
                }
            }
        }

        ###################################

        $sql_eva_c = "SELECT *
                FROM rudy_vendor_evaluation_eco rvee WHERE rvee.vendor_id = $vendor_id LIMIT 1";
        $data_eva_c = $db->rows($sql_eva_c);
        $c_eva = count($data_eva_c);
        ##################################
        $sql_eva = "SELECT case
                    when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
                            JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = $vendor_id) >= 70 then 'ผ่านเกณฑ์'
                    when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
                            JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = $vendor_id) < 70 then 'ไม่ผ่านเกณฑ์'
                    ELSE 'รอประเมิน'
                    END AS evaluation
                FROM rudy_vendor_evaluation_eco rvee WHERE rvee.vendor_id = $vendor_id";
        $data_eva = $db->row($sql_eva);
        //print_r($data_score['evaluation'] );
        ##################################
        $status_eva = "รอประเมิน";
        if ($c_new > 0) {
            $status_eva = $status_evamore;
        } else {
            $status_eva = $data_eva['evaluation'];
        }


        /* ประเมินโปรเฟส */
        $sql_pro = "SELECT *
            FROM rudy_questionnaire_eco rqe
            INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
            JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqce.questionnaire_id
            WHERE rqce.region = '" . $data_vendor['region'] . "' AND rqe.active = 0 and rqe.questionnaire_type = 2 and reqe.vendor_id = $vendor_id ORDER BY reqe.created_at DESC LIMIT 1";
        $data_pro = $db->rows($sql_pro);
        $sql_pc = "SELECT *
            FROM rudy_questionnaire_eco rqe
            INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
            JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqce.questionnaire_id
            WHERE rqce.region = '" . $data_vendor['region'] . "' AND rqe.active = 0  and rqe.questionnaire_type = 2 and reqe.vendor_id = $vendor_id GROUP BY reqe.created_at";
        $data_cp = $db->rows($sql_pc);
        $c_pro = count($data_cp);
        //print_r($sql_pro);
        $status_pro = "รอประเมิน";
        $grade = "";
        $status_end = "";
        foreach ($data_pro as $val_pro) { //วนหาแยกตามชนิดการตัดเกรด กรณีแบบฟอร์มเปลี่ยนทีหลัง
            //print_r($val_pro['grade_type']);
            if ($val_pro['grade_type'] == 1) { //1=คำนวณผ่านเกณฑ์,
                $sql_score_pro = "SELECT questionnaire_id,
                    SUM(reqe.score) AS evaluation
                    FROM rudy_evaluate_questionnaire_eco reqe
                    JOIN rudy_questionnaire_eco rqe ON rqe.id = reqe.questionnaire_id AND rqe.questionnaire_type = 2
                    AND rqe.grade_type = 1 AND reqe.choice_id != 0
                    WHERE reqe.vendor_id = $vendor_id and rqe.id = '" . $val_pro['questionnaire_id'] . "' ";
                $data_score_pro = $db->row($sql_score_pro);
                if ($data_score_pro) {
                    if ($data_score_pro['evaluation'] >= $val_pro['qualify_from']) {
                        $status_pro = "ผ่านเกณฑ์";
                    } else if ($data_score_pro['evaluation'] <= $val_pro['fail_to']) {
                        $status_pro = "ไม่ผ่านเกณฑ์";
                    }
                }
            } else if ($val_pro['grade_type'] == 2) { //2=คำนวณตัดเกรด
                $sql_sum_pro = "SELECT questionnaire_id,sum(reqe.score) AS sum_score
                    FROM rudy_evaluate_questionnaire_eco reqe
                    JOIN rudy_questionnaire_eco rqe ON rqe.id = reqe.questionnaire_id AND rqe.questionnaire_type = 2
                     AND rqe.grade_type = 2 AND reqe.choice_id != 0
                    WHERE reqe.vendor_id = $vendor_id and rqe.id = '" . $val_pro['questionnaire_id'] . "' ";
                $data_sum_pro = $db->row($sql_sum_pro); //2=คำนวณตัดเกรด
                //print_r($sql_sum_pro);
                $score = 0;
                if ($data_sum_pro) {
                    $score = $data_sum_pro['sum_score'];

                    if ($score <= $val_pro['grade_d_to']) {
                        $status_pro = "D";
                    } else if ($score >= $val_pro['grade_c_from'] && $score <= $val_pro['grade_c_to']) {
                        $status_pro = "C";
                    } else if ($score >= $val_pro['grade_b_from'] && $score <= $val_pro['grade_b_to']) {
                        $status_pro = "B";
                    } else if ($score >= $val_pro['grade_a_from']) {
                        $status_pro = "A";
                    }
                }
            }
        }

        $sql_sol_grade = "SELECT case
                when rv.grade_eco = 1 then 'A'
                when rv.grade_eco = 2 then 'B'
                when rv.grade_eco = 3 then 'C'
                when rv.grade_eco = 4 then 'D'
                ELSE null
            END AS grade
                FROM rudy_vendor rv
                WHERE rv.id  = $vendor_id ";
        $data_sol_grade = $db->row($sql_sol_grade);

        $sql_sol = "SELECT rvpe.id FROM rudy_vendor_portfolio_eco rvpe
            JOIN rudy_vendor_project_solution_eco rvpse ON rvpse.portfolio_id = rvpe.id
            WHERE rvpe.vendor_id =  $vendor_id and rvpse.draf = 1 and rvpse.status = 1 GROUP BY rvpe.id ";
        $data_sol = $db->rows($sql_sol);
        $c_solution = count($data_sol);
        //print_r($sql_sol);
        ###################################

        $data_attitude[] = [
            'count_attitude' => $c_new + $c_eva,
            'status' => $status_eva
        ];

        $data_professional[] = [
            'count_professional' => $c_pro,
            'status' => $status_pro
        ];

        $data_solution[] = [
            'count_solution' => $c_solution,
            'status' => $data_sol_grade['grade']
        ];

        $data_array = [
            'count_all' => ($c_new + $c_eva) + ($c_pro + $c_solution),
            'data_attitude' => $data_attitude,
            'data_professional' => $data_professional,
            'data_solution' => $data_solution,
        ];
        return $data_array;
    }


    public function dataEvaluation($shop_id, $vendor_id)
    {
        $query = DB::table('rudy_vendor_evaluation_eco as rvee')
            ->join('rudy_eco_evaluation_answer as reen', 'reen.id', 'rvee.answer')
            ->join('rudy_eco_evaluation as rev', 'rev.id', 'rvee.question')
            ->select(
                'rvee.id',
                'rvee.group',
                'rvee.question as question_id',
                'rev.question as question_name',
                'rvee.answer as answer_id',
                'reen.answer as answer_name',
                'reen.score',
                'rvee.updated_at'
            )
            ->where('rvee.shop_id', $shop_id)
            ->where('rvee.vendor_id', $vendor_id)
            ->get();

        return $query;
    }

    public function starMark($vendorId)
    {
        try {
            // echo $vendorId;
            // exit;
            if ($vendorId) {
                $query = DB::table('rudy_vendor')->select('starmark')->where('id', $vendorId)->first();
                $exec = $query->starmark;
                if ($exec == 0) {
                    $starmark = 1;
                } else {
                    $starmark = 0;
                }

                DB::table('rudy_vendor')
                    ->where('id', $vendorId)
                    ->update([
                        'starmark' => $starmark,
                    ]);
                return 'success';
            } else {
                throw new Exception("Error Processing Request. Vendor Id not found", 1);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function updateStatus($args)
    {

        // $vendor_id = $params['vendor_id'];
        $vendorId = $args['vendor_id'];
        $id = generateNextId('rudy_vendor_history_eco');
        // $sql_his2 = "SELECT * FROM rudy_vendor_history_eco WHERE vendor_id = $vendor_id
        //  and status !=4 order by id desc limit 1";
        // $data_his2 = $db->row($sql_his2);

        $query = DB::table('rudy_vendor_history_eco')
            ->where('vendor_id', $vendorId)
            ->where('status', '!=', 4)
            ->orderBy('id', 'desc')
            ->first();


        $insertHistory = DB::table('rudy_vendor_history_eco')->insert(
            [
                'id'                => $id,
                'vendor_id'         => $args['vendor_id'],
                'user_id'           => $args['user_id'],
                'status'            => $args['status'],
                'comment'           => $args['comment'],
                'comment_id'        => $args['comment_id'],
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
        );

        // $id_his = $db->insert('rudy_vendor_history_eco', [
        //   'vendor_id'         => $params['vendor_id'],
        //   'user_id'           => $params['user_id'],
        //   'status'            => $params['status'],
        //   'comment'           => $params['comment'],
        //   'comment_id'        => $params['comment_id'],
        //   'created_at'        => date('Y-m-d H:i:s'),
        //   'updated_at'        => date('Y-m-d H:i:s'),
        // ]);

        if ($args['status'] == 4) { //แบน
            // $db->update('rudy_vendor', [
            //   'status'            => $args->status,
            //   'status2'  => 3, //inactive
            //   'status_comment'    => $args->comment,
            //   'last_update'       => date('Y-m-d H:i:s'),
            // ], ['id' => $args->vendor_id]);
            DB::table('rudy_vendor')
                ->where('id', $vendorId)
                ->update(
                    [
                        'status'            => $args['status'],
                        'status2'           => 3,                 //inactive
                        'status_comment'    => $args['comment'],
                        'last_update'       => date('Y-m-d H:i:s'),
                    ]
                );


            // $id_ban = $db->insert('rudy_vendor_banned_eco', [
            //   'history_id'         => $id_his,
            //   'vendor_id'         => $args->vendor_id,
            //   'comment'           => $args->comment,
            //   'created_at'        => date('Y-m-d H:i:s'),
            // ]);

            DB::table('rudy_vendor_banned_eco')->insert(
                [
                    'history_id'        => $id,
                    'vendor_id'         => $args['vendor_id'],
                    'comment'           => $args['comment'],
                    'created_at'        => date('Y-m-d H:i:s'),
                ]
            );

            // $db->update('rudy_vendor_history_eco', [
            //   'status2'           => 3,
            //   'updated_at'        => date('Y-m-d H:i:s'),
            // ], ['id' => $id_his]);

            DB::table('rudy_vendor_history_eco')
                ->where('id', $id)
                ->update([
                    'status2'    => 3,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if (!empty($args['file_banned'])) {
                foreach ($args['file_banned'] as $file) {
                    $fdFile = "eco_portal/vendor/files_solution";
                    // $this->UploadFile_Solution($filename['file'], $filename['file_name'], $id_his, 4);
                    $type = 4;
                    // $names = File::uploadFilesBase64($file['file'], $file['file_name'], $fdFile, $specific_name = null, $id);
                    $names = File::uploadFileName($file['file'], $fdFile, $file['file_name']);
                    if ($names) {
                        if ($type == 4) { //banned
                            // $db->insert('rudy_file_banned_eco', [
                            //   'banned_id'     => $id,
                            //   'file_name'      => $names,
                            //   //'name'   => $names,
                            //   'created_at'    => $this->now,
                            //   'updated_at'    => $this->now,
                            // ]);

                            DB::table('rudy_file_banned_eco')->insert(
                                [
                                    'banned_id'     => $id,
                                    'file_name'     => $names,
                                    'created_at'    => date('Y-m-d H:i:s'),
                                    'updated_at'    => date('Y-m-d H:i:s'),
                                ]
                            );
                        }
                    }
                }
            }
        } else { //ยกเลิกแบน
            // $id_ban = $db->insert('rudy_vendor_banned_eco', [
            //   'history_id'         => $id_his,
            //   'vendor_id'         => $vendor_id,
            //   'comment'           => $args->comment,
            //   'status_banned'     => 0,
            //   'created_at'        => date('Y-m-d H:i:s'),
            // ]);

            DB::table('rudy_vendor_banned_eco')->insert([
                'history_id'        => $id,
                'vendor_id'         => $vendorId,
                'comment'           => $args['comment'],
                'status_banned'     => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            if (!empty($args['file_banned'])) {
                foreach ($args['file_banned'] as $file) {
                    // $this->UploadFile_Solution($filename['file'], $filename['file_name'], $id_his, 4);
                    $fdFile = "eco_portal/vendor/files_solution";
                    $type = 4;
                    // $names = File::uploadFilesBase64($file['file'], $file['file_name'], $fdFile, $specific_name = null, $id);
                    $names = File::uploadFileName($file['file'], $fdFile, $file['file_name']);
                    if ($names) {
                        if ($type == 4) { //banned
                            DB::table('rudy_file_banned_eco')->insert([
                                'banned_id'     => $id,
                                'file_name'     => $names,
                                'created_at'    => date('Y-m-d H:i:s'),
                                'updated_at'    => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }


            if ($query->status == 0) { //สถานะก่อนหน้าปลดแบนเป็น Register
                // $db->update('rudy_vendor', [
                //   'status'  => '0', //Register
                //   'status2'  => $data_his2['status2'],
                // ], ['id' => $vendor_id]);
                DB::table('rudy_vendor')
                    ->where('id', $vendorId)
                    ->update([
                        'status'  => '0', //Register
                        'status2'  => $query->status2,
                    ]);
                //print_r("0");

                // $db->update('rudy_vendor_history_eco', [
                //   'status'            => 0,
                //   'status2'           => $data_his2['status2'],
                //   'updated_at'        => date('Y-m-d H:i:s'),
                // ], ['id' => $id_his]);
                DB::table('rudy_vendor_history_eco')
                    ->where('id', $id)
                    ->update([
                        'status'            => 0,
                        'status2'           => $query->status2,
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ]);
            } else if ($query->status == 2) { //verified
                if ($query->status2 == 1) { //active
                    // $db->update('rudy_vendor', [
                    //   'status'  => '2', //verified
                    //   'status2'  => 1, //active
                    // ], ['id' => $vendor_id]);

                    DB::table('rudy_vendor')
                        ->where('id', $vendorId)
                        ->update([
                            'status'  => '2', //verified
                            'status2'  => 1, //active
                        ]);

                    // $db->update('rudy_vendor_history_eco', [
                    //   'status'   => 2,
                    //   'status2'  => 1,
                    //   'updated_at' => date('Y-m-d H:i:s'),
                    // ], ['id' => $id_his]);

                    DB::table('rudy_vendor_history_eco')
                        ->where('id', $id)
                        ->update([
                            'status'     => 2,
                            'status2'    => 1,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                } else { //ก่อนหน้าเป็น verifield แต่ยังไม่ active
                    // $db->update('rudy_vendor', [
                    //   'status'  => '2',
                    //   'status2'  => NULL,
                    // ], ['id' => $vendor_id]);
                    DB::table('rudy_vendor')
                        ->where('id', $vendorId)
                        ->update([
                            'status'  => '2',
                            'status2'  => NULL,
                        ]);

                    //print_r("21"+$vendor_id);

                    // $db->update('rudy_vendor_history_eco', [
                    //   'status'   => 2,
                    //   'status2'  => NULL,
                    //   'updated_at' => date('Y-m-d H:i:s'),
                    // ], ['id' => $id_his]);
                    DB::table('rudy_vendor_history_eco')
                        ->where('id', $id)
                        ->update([
                            'status'     => 2,
                            'status2'    => NULL,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    //print_r("2".$vendor_id);
                }
            }
        }
        return 'success';
    }

    public function insertPortfolio($args)
    {
        // dd($args);
        // exit;
        $shop_id      = $args['shop_id'];
        $vendor_id    = $args['vendor_id'];
        $project_name = $args['project_name'];
        // $solution_id  = $args['solution_id'];
        $created_by   = $args['created_by'];
        $details      = $args['details'];
        $project_id   = $args['project_id'];

        if ($vendor_id) {
            $id = generateNextId('rudy_vendor_portfolio_eco');
            DB::table('rudy_vendor_portfolio_eco')->insert([
                'id'              => $id,
                'vendor_id'       => $vendor_id,
                'shop_id'         => $shop_id,
                'project_name'    => $project_name,
                'created_by'      => $created_by,
                'details'         => $details,
                'project_id'      => $project_id,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            if (count($args['solution']) > 0) {
                foreach ($args['solution'] as $val_so) {
                    DB::table('rudy_project_solution_eco')->insert([
                        'portfolio_id'  => $id,
                        'solution'      => $val_so['solution_id'],
                        'subsolution'   => $val_so['subso_id'],
                    ]);
                }
            }
            if (!empty($args['img_portfolio'])) {
                foreach ($args['img_portfolio'] as $file) {
                    $fdFile = "eco_portal/vendor/files_solution";
                    $type = 2;
                    $names = File::uploadFileBase64($file['file'], $fdFile);

                    if ($type == 2) { //portfolio ผลงาน
                        DB::table('rudy_vendor_portfolio_img_eco')->insert([
                            'portfolio_id'  => $id,
                            'filename'      => $names,
                            'created_at'    => date('Y-m-d H:i:s'),
                            'updated_at'    => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
            ########################### CLOSE STATUS #################################
            $queryP = DB::table('rudy_vendor_portfolio_eco')->where('vendor_id', $vendor_id)->select('id')->get();
            $countQueryP = $queryP->count();

            $queryS = DB::table('rudy_vendor')->where('id', $vendor_id)->select('status')->first();

            if ($countQueryP > 0) //อัพเดทสถานะเมื่อมีการเพิ่มผลงาน
            {
                if ($queryS->status != '4') {
                    DB::table('rudy_vendor')->where('id', $vendor_id)->update([
                        'status2'     => 1, //active
                        'last_update' => date('Y-m-d H:i:s'),
                    ]);

                    $historyListId = self::historyStatusLast($vendor_id);
                    DB::table('rudy_vendor_history_eco')
                        ->where('id', $historyListId)
                        ->update([
                            'status2'    => 1, //active
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }
            ########################### CLOSE STATUS #################################
        }
        return [
            'status_exec' => true,
            'message' => 'Insert portfolio successfully.'
        ];
    }

    public function updatePortfolio($args)
    {
        // dd($args);
        // exit;

        $shop_id      = $args['shop_id'];
        $vendor_id    = $args['vendor_id'];
        $project_name = $args['project_name'];
        $updated_by   = $args['updated_by'];
        $details      = $args['details'];
        $id           = $args['portfolio_id'];

        if ($vendor_id) {
            // $db->update('rudy_vendor_portfolio_eco', [
            //   'vendor_id'       => $vendor_id,
            //   'shop_id'         => $shop_id,
            //   'project_name'    => $project_name,
            //   'updated_by'      => $updated_by,
            //   'details'         => $params['details'],
            //   'updated_at'      => $this->now,
            // ], ['id' => $id]);
            DB::table('rudy_vendor_portfolio_eco')->where('id', $id)->update([
                'vendor_id'       => $vendor_id,
                'shop_id'         => $shop_id,
                'project_name'    => $project_name,
                'updated_by'      => $updated_by,
                'details'         => $details,
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
            // return $update;
            if (count($args['solution']) > 0) {
                // $db->delete('rudy_project_solution_eco', ['portfolio_id' => $id]);
                DB::table('rudy_project_solution_eco')
                    ->where('portfolio_id', $id)
                    ->delete();

                foreach ($args['solution'] as $val_so) {
                    // $db->insert('rudy_project_solution_eco', [
                    //   'portfolio_id'         => $id,
                    //   'solution'       => $val_so['solution_id'],
                    //   'subsolution'   => $val_so['subso_id'],
                    // ]);

                    DB::table('rudy_project_solution_eco')->insert([
                        'portfolio_id' => $id,
                        'solution'     => $val_so['solution_id'],
                        'subsolution'  => $val_so['subso_id'],
                    ]);
                }
            }

            if (!empty($args['img_portfolio'])) {
                foreach ($args['img_portfolio'] as $file) {
                    if ($file['file_id'] == 0) {
                        // $this->UploadFile_Solution($filename['file'], $filename['file_name'], $id, 2);
                        $fdFile = "eco_portal/vendor/files_solution";
                        $type = 2;
                        $names = File::uploadFileBase64($file['file'], $fdFile);

                        if ($type == 2) { //portfolio ผลงาน
                            DB::table('rudy_vendor_portfolio_img_eco')->insert([
                                'portfolio_id'  => $id,
                                'filename'      => $names,
                                'created_at'    => date('Y-m-d H:i:s'),
                                'updated_at'    => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }
        }
        return [
            'status_exec' => true,
            'message'     => 'Update portfolio successfully.'
        ];
    }

    public function historyStatusLast($vendor_id)
    {
        $query = DB::table('rudy_vendor_history_eco')
            ->where('vendor_id', $vendor_id)
            ->select('id', 'status', 'status2')
            ->orderBy('id', 'desc')
            ->first();
        $id = $query->id;
        return $id;
    }

    public function deletePortfolio($args)
    {
        // dd($args);
        // exit;

        $shop_id      = $args['shop_id'];
        $portfolio_id = $args['portfolio_id'];
        $vendor_id    = $args['vendor_id'];
        $status       = $args['status'];
        $remarks      = $args['remarks'];

        $query = DB::table('rudy_vendor_portfolio_eco')->where('id', $portfolio_id)->first();
        if ($query && $query->status != 1) {

            DB::table('rudy_vendor_portfolio_eco')
                ->where('id', $portfolio_id)
                ->update([
                    'status'      => $status, // 1=reject, 2=delete
                    'remark'      => $remarks,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

            if ($query->project_eco_id > 0) {
                $project_eco_id = $query->project_eco_id;

                $queryP = DB::table('rudy_vendor_project_eco')->where('id', $project_eco_id)->first();
                DB::table('rudy_vendor_project_eco')
                    ->where('id', $project_eco_id)
                    ->update([
                        'status'      => 1,
                        'remark'      => $remarks,
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
            }

            $queryEvea = DB::table('rudy_vendor_project_solution_eco')
                ->where('portfolio_id', $portfolio_id)
                ->get();

            if (!empty($queryEvea)) {
                foreach ($queryEvea as $v_eva) {
                    DB::table('rudy_vendor_project_solution_eco')->where('id', $v_eva->id)->update([
                        'status' => 0,
                    ]);
                }

                $gradeLast = self::averageGradeProject($vendor_id);

                DB::table('rudy_vendor')
                    ->where('id', $vendor_id)
                    ->update([
                        'grade_eco'    => $gradeLast,
                    ]);
            }

            return [
                'status_exec' => true,
                'message'     => 'Delete portfolio successfully.'
            ];
        } else {
            return [
                'status_exec' => false,
                'message'     => "This portfolio status is reject already !."
            ];
        }
    }

    public function averageGradeProject($vendor_id)
    {
        $grade = "";
        $score = 0;

        $queryCount = DB::table('rudy_vendor_project_solution_eco')
            ->where('vendor_id', $vendor_id)
            ->whereNotIn('grade', ['ไม่ผ่านเกณฑ์', 'ผ่านเกณฑ์'])
            ->where('draf', '1')
            ->groupBy('created_at')
            ->get();

        $count_n = $queryCount->count();
        $sum_aver = 0;
        $aa = 0;
        foreach ($queryCount as $val) {
            if ($val->grade == 'A') {
                $sum_aver += 4;
            } else if ($val->grade == 'B') {
                $sum_aver += 3;
            } else if ($val->grade == 'C') {
                $sum_aver += 2;
            } else {
                $sum_aver += 1;
            }
        }

        $score = ($sum_aver / $count_n);
        if (round($score) > 0 && round($score) <= 1) {
            $grade = 4; //D
        } else if (round($score) > 1 && round($score) <= 2) {
            $grade = 3; //C
        } else if (round($score) > 2 && round($score) <= 3) {
            $grade = 2; //B
        } else if (round($score) >= 4) { //A
            $grade = 1;
        }

        return ($grade);
    }


    public function listProjects($shopId)
    {
        $query = DB::table('rudy_projects')
            ->select('id', 'project_name', 'project_code')
            ->where('shop_id', $shopId)
            ->whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->where('project_name', '!=', '-')
            ->orderBy('project_name', 'asc')
            ->get();

        $data_array = [];
        foreach ($query as $val) {
            $data_array[] = [
                'id'           => $val->id,
                'project_name' => $val->project_code . ' ' . $val->project_name,
                'name'         => $val->project_name
            ];
        }
        // $a[] = [
        //   'id'           => 0,
        //   'project_name' => "อื่นๆ",
        // ];
        // $merge = array_merge($a, $data_array);

        return ($data_array);
    }

    /* เพิ่ม project ที่ต้องการจะประเมินเก็บเป็นผลงานด้วย เป็นการเพิ่มจากระบบ eco ไม่ได้ถูก assign มาจาก cwork */
    public function insertProjectSolution($args)
    {

        $shop_id      = $args['shop_id'];
        $vendor_id    = $args['vendor_id'];
        $project_id   = $args['project_id'];
        $project_name = $args['project_name'];
        $created_by   = $args['created_by']; //

        if ($vendor_id) {
            // Table : rudy_vendor_portfolio_eco
            $id = generateNextId('rudy_vendor_portfolio_eco');
            DB::table('rudy_vendor_portfolio_eco')->insert([
                'id'                     => $id,
                'vendor_id'              => $vendor_id,
                'shop_id'                => $shop_id,
                'project_name'           => $project_name,
                'created_by'             => $created_by,
                'project_id'             => $project_id,
                'questionnaire_solution' => 1,
                'created_at'             => date('Y-m-d H:i:s'),
                'updated_at'             => date('Y-m-d H:i:s'),
            ]);

            //rudy_vendor_project_solution_eco
            if (!empty($args['img'])) {
                foreach ($args['img'] as $file) {
                    if ($file['file_id'] == 0) {
                        $fdFile = "eco_portal/vendor/files_solution";
                        $type = 2;
                        $names = File::uploadFileBase64($file['file'], $fdFile);
                        if ($type == 2) { //portfolio ผลงาน
                            DB::table('rudy_vendor_portfolio_img_eco')->insert([
                                'portfolio_id'  => $id,
                                'filename'      => $names,
                                'created_at'    => date('Y-m-d H:i:s'),
                                'updated_at'    => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }

            if (count($args['solution']) > 0) {
                foreach ($args['solution'] as $key_solution => $solution) { //วนลูปบันทึกตามจำนวน solution ที่ส่งมา
                    DB::table('rudy_project_solution_eco')->insert([
                        'portfolio_id'  => $id,
                        'solution'      => $solution['solution_id'],
                        'subsolution'   => $solution['subso_id'],
                    ]);
                }
            }
        }
        return $vendor_id;
    }

    ########################################### END ECO Detail ####################################
}
