<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
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

class SkillsRepository
{
    public function listUpSkill($shopId, $vendorId)
    {
        try {
            $queryRaw = DB::raw("
                DATE_FORMAT(rue.date_upskill,'%d/%m/%y') AS dateskill , DATE_FORMAT(rue.date_upskill,'%Y-%m-%d') AS dateskillfull,
                DATE_FORMAT(NOW(),'%Y-%m-%d') AS datenow,
                DATE_FORMAT(DATE_ADD(rue.date_upskill, INTERVAL 14 DAY),'%Y-%m-%d') AS dateadds,
                CASE
                    WHEN rue.learning_solution = 0 THEN ''
                    ELSE (SELECT name FROM rudy_learning_solution_eco WHERE id = rue.learning_solution)
                END AS learn_solution
            ");

            $query = DB::table('rudy_upskill_eco as rue')
                ->select('rue.*', $queryRaw)
                ->where('rue.shop_id', $shopId)
                ->where('rue.vendor_id', $vendorId)
                ->get();

            $data_array = [];

            foreach ($query as $val) {
                $data_doc_array = [];
                $upskill_id = $val->id;
                // $sql_doc = "SELECT * FROM rudy_upskill_img_eco WHERE upskill_id = $upskill_id";
                // $data_doc = $db->rows($sql_doc);
                $queryDoc = DB::table('rudy_upskill_img_eco')
                    ->where('upskill_id', $upskill_id)
                    ->get();

                $status = '';
                $status_edit = 0;
                if ($val->status == 1) {
                    $status = "Yes";
                } else {
                    $status = "No";
                }
                if ($val->status_upskill == 1) {
                    $status_skill = "ผ่าน";
                } else if ($val->status_upskill == 2) {
                    $status_skill = "ไม่ผ่าน";
                } else {
                    $status_skill = "";
                }
                if ($val->datenow > $val->dateadds) {
                    $status_edit = 1;
                }
                if ($queryDoc) {
                    foreach ($queryDoc as $val_doc) {
                        $data_doc_array[] = [
                            'img_id'         => $val_doc->id,
                            'file_name'      => $val_doc->file_name,
                            'file_extention' => explode(".", $val_doc->file_name)[1],
                            'link'           => 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/files_solution/' . $val_doc->file_name,
                        ];
                    }
                }


                $data_array[] = [
                    'upskill_id'        => $val->id,
                    'skill_name'        => $val->skill_name,
                    'lecturer_name'     => $val->lecturer_name,
                    'affiliation'       => $val->affiliation,
                    'date_upskill'      => $val->dateskill,
                    'date_upskill_full' => $val->dateskillfull,
                    'status'            => $val->status,
                    'status_txt'        => $status,
                    'status_skill'      =>  $val->status_upskill,
                    'status_skill_txt'  => $status_skill,
                    'status_edit'       => $status_edit,
                    'learning_id'       => $val->learning_solution,
                    'learn_solution'    => $val->learn_solution,
                    'participants'      => $val->participants,
                    'man_hour'          => $val->man_hour,
                    'solution'          => self::solutionUpskill($val->id),
                    'img_upskill'       => $data_doc_array,
                ];
            }
            return $data_array;
            return [];
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function solutionUpskill($upskillId)
    {
        try {

            $query = DB::table('rudy_upskill_solution_eco as ruse')
                ->join('rudy_solution as rs', 'rs.id', 'ruse.solution_id')
                ->select('ruse.*', 'rs.name')
                ->where('ruse.upskill_id', $upskillId)
                ->groupBy('ruse.solution_id')
                ->get();

            $array_so = [];
            foreach ($query as $val) {
                $array_sub = [];
                $so_name   = $val->name;

                $query_r = DB::table('rudy_upskill_solution_eco as ruse')
                    ->select('ruse.*')
                    ->where('ruse.upskill_id', $upskillId)
                    ->where('ruse.solution_id', $val->solution_id)
                    ->get();

                foreach ($query_r as $val_r) {
                    $query_sub = DB::table('rudy_sub_solution')
                        ->select('id', 'name')
                        ->where('id', $val_r->sub_solution_id)
                        ->where('solution_id', $val_r->solution_id)
                        ->get();

                    foreach ($query_sub as $val_sub) {
                        $array_sub[] = [
                            //'id' => $val_sub['id'],
                            'subsolution_id'   => $val_sub->id,
                            'subsolution_name' => $val_sub->name
                        ];
                    }
                }

                $array_so[] = [
                    'solution_id'   => $val->solution_id,
                    'solution_name' => $so_name,
                    'sub_name'      => $array_sub,

                ];
            }

            return $array_so;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
    public function learnSolution()
    {
        try {
            $query = DB::table('rudy_learning_solution_eco')
                ->orderBy('id')
                ->get();

            $resultArray = [];
            foreach ($query as $val) {
                $resultArray[] = [
                    'id'   => $val->id,
                    'name' => $val->name
                ];
            }
            return $resultArray;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function insertUpskill($args)
    {
        try {
            $shop_id    = $args['shop_id'];
            $vendor_id  = $args['vendor_id'];
            $created_by = $args['created_by'];

            if ($vendor_id) {
                $id = generateNextId('rudy_upskill_eco');
                DB::table('rudy_upskill_eco')->insert(
                    [
                        'id'                => $id,
                        'vendor_id'         => $args['vendor_id'],
                        'skill_name'        => $args['skill_name'],
                        'shop_id'           => $shop_id,
                        'lecturer_name'     => $args['lecturer_name'],
                        // 'affiliation'       => $args['affiliation'],
                        'date_upskill'      => $args['date_upskill'],
                        'status'            => $args['status'],
                        'status_upskill'    => $args['status_upskill'],
                        'learning_solution' => $args['learn_solution'],
                        'man_hour'          => $args['man_hour'],
                        'participants'      => $args['participants'],
                        'created_by'        => $args['created_by'],
                        'created_at'        => Date('Y-m-d H:i:s'),
                        'updated_at'        => Date('Y-m-d H:i:s')
                    ]
                );
                // $id = $db->insert('rudy_upskill_eco', );

                if (count($args['solution']) > 0) {
                    foreach ($args['solution'] as $val_so) {
                        DB::table('rudy_upskill_solution_eco')->insert(
                            [
                                'upskill_id'        => $id,
                                'solution_id'       => $val_so['solution_id'],
                                'sub_solution_id'   => $val_so['subso_id'],
                            ]
                        );
                    }
                }

                if (!empty($args['file_name'])) {
                    foreach ($args['file_name'] as $filename) {
                        if ($filename['file_id'] == 0) { //ไว้เช็คค่า ถ้าเป็น 0 insert, 1ไม่ต้องinsert
                            self::UploadFileSolution($filename['file'], $filename['file_name'], $id, 3);
                        }
                    }
                }
            }
            return ['message' => 'Insert successfully'];
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function updateUpskill($args)
    {

        try {
            $upskill_id = $args['upskill_id'];
            $shop_id    = $args['shop_id'];
            $vendor_id  = $args['vendor_id'];
            $created_by = $args['created_by'];

            if ($upskill_id) {

                DB::table('rudy_upskill_eco')
                    ->where('id', $upskill_id)
                    ->update([
                        'skill_name'         => $args['skill_name'],
                        'lecturer_name'      => $args['lecturer_name'],
                        // 'affiliation'        => $args['affiliation'],
                        'date_upskill'       => $args['date_upskill'],
                        'status'             => $args['status'],
                        'status_upskill'     => $args['status_upskill'],
                        'learning_solution'  => $args['learn_solution'],
                        'man_hour'           => $args['man_hour'],
                        'participants'       => $args['participants'],
                        'created_by'         => $args['created_by'],
                        'updated_at'         => date('Y-m-d H:i:s'),
                        'created_at'         => date('Y-m-d H:i:s'),
                        'updated_at'         => date('Y-m-d H:i:s'),
                    ]);

                if (count($args['solution']) > 0) {

                    DB::table('rudy_upskill_solution_eco')
                        ->where('upskill_id', $upskill_id)
                        ->delete();

                    foreach ($args['solution'] as $val_so) {
                        DB::table('rudy_upskill_solution_eco')->insert(
                            [
                                'upskill_id'        => $upskill_id,
                                'solution_id'       => $val_so['solution_id'],
                                'sub_solution_id'   => $val_so['subso_id'],
                            ]
                        );
                    }
                }

                if (!empty($args['file_name'])) {
                    foreach ($args['file_name'] as $file) {
                        if ($file['file_id'] == 0) {
                            self::UploadFileSolution($file['file'], $file['file_name'], $upskill_id, 3);
                        }
                    }
                }
            }
            return ['message' => 'Update upskill successfully'];
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function UploadFileSolution($file, $names, $id, $type)
    {
        $db = new DB;
        $fd = "eco_portal/vendor/files_solution";
        $count_file = 0;

        if ($type == 1) { //file solution
            $query = DB::table('rudy_solution_document_eco')->where('file_name', $names)->get();
            $count_file = count($query);
        }

        if ($count_file == 0) {
            // $nameFile = File::uploadFilesBase64($file, $names, $fd, $specific_name = null, $id);
            // $nameFile = File::uploadFileBase64($file, $fd);
            $nameFile = File::uploadFileName($file, $fd, $names);


            if ($names) {
                if ($type == 1) { //file solution
                    DB::table('rudy_solution_document_eco')->insert([
                        'solution_evaluate_id' => $id,
                        'file_name'            => $names,
                        'created_at'           => Date('Y-m-d H:i:s'),
                        'updated_at'           => Date('Y-m-d H:i:s'),
                    ]);
                }
                if ($type == 2) { //portfolio ผลงาน
                    DB::table('rudy_vendor_portfolio_img_eco')->insert(
                        [
                            'portfolio_id'  => $id,
                            'filename'      => $names,
                            'created_at'    => Date('Y-m-d H:i:s'),
                            'updated_at'    => Date('Y-m-d H:i:s'),
                        ]
                    );
                }
                if ($type == 3) { //upskill
                    DB::table('rudy_upskill_img_eco')->insert([
                        'upskill_id'    => $id,
                        'file_name'     => $nameFile,
                        'name'          => $nameFile,
                        'created_at'    => Date('Y-m-d H:i:s'),
                        'updated_at'    => Date('Y-m-d H:i:s'),
                    ]);
                }

                if ($type == 4) { //banned
                    DB::table('rudy_file_banned_eco')->insert([
                        'banned_id'     => $id,
                        'file_name'     => $names,
                        'created_at'    => Date('Y-m-d H:i:s'),
                        'updated_at'    => Date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return ($names);
    }
}
