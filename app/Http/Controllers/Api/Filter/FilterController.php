<?php

namespace App\Http\Controllers\Api\Filter;

use App\Http\Controllers\Controller;
use Dotenv\Repository\RepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use App\Repositories\FilterRepository as Filter;
use Throwable;
use Illuminate\Support\Facades\Validator;

class FilterController extends Controller
{
    /**
     * Constructor : Initial process function use for inject depends
     */
    function __construct()
    { }

    /**
     * > This function is used to filter the chain.
     *
     * @param Request request The request object
     *
     * @return A JSON response with a status of OK and an array of chain.
     */
    function filterChain(Request $request)
    {
        $shopId = $request->shop_id;
        try {
            $item = Filter::region($shopId);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }


    /**
     * > This function is used to filter the CSCs based on the given parameters
     *
     * @param Request request The request object
     */
    function filterCSC(Request $request)
    {
        $csc = $request->csc;
        $region = $request->region;

        try {
            $item = Filter::csc($csc, $region);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            /* A function that is called filterSolution. It takes a request as a parameter. */
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    /**
     * > This function is used to filter the geography
     *
     * @param Request request The request object
     */
    function filterGeography(Request $request)
    {
        try {
            $item = FIlter::geoGraphy();
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    /**
     * > This function is used to filter the solution based on the given parameters
     *
     * @param Request request The request object
     */
    function filterSolution(Request $request)
    {
        $shopId = $request->shop_id;
        $userId = $request->user_id;
        $search = trim($request->search);
        try {
            $item = Filter::solution($shopId, $userId, $search);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    function filterSolutionAll(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shop_id'   => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $shopId = $request->shop_id;

        try {
            $item = Filter::getSolutionAll($shopId);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    function filterSubSolution(Request $request)
    {
        $shopId = $request->shop_id;
        $solutionId = $request->solution_id;
        try {
            $item = Filter::subSolution($shopId, $solutionId);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    /**
     * > This function returns a list of provinces based on the country selected
     *
     * @param Request request The request object
     */
    /* This function returns a list of provinces based on the country selected */
    function filterProvinces(Request $request)
    {
        $search = trim($request->search);
        $geoId = $request->geo_id;
        try {
            $item = Filter::provinces($search, $geoId);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    function filterGroupProvinces(Request $request)
    {
        try {
            $items = [];
            $provinces = Filter::provincesGroup();

            foreach ($provinces as $province) {
                $items[$province->GEO_NAME][] = [
                    "GEO_ID"        => $province->GEO_ID,
                    "PROVINCE_ID"   => $province->PROVINCE_ID,
                    "PROVINCE_NAME" => $province->PROVINCE_NAME
                ];
            }

            $res = [
                "status" => "OK",
                "item" => $items
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    /**
     * > This function returns a list of regions based on the country selected.
     * > Old Rest API get_region.
     *
     * @param Request request The request object
     */
    function filterRegions(Request $request)
    {
        $shopId = $request->shop_id;
        try {
            $item = Filter::region($shopId);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    // Get User admin
    function filterUserAdmin(Request $request)
    {
        try {
            $shopId = $request->shop_id;
            $search = $request->search;
            $item = Filter::userAdmin($shopId, $search);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    function filterUser(Request $request)
    {
        try {
            $shopId = $request->shop_id;
            $item = Filter::user($shopId);
            $res = [
                "status" => "OK",
                "item" => $item
            ];

            return response()->json($res, 200);
        } catch (Throwable $e) {
            return response()->json([
                "status" => "FAILED",
                "msg" => $e->getMessage()
            ], 503);
        }
    }

    function filterStatus()
    {
        try {
            $list = [
                0 => 'Registed',
                1 => 'Verified',
                2 => 'Banned'
            ];

            return response()->json(['status' => 'OK', 'item' => $list], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterSubsegment(Request $request)
    {
        $shopId = $request->shop_id;
        $search = $request->search;
        $segment = $request->segment;
        // "segment_id": 1
        try {
            $list = Filter::getSubsegment($shopId, $search, $segment);
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterStaff()
    {
        try {
            $list = Filter::getStaff();
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterWorkExp()
    {
        try {
            $list = Filter::getWorkExp();
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterCapital()
    {
        try {
            $list = Filter::getCapital();
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterDataCompany(Request $request)
    {
        $shopId = $request->shop_id;
        try {
            $list = Filter::dataCompany($shopId);
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterEntityType()
    {
        try {
            $list = Filter::entityType();
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterCustomerType(Request $request)
    {
        $shopId  = $request->shop_id;
        try {
            $list = Filter::customerType($shopId);
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }

    function filterWorkType(Request $request)
    {
        $shopId  = $request->shop_id;
        $search  = $request->search;
        try {
            $list = Filter::getWorkType($shopId, $search);
            return response()->json(['status' => 'OK', 'item' => $list, 'total' => count($list)], 200);
        } catch (Throwable $e) {
            return response()->json(['status' => 'FAILED', 'msg' => $e->getMessage()], 500);
        }
    }
}
