<?php

namespace App\Http\Controllers\Api\Managements;

use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ProjectsRepository;
use App\Repositories\CreateProjectsRepository;
use App\Repositories\SubECODetailRepository;

use App\Adapters\Response;
use App\Repositories\CommentsRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SizingRepository;
use App\Repositories\SkillsRepository;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Validator;

class ManagementController extends Controller
{

    public function getProjectList(Request $request)
    {
        $args = [
            'shopId'                => $request->shop_id,
            'page'                  => $request->page,
            'search'                => $request->search,
            'searchQuestion'        => $request->question,
            'grade'                 => $request->grade,
            'area'                  => $request->area,
            'region'                => $request->region,
            'csc'                   => $request->csc,
            'solution'              => $request->solution,
            'admin_eco'             => $request->admin_eco,
            'assessor'              => $request->assessor,
            'choose'                => $request->choose,
            'status'                => $request->status,
            'zone'                  => $request->zone,
            'projectId'             => $request->project_id,
            'solutionCWork'         => $request->solution_id,
            'subSolutionCWork'      => $request->sub_solution_id,
            'popupStauts'           => $request->popup_status,
            'cwork'                 => $request->cwork,
            'subSolution'           => $request->sub_slolution
        ];

        try {
            $items = ProjectsRepository::getProjectList($args);

            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 500);
        }
    }

    // Insert ECO data
    public function insertECO(Request $request)
    {
        $args = [
            "shop_id"       => $request->shop_id,
            "customer_type" => $request->customer_type,
            "profile_img"   => $request->profile_img,
            "vendor_type"   => $request->vendor_type,
            "gender"        => $request->gender,
            "grade"         => $request->grade,
            "prefix_name"   => $request->prefix_name,
            "name"          => $request->name,
            "nickname"      => $request->nickname,
            "region"        => $request->region,
            "company"       => $request->company,
            "csc"           => $request->csc,
            "tel"           => $request->tel,
            "ext"           => $request->ext,
            "tax_no"        => $request->tax_no,
            "email"         => $request->email,
            "line_id"       => $request->line_id,
            "address"       => $request->address,
            "note"          => $request->note,
            "segment"       => $request->segment, // array
            "subsegment"    => $request->subsegment, // array
            'province1'     => $request->province1, // array
            'province2'     => $request->province2, // array
            'province3'     => $request->province3, // array
            "boq"           => $request->boq,
            "design"        => $request->design,
            "join_scg"      => $request->join_scg,
            "typeget_work"  => $request->typeget_work,
            "staff"         => $request->staff,
            "capital"       => $request->capital,
            "experience"    => $request->experience,
            "machine"       => $request->machine,
            "size"          => $request->size,
            "register_date" => $request->register_date,
            "admin_eco"     => $request->admin_eco,
            "solution"      => $request->solution, // array
            "subsolution"   => $request->subsolution, // array
            "worktype"      => $request->worktype, // array
            "username"      => $request->username,
            "viewpoint"     => $request->viewpoint,
            "qualify"       => $request->qualify,
            "month_qualify" => $request->month_qualify,
            "company_id"    => $request->company_id,
            "staff_type"    => $request->staff_type,
            "qualify"       => $request->qualify,
            'created_by'    => $request->created_by,
            'fileupload'    => $request->fileupload,
            'entity_type'   => $request->entity_type,
            'contactors'    => $request->contactors, // array
        ];
        try {
            $res = CreateProjectsRepository::insertECO($args);
            return Response::responseJson($res, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 500);
        }
    }

    // Update ECO data
    public function updateECO(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'vendor_id' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $args = [
            'vendor_id'         => $request->vendor_id,
            'customer_type'     => $request->customer_type,
            'vendor_type'       => $request->vendor_type,
            'gender'            => $request->gender,
            'grade'             => $request->grade,
            'prefix_name'       => $request->prefix_name,
            'name'              => $request->name,
            'nickname'          => $request->nickname,
            'company_id'        => $request->company_id,
            'company'           => $request->company,
            'region'            => $request->region,
            'csc'               => $request->csc,
            'tel'               => $request->tel,
            'ext'               => $request->ext,
            'tax_no'            => $request->tax_no,
            'email'             => $request->email,
            'line_id'           => $request->line_id,
            'address'           => $request->address,
            'note'              => $request->note,
            'profile_img'       => $request->profile_img,
            'segment'           => $request->segment, // array
            'subsegment'        => $request->subsegment, // array
            'solution'          => $request->solution,  // array
            'subsolution'       => $request->subsolution, //array
            'worktype'          => $request->worktype, // array
            'province1'         => $request->province1, // array
            'province2'         => $request->province2, // array
            'province3'         => $request->province3, // array
            'boq'               => $request->boq,
            'design'            => $request->design,
            'join_scg'          => $request->join_scg,
            'typeget_work'      => $request->typeget_work,
            'staff'             => $request->staff,
            'staff_type'        => $request->staff_type,
            'capital'           => $request->capital,
            'experience'        => $request->experience,
            'machine'           => $request->machine,
            'size'              => $request->size,
            'register_date'     => $request->register_date,
            'admin_eco'         => $request->admin_eco,
            'created_by'        => $request->created_by,
            'username'          => $request->username,
            'fileupload'        => $request->fileupload,
            'entity_type'       => $request->entity_type,
            "month_qualify"     => $request->month_qualify,
            "viewpoint"         => $request->viewpoint,
            "qualify"           => $request->qualify,
            'contactors'        => $request->contactors, // array
        ];

        try {
            $res = CreateProjectsRepository::updateECO($args);
            return Response::responseJson($res, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 500);
        }
    }

    // Check if vendor is exists
    public function checkDataAlready(Request $request)
    {
        try {
            $res = CreateProjectsRepository::checkDataAlready($request);
            return Response::responseJson($res, 'OK', 200);
        } catch (throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 500);
        }
    }

    public function getVendorView(Request $request)
    {

        $shopId   = $request->shop_id;
        $vendorId = $request->vendor_id;

        try {
            $items = ProjectsRepository::getVendorView($shopId, $vendorId);
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 200);
        }
    }

    public function deleteFile(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $id = $request->id;

        try {
            $items = CreateProjectsRepository::deleteFile($id);
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 200);
        }
    }

    public function listPortfolio(Request $request)
    {
        $shopId           = $request->shop_id;
        $vendorId         = $request->vendor_id;
        $statusProjectId  = $request->status_project_id;
        $statusReject     = $request->status_reject;

        try {
            $item = ProjectsRepository::listPortfolio(
                $shopId,
                $vendorId,
                $statusProjectId,
                $statusReject
            );

            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), 'FAILED', 500);
        }
    }

    public function listQuestionnaire(Request $request)
    {
        $args = [
            "shop_id"    => $request->shop_id,
            "type"       => $request->type,
            "chain"      => $request->chain,
            "csc"        => $request->csc,
            "status"     => $request->status,
            "search"     => $request->search,
            "page"       => $request->page
        ];
        try {
            $item = ProjectsRepository::listQuestionnaire($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getMSolutioList(Request $request)
    {
        $shopId = $request->shop_id;
        try {
            $item = ProjectsRepository::getMSolutioList($shopId);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }


    public function listSolutionData(Request $request)
    {
        $args = [
            "shop_id"   => $request->shop_id,
            "vendor_id" => $request->vendor_id,
            "search"    => $request->search,
            "page"      => $request->page
        ];

        try {
            $item = ProjectsRepository::listSolutionData($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function listEvaluateAttitude(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shop_id' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $args = [
            "shop_id"   => $request->shop_id,
            "vendor_id" => $request->vendor_id,
            "search"    => $request->search
        ];

        try {
            $item = ProjectsRepository::listEvaluateAttitude($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }


    public function listEvaluateProfessional(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shop_id' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $args = [
            "shop_id"   => $request->shop_id,
            "vendor_id" => $request->vendor_id,
            "search"    => $request->search
        ];

        try {
            $item = ProjectsRepository::listEvaluateProfessional($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getEvaluateData(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shop_id'   => 'required',
                'vendor_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // $item = ProjectsRepository::getEvaluateData($request->shop_id, $request->vendor_id);
            // return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function dataEvaluation(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'shop_id'   => 'required',
                'vendor_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $item = ProjectsRepository::dataEvaluation($request->shop_id, $request->vendor_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertContactor(Request $request)
    {

        $args = [
            "shopId"        => $request->shop_id,
            "vendorId"      => $request->vendor_id,
            "name"          => $request->name,
            "position"      => $request->position,
            "phone"         => $request->phone,
            "email"         => $request->email,
            "lineID"        => $request->lineID,
            "gender"        => $request->gender,
            "profileImage"  => $request->profile_img,
        ];

        try {
            $item = CreateProjectsRepository::insertContactor($args, $request->vendor_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getOwnerContactor(Request $request)
    {

        $shopId = $request->shop_id;
        $vendorId = $request->vendor_id;

        try {
            $item = CreateProjectsRepository::getOwnerContactor($shopId, $vendorId);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    // public function getContactor(Reuqst $request)
    // {
    //     $shopId = $request->shop_id;

    //     try {
    //         $item = ProjectsRepository::insertContactor($shopId);
    //         return Response::responseJson($item, 'OK', 200);
    //     } catch (Throwable $e) {
    //         return Response::responseJson($e->getMessage(), "FAILED", 500);
    //     }
    // }

    public function listUpskill(Request $request)
    {
        $shopId = $request->shop_id;
        $vendorId = $request->vendor_id;

        try {
            $item = SkillsRepository::listUpskill($shopId, $vendorId);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function learnSolution(Request $request)
    {
        try {
            $item = SkillsRepository::learnSolution();
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertUpskill(Request $request)
    {
        $args = [
            "vendor_id"      => $request->vendor_id,
            "skill_name"     => $request->skill_name,
            "shop_id"        => $request->shop_id,
            "lecturer_name"  => $request->lecturer_name,
            // "affiliation"    => $request->affiliation,
            "date_upskill"   => $request->date_upskill,
            "status"         => $request->status,
            "status_upskill" => $request->status_upskill,
            "created_by"     => $request->created_by,
            "file_name"      => $request->file_name, //array
            "solution"       => $request->solution,  // array
            "learn_solution" => $request->learn_solution,
            "man_hour"       => $request->man_hour,
            "participants"   => $request->participants,
        ];
        try {
            $item = SkillsRepository::insertUpskill($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function updateUpskill(Request $request)
    {
        $args = [
            "upskill_id"     => $request->upskill_id,
            "vendor_id"      => $request->vendor_id,
            "skill_name"     => $request->skill_name,
            "shop_id"        => $request->shop_id,
            "lecturer_name"  => $request->lecturer_name,
            // "affiliation"    => $request->affiliation,
            "date_upskill"   => $request->date_upskill,
            "status"         => $request->status,
            "status_upskill" => $request->status_upskill,
            "created_by"     => $request->created_by,
            "file_name"      => $request->file_name, //array
            "solution"       => $request->solution,  // array
            "learn_solution" => $request->learn_solution,
            "man_hour"       => $request->man_hour,
            "participants"   => $request->participants,
        ];
        try {
            $item = SkillsRepository::updateUpskill($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function listCommentData(Request $request)
    {
        $args = [
            "shop_id"    => $request->shop_id,
            "vendor_id"  => $request->vendor_id,
            "user_id"    => $request->user_id,
            "score"      => $request->score,
        ];
        try {
            $item = CommentsRepository::listCommentData($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertComment(Request $request)
    {
        $args = [
            "shopId"    => $request->shop_id,
            "vendorId"  => $request->vendor_id,
            "createdBy" => $request->created_by,
            "comments"  => $request->comments,
            "score"     => $request->score
        ];

        try {
            $item = CommentsRepository::insertComment($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function updateComment(Request $request)
    {
        $args = [
            "comment_id" => $request->comment_id,
            "comments"   => $request->comments,
            "score"      => $request->score,
            "created_by" => $request->created_by
        ];

        try {
            $item = CommentsRepository::updateComment($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function deleteComment(Request $request)
    {
        $comment_id  = $request->comment_id;

        try {
            $item = CommentsRepository::deleteComment($comment_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function starMark(Request $request)
    {
        $vendorId  = $request->vendor_id;

        try {
            $item = ProjectsRepository::starMark($vendorId);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }


    public function updateStatus(Request $request)
    {
        $args = [
            'vendor_id'     => $request->vendor_id,
            'user_id'       => $request->user_id,
            'status'        => $request->status,
            'comment'       => $request->comment,
            'comment_id'    => $request->comment_id,
            'file_banned'   => $request->file_banned
        ];

        try {
            $item = ProjectsRepository::updateStatus($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertPortfolio(Request $request)
    {
        $args = [
            'shop_id'            => $request->shop_id,
            'vendor_id'          => $request->vendor_id,
            'project_name'       => $request->project_name,
            'solution'           => $request->solution,
            'details'            => $request->details,
            'project_id'         => $request->project_id, //รอสรุป
            'img_portfolio'      => $request->img_portfolio,
            'created_by'         => $request->created_by,
        ];

        try {
            $item = ProjectsRepository::insertPortfolio($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function updatePortfolio(Request $request)
    {
        $args = [
            'shop_id'            => $request->shop_id,
            'portfolio_id'       => $request->portfolio_id,
            'vendor_id'          => $request->vendor_id,
            'project_name'       => $request->project_name,
            'solution'           => $request->solution,
            'details'            => $request->details,
            // 'project_id'         => $request->project_id, //รอสรุป
            'img_portfolio'      => $request->img_portfolio,
            'updated_by'         => $request->updated_by,
        ];

        try {
            $item = ProjectsRepository::updatePortfolio($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function deletePortfolio(Request $request)
    {
        $args = [
            'shop_id'      => $request->shop_id,
            'vendor_id'    => $request->vendor_id,
            'portfolio_id' => $request->portfolio_id,
            'remarks'      => $request->remarks,
            'status'       => $request->status,
            'vendor_id'    => $request->vendor_id
        ];

        try {
            $item = ProjectsRepository::deletePortfolio($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function listProjects(Request $request)
    {
        $shopId = $request->shop_id;

        try {
            $item = ProjectsRepository::listProjects($shopId);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertProjectSolution(Request $request)
    {
        $args = [
            'shop_id'       => $request->shop_id,
            'vendor_id'     => $request->vendor_id,
            'project_id'    => $request->project_id,
            'project_name'  => $request->project_name,
            'solution'      => $request->solution,
            'img'           => $request->img,
            'created_by'    => $request->created_by
        ];

        try {
            $item = ProjectsRepository::insertProjectSolution($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function get_solution_data(Request $request)
    {
        $sub_solution_id = $request->sub_solution_id;

        try {
            $item = SubECODetailRepository::get_solution_data($sub_solution_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function get_evaluate_data(Request $request)
    {
        $shop_id    = $request->shop_id;
        $vendor_id  = $request->vendor_id;

        try {
            $item = SubECODetailRepository::get_evaluate_data($shop_id, $vendor_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }
    public function get_evaluate_dataEx(Request $request)
    {
        $shop_id    = $request->shop_id;
        $vendor_id  = $request->vendor_id;

        try {
            $item = SubECODetailRepository::get_evaluate_data($shop_id, $vendor_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function list_evaluate_attitude(Request $request)
    {
        $shop_id    = $request->shop_id;
        $vendor_id  = $request->vendor_id;
        $search     = $request->search;

        try {
            $item = SubECODetailRepository::list_evaluate_attitude($shop_id, $vendor_id, $search);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getContactTypes()
    {
        try {
            $items = CreateProjectsRepository::getContactTypes();
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertContactorPerson(Request $request)
    {
        $args = [
            'shop_id'            => $request->shop_id,
            'vendor_id'          => $request->vendor_id,
            'name'               => $request->name,
            'nickname'           => $request->nickname,
            'position'           => $request->position,
            'contact_detail'     => $request->contact_detail,
            'contact_type_id'    => $request->contact_type_id,
            'contact_code'       => $request->contact_code,
            'extension_number'   => $request->extension_number,
            'created_at'         => $request->created_at,
            'profile_img'        => $request->profile_img,
        ];
        try {
            $items = CreateProjectsRepository::insertContactorPerson($args);
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function updateContactorPerson(Request $request)
    {
        $args = [
            'id'                 => $request->id,
            'shop_id'            => $request->shop_id,
            'vendor_id'          => $request->vendor_id,
            'name'               => $request->name,
            'nickname'           => $request->nickname,
            'position'           => $request->position,
            'contact_detail'     => $request->contact_detail,
            'contact_type_id'    => $request->contact_type_id,
            'contact_code'       => $request->contact_code,
            'extension_number'   => $request->extension_number,
            'created_at'         => $request->created_at,
            'profile_img'        => $request->profile_img,
        ];
        try {
            $items = CreateProjectsRepository::updateContactorPerson($args);
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function deleteContactPerson(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'contact_ids' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $contact_ids = $request->contact_ids; // Array of object

        try {
            $items = CreateProjectsRepository::deleteContactor($contact_ids);
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function testGenContactcode(Request $request)
    {
        try {
            $items = CreateProjectsRepository::genCodecontact($request->shop_id);
            return Response::responseJson($items, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function uploadDocuments()
    {
        $args = [];

        try {
            $uploadFile = CreateProjectsRepository::uploadDocuments($args);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function insertQuestion(Request $request)
    {
        $args = [
            'shop_id'              => $request->shop_id,
            'questionnaire_name'   => $request->questionnaire_name,
            'questionnaire_type'   => $request->questionnaire_type,
            'check_solotion'       => $request->check_solotion,
            'region'               => $request->region,
            'csc'                  => $request->csc,
            'eva_active'           => $request->eva_active,
            'created_by'           => $request->created_by,
            'status'               => $request->status,
            'question'             => $request->question,  // Array of objects
            'grade_a_from'         => $request->grade_a_from,
            'grade_a_to'           => $request->grade_a_to,
            'grade_a_to_percent'   => $request->grade_a_to_percent,
            'grade_a_from_percent' => $request->grade_a_from_percent,
            'grade_b_from'         => $request->grade_b_from,
            'grade_b_to'           => $request->grade_b_to,
            'grade_b_to_percent'   => $request->grade_b_to_percent,
            'grade_b_from_percent' => $request->grade_b_from_percent,
            'grade_c_from'         => $request->grade_c_from,
            'grade_c_to'           => $request->grade_c_to,
            'grade_c_to_percent'   => $request->grade_c_to_percent,
            'grade_c_from_percent' => $request->grade_c_from_percent,
            'grade_d_from'         => $request->grade_d_from,
            'grade_d_to'           => $request->grade_d_to,
            'grade_d_to_percent'   => $request->grade_d_to_percent,
            'grade_d_from_percent' => $request->grade_d_from_percent,
            'qualify_from'         => $request->qualify_from,
            'qualify_to'           => $request->qualify_to,
            'fail_from'            => $request->fail_from,
            'fail_to'              => $request->fail_to,
            'questionnaire_id'     => $request->questionnaire_id,
            'grade_type'           => $request->grade_type,
            'chain'                => $request->chain,            // Array of objects
            'question_answer'      => $request->question_answer,  // Array of objects
            'solution'             => $request->solution,         // Array of objects
            'ontime'               => $request->ontime,           // Array of objects
        ];
        try {
            // $uploadFile = CreateProjectsRepository::uploadDocuments($args);
            $items = QuestionRepository::insertQuestion($args);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function updateQuestion(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'question_id' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $args = [
            'id'                   => $request->question_id,
            'shop_id'              => $request->shop_id,
            'questionnaire_name'   => $request->questionnaire_name,
            'questionnaire_type'   => $request->questionnaire_type,
            'check_solotion'       => $request->check_solotion,
            'region'               => $request->region,
            'csc'                  => $request->csc,
            'eva_active'           => $request->eva_active,
            'created_by'           => $request->created_by,
            'status'               => $request->status,
            'question'             => $request->question,  // Array of objects
            'grade_a_from'         => $request->grade_a_from,
            'grade_a_to'           => $request->grade_a_to,
            'grade_a_to_percent'   => $request->grade_a_to_percent,
            'grade_a_from_percent' => $request->grade_a_from_percent,
            'grade_b_from'         => $request->grade_b_from,
            'grade_b_to'           => $request->grade_b_to,
            'grade_b_to_percent'   => $request->grade_b_to_percent,
            'grade_b_from_percent' => $request->grade_b_from_percent,
            'grade_c_from'         => $request->grade_c_from,
            'grade_c_to'           => $request->grade_c_to,
            'grade_c_to_percent'   => $request->grade_c_to_percent,
            'grade_c_from_percent' => $request->grade_c_from_percent,
            'grade_d_from'         => $request->grade_d_from,
            'grade_d_to'           => $request->grade_d_to,
            'grade_d_to_percent'   => $request->grade_d_to_percent,
            'grade_d_from_percent' => $request->grade_d_from_percent,
            'qualify_from'         => $request->qualify_from,
            'qualify_to'           => $request->qualify_to,
            'fail_from'            => $request->fail_from,
            'fail_to'              => $request->fail_to,
            'questionnaire_id'     => $request->questionnaire_id,
            'grade_type'           => $request->grade_type,
            'chain'                => $request->chain,            // Array of objects
            'question_answer'      => $request->question_answer,  // Array of objects
            'solution'             => $request->solution,         // Array of objects
            'ontime'               => $request->ontime,           // Array of objects
        ];
        try {
            $items = QuestionRepository::updateQuestion($args);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function deleteQuestion()
    {
        try {
            // $uploadFile = CreateProjectsRepository::uploadDocuments($args);
            $items = [];
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getQuestionList(Request $request)
    {
        $args = [
            'shop_id'   => $request->shop_id,
            'type'      => $request->type,
            'chain'     => $request->chain,
            'csc'       => $request->csc,
            'status'    => $request->status,
            'search'    => $request->search,
            'page'      => $request->page,
        ];
        try {
            $items = QuestionRepository::getQuestionList($args);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getQuestionSizing(Request $request)
    {
        $items = [
            [
                'label' => "ประเมิน Sizing",
                'value' => 1
            ],
            [
                'label' => "ประเมินผลงาน",
                'value' => 2
            ]
        ];
        try {
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function listQuestionChoice(Request $request)
    {
        $shop_id = $request->shop_id;
        $question_id = $request->question_id;

        try {
            $items = QuestionRepository::listQuestionChoice($shop_id, $question_id);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }


    public function insertSizing(Request $request)
    {
        $args = [
            'shop_id'              => $request->shop_id,
            'questionnaire_name'   => $request->questionnaire_name,
            'questionnaire_type'   => $request->questionnaire_type,
            'check_solotion'       => $request->check_solotion,
            'region'               => $request->region,
            'csc'                  => $request->csc,
            'eva_active'           => $request->eva_active,
            'created_by'           => $request->created_by,
            'status'               => $request->status,
            'question'             => $request->question,  // Array of objects

            'size_l_from'         => $request->size_l_from,
            'size_l_to'           => $request->size_l_to,
            'size_m_from'         => $request->size_m_from,
            'size_m_to'           => $request->size_m_to,
            'size_s_from'         => $request->size_s_from,
            'size_s_to'           => $request->size_s_to,

            'qualify_from'         => $request->qualify_from,
            'qualify_to'           => $request->qualify_to,
            'fail_from'            => $request->fail_from,
            'fail_to'              => $request->fail_to,
            'questionnaire_id'     => $request->questionnaire_id,
            'grade_type'           => $request->grade_type,
            'chain'                => $request->chain,            // Array of objects
            'question_answer'      => $request->question_answer,  // Array of objects
            'solution'             => $request->solution,         // Array of objects
            'ontime'               => $request->ontime,           // Array of objects
        ];
        try {
            $items = SizingRepository::insertSizing($args);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }


    public function updateSizing(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'sizing_id' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $args = [
            'id'                   => $request->sizing_id,
            'shop_id'              => $request->shop_id,
            'questionnaire_name'   => $request->questionnaire_name,
            'questionnaire_type'   => $request->questionnaire_type,
            'check_solotion'       => $request->check_solotion,
            'region'               => $request->region,
            'csc'                  => $request->csc,
            'eva_active'           => $request->eva_active,
            'created_by'           => $request->created_by,
            'status'               => $request->status,
            'question'             => $request->question,  // Array of objects

            'size_l_from'         => $request->size_l_from,
            'size_l_to'           => $request->size_l_to,
            'size_m_from'         => $request->size_m_from,
            'size_m_to'           => $request->size_m_to,
            'size_s_from'         => $request->size_s_from,
            'size_s_to'           => $request->size_s_to,

            'qualify_from'         => $request->qualify_from,
            'qualify_to'           => $request->qualify_to,
            'fail_from'            => $request->fail_from,
            'fail_to'              => $request->fail_to,
            'questionnaire_id'     => $request->questionnaire_id,
            'grade_type'           => $request->grade_type,
            'chain'                => $request->chain,            // Array of objects
            'question_answer'      => $request->question_answer,  // Array of objects
            'solution'             => $request->solution,         // Array of objects
            'ontime'               => $request->ontime,           // Array of objects
        ];
        try {
            $items = SizingRepository::updateSizing($args);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function listSizing(Request $request)
    {
        $args = [
            "shop_id"   => $request->shop_id,
            "vendor_id" => $request->vendor_id,
            "search"    => $request->search
        ];

        try {
            $items = SizingRepository::listSizing($args);
            return Response::responseJson($items, "OK", 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }



    public function listEvaluateSizing(Request $request)
    {
        $shop_id    = $request->shop_id;
        $vendor_id  = $request->vendor_id;
        $search     = $request->search;

        try {
            $item = SizingRepository::listEvaluateSizing($shop_id, $vendor_id, $search);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }


    public function insertEvaluateSizing(Request $request)
    {

        $args = [
            'shop_id'           => $request->shop_id,
            'vendor_id'         => $request->vendor_id,
            'sizing_id'         => $request->sizing_id,
            'choice'            => $request->choice,
            'question_answer'   => $request->question_answer,
            'created_by'        => $request->created_by,
            'ontime'            => $request->ontime,
            'solution'          => $request->solution,
            'portfolio_id'      => $request->portfolio_id,
            'draf'              => $request->draf,
        ];

        try {
            $item = SizingRepository::insertEvaluateSizing($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getDataEvaluation(Request $request)
    {
        $shop_id   = $request->shop_id;
        $vendor_id = $request->vendor_id;

        try {
            $item = SizingRepository::getDataEvaluation($shop_id, $vendor_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function detailEvaQuestionnaire(Request $request)
    {
        $shop_id   = $request->shop_id;
        $vendor_id = $request->vendor_id;
        $questionnaire_id = $request->questionnaire_id;
        $create_at = $request->questionnacreate_atire_id;
        try {
            $item = SizingRepository::detailEvaQuestionnaire($shop_id, $vendor_id, $questionnaire_id, $create_at);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function previewEvaluateSizing(Request $request)
    {

        $args = [
            'shop_id'           => $request->shop_id,
            'vendor_id'         => $request->vendor_id,
            'sizing_id'         => $request->sizing_id,
            'choice'            => $request->choice,
            'question_answer'   => $request->question_answer,
            'created_by'        => $request->created_by,
            'ontime'            => $request->ontime,
            'solution'          => $request->solution,
            'portfolio_id'      => $request->portfolio_id,
        ];

        try {
            $item = SizingRepository::previewEvaluateSizing($args);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }

    public function getVendorPersonal(Request $request)
    {

        $shop_id = $request->shop_id;
        $vendor_id = $request->vendor_id;
        try {
            $item = SizingRepository::getVendorPersonal($shop_id, $vendor_id);
            return Response::responseJson($item, 'OK', 200);
        } catch (Throwable $e) {
            return Response::responseJson($e->getMessage(), "FAILED", 500);
        }
    }
}
