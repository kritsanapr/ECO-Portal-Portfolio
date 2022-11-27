<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Throwable;

class SizingRepository
{

    public function insertSizing($args)
    {
        // dd($args);
        $shop_id = $args['shop_id'];
        $dateNow = date('Y-m-d H:i:s');

        $sizing_id = generateNextId('rudy_questionnaire_eco');
        $rudy_sizing = [
            'id'                 => $sizing_id,
            'shop_id'            => $shop_id,
            'questionnaire_name' => $args['questionnaire_name'],
            'questionnaire_type' => $args['questionnaire_type'],
            'created_by'         => $args['created_by'],
            'active'             => 0,
            'status'             => $args['status'],
            'created_at'         => $dateNow,
            'updated_at'         => $dateNow,

            'size_l_from'       => $args['size_l_from'],
            'size_l_to'         => $args['size_l_to'],
            'size_m_from'       => $args['size_m_from'],
            'size_m_to'         => $args['size_m_to'],
            'size_s_from'       => $args['size_s_from'],
            'size_s_to'         => $args['size_s_to'],

            'qualify_from'       => $args['qualify_from'],
            'qualify_to'         => $args['qualify_to'],
            'fail_from'          => $args['fail_from'],
            'fail_to'            => $args['fail_to'],
            'grade_type'         => $args['grade_type'],
            'ontime'             => $args['ontime'],
        ];
        // print_r($rudy_sizing);
        $excute_question = DB::table('rudy_questionnaire_eco')->insert($rudy_sizing);

        foreach ($args['question'] as $key_q => $question) { //array แรก
            if (count($question['header']) > 0) { //มีหัวข้อหลักส่งมา
                // echo ("question header : \n");
                // print_r($args['question'][$key_q]['header']['name']);
                // echo ("\n");

                $header_id = generateNextId('rudy_questionnaire_choice_eco');
                $insert_header = [
                    'id'                => $header_id,
                    'questionnaire_id'  => $sizing_id,
                    'header'            => $args['question'][$key_q]['header']['name'],
                    'number'            => $args['question'][$key_q]['header']['number'],
                    'score'             => 10,
                    'active'            => 0,
                ];
                $excute_header = DB::table('rudy_questionnaire_choice_eco')->insert($insert_header);

                // echo ("question header : \n");
                // print_r($insert_header);
                // echo ("\n");
            }
            //print_r($args['question'][$key_q]['choice']);exit();
            if (count($question['choice']) > 0) { //มีข้อย่อยภายในข้อหลักส่งมา
                foreach ($args['question'][$key_q]['choice'] as $key_c => $val_c) { //array ย่อยช้อย

                    // echo ("question choice : \n");
                    // print_r($val_c['name']);
                    // echo ("\n ------- \n");
                    // echo ("\n");

                    if ($header_id > 0) {
                        $insert_choice = [
                            'questionnaire_id'  => $sizing_id,
                            'header'            => $val_c['name'],
                            'number'            => $val_c['number'],
                            'ref_id'            => $header_id,
                            'score'             => $val_c['score'],
                            'active'            => 0,
                        ];
                        $excute_choice = DB::table('rudy_questionnaire_choice_eco')->insert($insert_choice);
                    }

                    // echo ("question choice : \n");
                    // print_r($insert_choice);
                    // echo ("\n");
                }
            }
        }


        foreach ($args['question_answer'] as $key_a => $question_a) {
            // print_r($args['question'][$key_a]['header']['name']);
            // echo ("\n");
            $insert_answer = [
                'questionnaire_id'  => $sizing_id,
                'name'              => $question_a['name'],
                'number'            => $question_a['number'],
                'active'            => 0,
                'created_at'        => $dateNow,
            ];
            $excute_answer = DB::table('rudy_questionnaire_question_eco')->insert($insert_answer);

            // echo "------------------ \n";
            // echo ("Answer : " . " \n");
            // print_r($insert_answer);
            // echo ("\n");
        }


        foreach ($args['chain'] as $key_chain => $chain) { //วนลูปบันทึกตามจำนวน region ที่ส่งมา
            // print_r($chain);
            // echo ("\n");
            $rudy_sizing_csc_eco = [
                'questionnaire_id' => $sizing_id,
                'region'           => $chain['region'],
            ];
            $excute_chain = DB::table('rudy_questionnaire_csc_eco')->insert($rudy_sizing_csc_eco);
        }

        foreach ($args['solution'] as $key_solution => $solution) { //วนลูปบันทึกตามจำนวน solution ที่ส่งมา
            // print_r($solution);
            // echo ("\n");
            $rudy_sizing_solution_eco = [
                'questionnaire_id'  => $sizing_id,
                'solution'          => $solution['solution_id'],
                'subsolution'       => $solution['subso_id'],
            ];

            $excute_solution = DB::table('rudy_questionnaire_solution_eco')->insert($rudy_sizing_solution_eco);
        }

        return $sizing_id;
    }

    public function updateSizing($args)
    {
        $sizing_id = $args['id'];
        $shop_id = $args['shop_id'];

        if (empty($sizing_id)) {
            return "Sizing id is missing !.";
        }

        $dateNow = date('Y-m-d H:i:s');

        $rudy_questionnaire_eco = [
            'shop_id'            => $shop_id,
            'questionnaire_name' => $args['questionnaire_name'],
            'questionnaire_type' => $args['questionnaire_type'],
            'created_by'         => $args['created_by'],
            'active'             => 0,
            'status'             => $args['status'],
            'updated_at'         => $dateNow,

            'size_l_from'       => $args['size_l_from'],
            'size_l_to'         => $args['size_l_to'],
            'size_m_from'       => $args['size_m_from'],
            'size_m_to'         => $args['size_m_to'],
            'size_s_from'       => $args['size_s_from'],
            'size_s_to'         => $args['size_s_to'],

            'qualify_from'       => $args['qualify_from'],
            'qualify_to'         => $args['qualify_to'],
            'fail_from'          => $args['fail_from'],
            'fail_to'            => $args['fail_to'],
            'grade_type'         => $args['grade_type'],
            'ontime'             => $args['ontime'],
        ];
        $update_question_eco = DB::table('rudy_questionnaire_eco')->where('id', $sizing_id)->update($rudy_questionnaire_eco);

        // $sql_q = "SELECT * FROM rudy_questionnaire_eco WHERE active = 0 and id = $questionnaire_id";
        // $q_data = $db->row($sql_q);
        $query_question = DB::table('rudy_questionnaire_eco')
            ->where('active', 0)
            ->where('id', $sizing_id)
            ->first();

        // echo "Debuging \n";
        // print_r($query_question);
        // echo "\n";

        // สำหรับโยงกับแบบประเมินทัศคติตัวเก่า มี1ฟอร์ม
        // if ($query_question->eva_answer_id == 1) {
        //     if (count($args['question']) > 0) {
        //         foreach ($args['question'] as $key_q => $question) { //array แรก
        //             $h_choice_id = $args['question'][$key_q]['header']['id'];
        //             $active_old = 0;
        //             if ($args['question'][$key_q]['header']['active'] == 0) {
        //                 $active_old = 1;
        //             }
        //             if ($h_choice_id > 0) { //แก้ไข
        //                 $rudy_eco_evaluation = [
        //                     'question' => $args['question'][$key_q]['header']['name'],
        //                     'number'   => $args['question'][$key_q]['header']['number'],
        //                     'score'    => 10,
        //                     'status'   => $active_old,
        //                 ];
        //                 $update_eco_evaluation = DB::table('rudy_eco_evaluation')->where('id', $h_choice_id)->update($rudy_eco_evaluation);

        //                 // echo "Debuging Update : Header \n";
        //                 // print_r($rudy_eco_evaluation);
        //                 // echo "\n";
        //             } elseif ($h_choice_id == 0) { //เพิ่มหัวข้อใหม่
        //                 $rudy_eco_evaluation = [
        //                     'question'  => $args['question'][$key_q]['header']['name'],
        //                     'number'    => $args['question'][$key_q]['header']['number'],
        //                     'score'     => 10,
        //                 ];

        //                 $update_eco_evaluation = DB::table('rudy_eco_evaluation')->insert($rudy_eco_evaluation);

        //                 // echo "Debuging Insert : Header \n";
        //                 // print_r($rudy_eco_evaluation);
        //                 // echo "\n";
        //             }
        //             //if(count($question['choice']) > 0){//มีข้อย่อยภายในข้อหลักส่งมา
        //             foreach ($args['question'][$key_q]['choice'] as $key_c => $val_c) { //array ย่อยช้อย
        //                 $choice_id = $val_c['id'];
        //                 $active_c = 0;
        //                 if ($val_c['active'] == 0) {
        //                     $active_c = 1;
        //                 }
        //                 if ($choice_id > 0) { //แก้ไข
        //                     $rudy_eco_evaluation_answer = [
        //                         'answer'  => $val_c['name'],
        //                         'number'  => $val_c['number'],
        //                         'score'   => $val_c['score'],
        //                         'status'  => $active_c
        //                     ];
        //                     $update_evaluation_answer = DB::table('rudy_eco_evaluation_answer')->where('id', $choice_id)->update($rudy_eco_evaluation_answer);
        //                     // echo "Debuging Update : Answer \n";
        //                     // print_r($rudy_eco_evaluation_answer);
        //                     // echo "\n";
        //                 } elseif ($choice_id == 0) { // บันทึก
        //                     if ($h_choice_id > 0) {
        //                         $rudy_eco_evaluation_answer = [
        //                             'evaluation_id'  => $h_choice_id,
        //                             'answer'         => $val_c['name'],
        //                             'number'         => $val_c['number'],
        //                             'score'          => $val_c['score'],
        //                             'status'         => $active_c,
        //                         ];
        //                         $insert_evaluation_answer = DB::table('rudy_eco_evaluation_answer')->insert($rudy_eco_evaluation_answer);

        //                         // echo "Debuging Insert : Answer \n";
        //                         // print_r($rudy_eco_evaluation_answer);
        //                         // echo "\n";
        //                     }
        //                 }
        //             }
        //             //}
        //         }
        //     }
        // } else {
        if (count($args['question']) > 0) {
            foreach ($args['question'] as $key_q => $question) { //array แรก
                $h_choice_id = $args['question'][$key_q]['header']['id'];
                // Update header.
                if ($h_choice_id > 0) { //แก้ไข
                    $rudy_questionnaire_choice_eco = [
                        'header' => $args['question'][$key_q]['header']['name'],
                        'number' => $args['question'][$key_q]['header']['number'],
                        'score'  => 10,
                        'active' => $args['question'][$key_q]['header']['active'],
                    ];
                    $update_question_choice = DB::table('rudy_questionnaire_choice_eco')->where('id', $h_choice_id)->update($rudy_questionnaire_choice_eco);

                    // echo "Debuging Update : Choice eco \n";
                    // print_r($rudy_questionnaire_choice_eco);
                    // echo "\n";
                } elseif ($h_choice_id == 0) { //เพิ่มใหม่
                    //print($args['question'][$key_q]['header']['active']);
                    $rudy_questionnaire_choice_eco = [
                        'questionnaire_id'  => $sizing_id,
                        'header'            => $args['question'][$key_q]['header']['name'],
                        'number'            => $args['question'][$key_q]['header']['number'],
                        'score'             => 10,
                    ];

                    $insert_question_choice = DB::table('rudy_questionnaire_choice_eco')->insert($rudy_questionnaire_choice_eco);

                    // echo "Debuging Insert : Choice eco \n";
                    // print_r($rudy_questionnaire_choice_eco);
                    // echo "\n";
                }
                //if(count($question['choice']) > 0){//มีข้อย่อยภายในข้อหลักส่งมา
                foreach ($args['question'][$key_q]['choice'] as $key_c => $val_c) { //array ย่อยช้อย
                    $choice_id = $val_c['id'];
                    if ($choice_id > 0) {
                        // echo "----If----" . "\n";
                        // echo "choice id" . $choice_id . "\n";

                        $rudy_questionnaire_choice_eco = [
                            'header'  => $val_c['name'],
                            'number'  => $val_c['number'],
                            'score'   => $val_c['score'],
                            'active'  => $val_c['active'],
                        ];

                        $update_question_choice = DB::table('rudy_questionnaire_choice_eco')->where('id', $choice_id)->update($rudy_questionnaire_choice_eco);

                        // echo "Debuging Update : Question Choice eco \n";
                        // print_r($rudy_questionnaire_choice_eco);
                        // echo "\n";
                    } elseif ($choice_id == 0) { // เพิ่มใหม่

                        // echo "----Else----" . "\n";
                        // echo "choice id" . $choice_id . "\n";
                        if ($h_choice_id > 0) {
                            $rudy_questionnaire_choice_eco = [
                                'questionnaire_id'  => $sizing_id,
                                'header'            => $val_c['name'],
                                'number'            => $val_c['number'],
                                'ref_id'            => $h_choice_id,
                                'score'             => $val_c['score'],
                                'active'            => $val_c['active'],
                            ];
                            $insert_question_choice = DB::table('rudy_questionnaire_choice_eco')->insert($rudy_questionnaire_choice_eco);
                            // echo "Debuging Insert : Question Choice eco \n";
                            // print_r($rudy_questionnaire_choice_eco);
                            // echo "\n";
                        }
                    }
                }
            }
        }
        // }



        if (count($args['question_answer']) > 0) {
            foreach ($args['question_answer'] as $key_a => $question_a) {
                $question_id = $question_a['id'];
                if ($question_id > 0) {
                    // echo "----If----" . "\n";
                    $rudy_questionnaire_question_eco = [
                        'name'        => $question_a['name'],
                        'number'      => $question_a['number'],
                        'active'      => $question_a['active'],
                        'updated_at'  => $dateNow,
                    ];
                    $update_question_eco = DB::table('rudy_questionnaire_question_eco')->where('id', $question_id)->update($rudy_questionnaire_question_eco);
                    // echo "Debuging Update : rudy_questionnaire_question_eco table \n";
                    // print_r($rudy_questionnaire_question_eco);
                    // echo "\n";
                } elseif ($question_id == 0) {
                    // echo "----Else----" . "\n";
                    $rudy_questionnaire_question_eco = [
                        'questionnaire_id'  => $sizing_id,
                        'name'              => $question_a['name'],
                        'number'            => $question_a['number'],
                        'active'            => $question_a['active'],
                        'created_at'        => $dateNow,
                    ];
                    $insert_question_eco = DB::table('rudy_questionnaire_question_eco')->insert($rudy_questionnaire_question_eco);
                    // echo "Debuging Insert : rudy_questionnaire_question_eco table \n";
                    // print_r($rudy_questionnaire_question_eco);
                    // echo "\n";
                }
            }
        }

        if (count($args['chain']) > 0) {
            $delete_question_csc = DB::table('rudy_questionnaire_csc_eco')->where('questionnaire_id', $sizing_id)->delete();
            foreach ($args['chain'] as $key_chain => $chain) { //วนลูปบันทึกใหม่
                $rudy_questionnaire_csc_eco = [
                    'questionnaire_id' => $sizing_id,
                    'region'           => $chain['region']
                ];
                $insert_question_csc = DB::table('rudy_questionnaire_csc_eco')->insert($rudy_questionnaire_csc_eco);

                // echo "Debuging Insert : rudy_questionnaire_csc_eco table \n";
                // print_r($rudy_questionnaire_csc_eco);
                // echo "\n";
            }
        }
        if (count($args['solution']) > 0) {
            $delete_question_solution = DB::table('rudy_questionnaire_solution_eco')->where('questionnaire_id', $sizing_id)->delete();
            foreach ($args['solution'] as $key_solution => $solution) { //วนลูปบันทึกใหม่
                $rudy_questionnaire_solution_eco = [
                    'questionnaire_id'  => $sizing_id,
                    'solution'          => $solution['solution_id'],
                    'subsolution'       => $solution['subso_id'],
                ];
                $insert_questionnaire_solution_eco = DB::table('rudy_questionnaire_solution_eco')->insert($rudy_questionnaire_solution_eco);

                // echo "Debuging Insert : rudy_questionnaire_solution_eco table \n";
                // print_r($rudy_questionnaire_solution_eco);
                // echo "\n";
            }
        } else {
            $query_question = DB::table('rudy_questionnaire_solution_eco')->where('questionnaire_id', $question_id)->get();
            // echo "------ Qquery------ \n";
            // print_r($query_question);
            // echo "\n";
            if (count($query_question) > 0) {
                DB::table('rudy_questionnaire_solution_eco')->where('questionnaire_id', $question_id)->delete();
            }
        }

        return $sizing_id;
    }

    public function deleteSizing($shop_id, $sizing_id)
    {
        return [];
    }

    public function listSizing($args)
    {
        $shop_id   = $args['shop_id'];
        $vendor_id = $args['vendor_id'];
        $search    = $args['search'];
        $w_search = "";
        // if ($search) {
        //     $w_search = " and rqe.questionnaire_name like '%" . $search . "%'";
        //     $w_search_old = " and questionnaire_name like '%" . $search . "%'";
        // }
        $data_array = [];

        // $sql_vendor = "SELECT cv.id,cv.csc,cv.region
        // FROM rudy_vendor cv
        // WHERE cv.id =  $vendor_id ";
        // $data_vendor = $db->row($sql_vendor);
        $query_vendor = DB::table('rudy_vendor')
            ->select('id', 'csc', 'region')
            ->where('id', $vendor_id)
            ->first();

        // print_r($query_vendor);

        // $sql_evamore = "SELECT rqe.id,rqe.questionnaire_name
        //     FROM rudy_questionnaire_eco rqe
        //     INNER JOIN rudy_questionnaire_csc_eco rqce ON rqce.questionnaire_id = rqe.id
        //     WHERE rqce.region = '" . $data_vendor['region'] . "'
        // AND rqe.eva_answer_id = 0 AND rqe.active = 0 AND rqe.STATUS = 1
        //     and rqe.questionnaire_type = 1 $w_search GROUP BY rqe.id";
        // $data_evamore = $db->rows($sql_evamore);
        $query_evemore = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', 'rqe.id')
            ->select('rqe.id', 'rqe.questionnaire_name')
            ->where('rqce.region', $query_vendor->region)
            ->where('rqe.eva_answer_id', 0)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 1)
            ->where('rqe.STATUS', 1);
        if (!empty($search)) {
            $query_evemore->where('rqe.questionnaire_name', 'like', '%' . $search . '%');
        }
        $query_evemore = $query_evemore->get();
        // print_r($query_evemore);

        // $sql_eva = "SELECT *,(SELECT cus.name FROM rudy_users cus WHERE cus.id = rvee.user_id) AS name_created
        //      FROM rudy_vendor_evaluation_eco rvee WHERE rvee.vendor_id = $vendor_id LIMIT 1";
        // $data_eva = $db->row($sql_eva);

        // $query_eva_raw = DB::raw("(select cus.name from rudy_users cus where cus.id = rvee.user_id) as name_created");
        $query_eva_raw = DB::raw("(SELECT cus.name FROM rudy_users cus WHERE cus.id = rvee.user_id) AS name_created");
        $query_eva = DB::table('rudy_vendor_evaluation_eco as rvee')
            ->select('rvee.*', $query_eva_raw)
            ->where('rvee.vendor_id', $vendor_id)
            ->first();

        // print_r($query_eva);


        if (empty($query_eva)) {
            // $sql_name = "SELECT questionnaire_name FROM rudy_questionnaire_eco WHERE eva_answer_id = 1 AND active = 0 $w_search_old ";
            // $data_name = $db->row($sql_name);
            $query_name = DB::table('rudy_questionnaire_eco')
                ->select('questionnaire_name')
                ->where('eva_answer_id', 1)
                ->where('active', 0);
            if (!empty($search)) {
                $query_name->where('rqe.questionnaire_name', 'like', '%' . $search . '%');
            }
            $query_name = $query_name->first();

            if ($query_name) {
                $a[] = [
                    'id' => 0,
                    'questionnaire_name' => $query_name->questionnaire_name, //"แบบประเมินทัศนคติ Ver.1",
                ];
                $data_array = array_merge($a, $data_array);
            }
        }

        foreach ($query_evemore as  $val) {
            $data_array[] = [
                'id'                 => $val->id,
                'questionnaire_name' => $val->questionnaire_name,
            ];
        }

        return $data_array;
    }

    // List ECO detail table in tab evaluate.
    public static function listEvaluateSizing($shop_id, $vendor_id, $search)
    {
        // rudy_vendor. เก็บข้อมูล vendor.
        // rudy_questionnaire_eco. เก็บมีคะแนน Size
        // rudy_questionnaire_csc_eco. เก็บ CSC ref: region, questionnaire_id
        // rudy_evaluate_questionnaire_eco. เก็บแบบประเมินที่ทำเเล้ว พร้อมคะแนน ref: vendor_id, questionnaire_id, created_by -> computed by score

        $return_data = [];
        // หา Vendor.
        $query_vendor = DB::table('rudy_vendor as rv')
            ->select(
                'rv.id',
                'rv.csc',
                'rv.region',
                'rv.vendor_type'
            )
            ->where('rv.id', $vendor_id)
            ->first();

        // ถ้าเจอ Vendor.
        if ($query_vendor) {
            $query_raw = DB::raw('SUM(reqe.score) AS sum_score');
            $query_avaluate = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                ->select('*', $query_raw)
                ->join('rudy_questionnaire_eco as rqe', 'rqe.id', 'reqe.questionnaire_id')
                ->join('rudy_users as ru', 'ru.id', 'reqe.created_by')
                ->where('vendor_id', $vendor_id)
                ->whereNotNull('size_s_from')
                ->whereNotNull('size_s_to');


            if (!empty($search)) {
                $query_avaluate->where('rqe.questionnaire_name', 'LIKE', '%' . $search . '%');
            }

            $query_avaluate = $query_avaluate
                ->groupBy('rqe.id')
                ->orderBy('reqe.id', 'desc')
                ->orderBy('reqe.created_at', 'desc')
                ->get();

            $size = '';
            $data_array = [];
            foreach ($query_avaluate as $val) {
                if ($query_vendor->vendor_type != 0) {
                    if ($val->sum_score > $val->size_s_from && $val->sum_score < $val->size_s_to) {
                        $size = 'S';
                    } elseif ($val->sum_score > $val->size_m_from && $val->sum_score < $val->size_m_to) {
                        $size = 'M';
                        // } elseif ($val->sum_score > $val->size_l_from && $val->sum_score < $val->size_l_to) {
                    } elseif ($val->sum_score > $val->size_l_from) {
                        $size = 'L';
                    }

                    $data_array[] = [
                        "id"                 => $val->id,
                        "shop_id"            => $val->shop_id,
                        "vendor_id"          => $val->vendor_id,
                        "questionnaire_id"   => $val->questionnaire_id,
                        "h_choice_id"        => $val->h_choice_id,
                        "choice_id"          => $val->choice_id,
                        "score"              => $val->score,
                        "created_by_id"      => $val->created_by,
                        "created_by_name"    => $val->name,
                        "created_at"         => $val->created_at,
                        "ontime"             => $val->ontime,
                        "eva_number"         => $val->eva_number,
                        "portfolio_id"       => $val->portfolio_id,
                        "questionnaire_name" => $val->questionnaire_name,
                        "questionnaire_type" => $val->questionnaire_type,
                        "region"             => $val->region,
                        "csc"                => $val->csc,
                        "active"             => $val->active,
                        "status"             => $val->status,
                        "updated_at"         => $val->updated_at,
                        "sum_score"          => $val->sum_score,
                        "size"               => $size,
                        "size_l_from"        => $val->size_l_from,
                        "size_l_to"          => $val->size_l_to,
                        "size_m_from"        => $val->size_m_from,
                        "size_m_to"          => $val->size_m_to,
                        "size_s_from"        => $val->size_s_from,
                        "size_s_to"          => $val->size_s_to,
                    ];
                }
            }
            return ['list_evaluate' => $data_array, 'total' => count($data_array)];
        }


        //         $return_data = [
        //             "assessment_date"   => '',
        //             "assessment_result" => '',
        //             "assessor"          => '',
        //             "appraisal_history" => '',
        //         ];

        // return $data_size;
        // return $query_vendor;
    }

    /**
     * Function to get eveluate data to show in ECO Detail page on tab การประเมิน ECO.
     * @param int $shop_id.
     * @param int $vendor_id.
     * @param string $search.
     * @return array: typeof array of object $myarray.
     * @uses static function questionnaire_types() -> parameter q_id.
     */
    public static function listEvaluateSizingEx($shop_id, $vendor_id, $search)
    {
        $myarray = [];
        $data_array = [];
        $data_vendor = DB::table('rudy_vendor as cv')
            ->select(
                'cv.id',
                'cv.csc',
                'cv.region',
                DB::raw("case
                            when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
                                    JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = cv.id) >= 70 then 'ผ่านเกณฑ์'
                            when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
                                    JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = cv.id) < 70 then 'ไม่ผ่านเกณฑ์'
                            ELSE 'รอประเมิน'
                        END AS evaluation ")
            )
            ->where('cv.id', $vendor_id)
            ->first();

        if ($data_vendor) {

            $data_eva = DB::table('rudy_vendor_evaluation_eco as rvee')
                ->select(
                    '*',
                    DB::raw("(SELECT cus.name FROM rudy_users cus WHERE cus.id = rvee.user_id) AS name_created")
                )
                ->where('rvee.vendor_id', $vendor_id)
                ->limit(1)
                ->first();

            $id = 0; //ไม่มีการประเมินครั้งแรก แบบเก่า
            if ($data_eva) { //มีค่าประเมินเก่า

                $sql_data_name = DB::table('rudy_questionnaire_eco')
                    ->select('questionnaire_name')
                    ->where('eva_answer_id', 1)
                    ->where('active', 0);
                if (!empty($search)) {
                    $sql_data_name->where('questionnaire_name', 'LIKE', '%' . $search . '%');
                }

                $data_name = $sql_data_name->first();

                if ($data_name) {
                    $year_o = substr($data_eva->updated_at, 0, 4) + 543;
                    if ($data_vendor->evaluation == 'รอประเมิน') {
                        $create_at = "";
                        $update_at = "";
                        $user_created = "";
                    } else {
                        $create_at = date('d/m', strtotime($data_eva->created_at)) . '/' . $year_o . date(' H:i', strtotime($data_eva->created_at));
                        $update_at = date('d/m', strtotime($data_eva->updated_at)) . '/' . $year_o . date(' H:i', strtotime($data_eva->updated_at));
                        $user_created = $data_eva->name_created;
                    }
                    $data_old[] = [
                        'vendor_id'          => $vendor_id,
                        'questionnaire_name' => 'แบบประเมินทัศนคติ Ver.1',
                        'q_type_name'        => 'ทัศนคติ',
                        'csc'                => $data_vendor->region,
                        'status'             => $data_vendor->evaluation,
                        'create_at'          => $create_at,
                        'user_created'       => $user_created,
                        'updated_at'         => $update_at,
                    ];
                }
            }

            $sql_evamore = DB::table('rudy_questionnaire_eco as rqe')
                ->select(
                    '*',
                    'reqe.created_at as create_eva',
                    'rqe.id AS ques_id',
                    DB::raw("(SELECT cus.name FROM rudy_users cus WHERE cus.id = rqe.created_by) AS name_created"),
                    'reqe.created_at'
                )
                ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
                ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
                ->where('rqce.region', $data_vendor->region)
                ->where('rqe.active', 1)
                ->where('rqe.questionnaire_type', 1)
                ->where('reqe.vendor_id', $vendor_id)
                ->where('reqe.choice_id', '!=', $vendor_id);
            if (!empty($search)) {
                $sql_evamore->where('rqe.questionnaire_name', 'LIKE', '%' . $search . '%');
            }

            $data_evamore = $sql_evamore->groupby('reqe.created_at')->get();
            $c_new = count($data_evamore);
            // print_r($data_evamore); exit;

            $data_port = [];
            foreach ($data_evamore as  $val) {
                $status_q = "รอประเมิน";
                if ($val->grade_type == 1) { //1=คำนวณผ่านเกณฑ์

                    $year_e = substr($val->updated_at, 0, 4) + 543;
                    $data_score = DB::table('rudy_evaluate_questionnaire_eco as rqe')
                        ->select(DB::raw("sum(rqe.score) AS evaluation"))
                        ->join('rudy_questionnaire_eco as rqee', 'rqee.id', '=', 'rqe.questionnaire_id')
                        ->where('rqe.vendor_id', $vendor_id)
                        ->where('rqee.grade_type', 1)
                        ->where('rqe.choice_id', '!=', 0)
                        ->where('rqe.created_at', $val->create_eva)
                        ->where('rqe.questionnaire_id', $val->questionnaire_id)
                        ->first();

                    $status_q = "รอประเมิน";
                    if ($data_score->evaluation >= $val->qualify_from) {
                        $status_q = "ผ่านเกณฑ์";
                    } else if ($data_score->evaluation <= $val->fail_to) {
                        $status_q = "ไม่ผ่านเกณฑ์";
                    }
                } else if ($val->grade_type == 2) { //2=คำนวณตัดเกรด

                    $year_e = substr($val->updated_at, 0, 4) + 543;
                    $data_score = DB::table('rudy_evaluate_questionnaire_eco as rqe')
                        ->select(DB::raw("sum(rqe.score) AS evaluation"))
                        ->join('rudy_questionnaire_eco as rqee', 'rqee.id', '=', 'rqe.questionnaire_id')
                        ->where('rqe.vendor_id', $vendor_id)
                        ->where('rqee.grade_type', 2)
                        ->where('rqe.choice_id', '!=', 0)
                        ->where('rqe.created_at', $val->create_eva)
                        ->where('rqe.questionnaire_id', $val->questionnaire_id)
                        ->first();

                    $grade = "รอประเมิน";
                    if ($data_score) {
                        $score = $data_score->evaluation;
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
                    'questionnaire_id'   => $val->questionnaire_id,
                    'questionnaire_name' => $val->questionnaire_name,
                    'q_type_name'        => self::questionnaire_types($val->questionnaire_type),
                    'csc'                => $val->region,
                    'status'             => $status_q,
                    'create_at'          => date('d/m', strtotime($val->created_at)) . '/' . $year_e . date(' H:i', strtotime($val->created_at)),
                    'user_created'       => $val['name_created'],
                    'updated_at'         => date('d/m', strtotime($val->updated_at)) . '/' . $year_e . date(' H:i', strtotime($val->updated_at)),
                    'created'            => $val['created_at'],
                ];
            }
        }

        $data_array = [
            'data_old' => $data_old,
            'data_new'  => $data_port
        ];
        return $data_array;
    }

    // บันทึกการประเมิน Sizing.
    public function insertEvaluateSizing($args)
    {
        $shop_id      = $args['shop_id'];
        $vendor_id    = $args['vendor_id'];
        $sizing_id    = $args['sizing_id'];
        $portfolio_id = $args['portfolio_id'];
        $dateNow      = date('Y-m-d H:i:s');

        $data_array   = [];
        $eva_number = 0;

        if ($portfolio_id != 0) {
            $query_ava_num = DB::table('rudy_vendor_project_solution_eco')
                ->select('eva_number')
                ->where('vendor_id', $vendor_id)
                ->where('portfolio_id', $portfolio_id)
                ->orderBy('eva_number', 'desc')
                ->limit(1)
                ->first();

            if ($query_ava_num->eva_number > 0) { //ถ้ามี
                $eva_number = $query_ava_num->eva_number + 1;
            } else if ($query_ava_num->eva_number == 0) {
                $eva_number = 1;
            } else {
                $eva_number = 1;
            }
        }

        $query_question = DB::table('rudy_questionnaire_eco')
            ->where('id', $sizing_id)
            ->first();

        $grade_type = $query_question->grade_type;
        $sum_score = 0;
        $count_choice = 0;
        $count_choice_all = 0;

        // echo "Start Debuging: session 1------- \n";
        // print_r($query_ava_num);
        // echo "Eva number : " . $eva_number, "\n";
        // print_r($query_question);
        // echo "Grad type : " . $grade_type, "\n";
        // echo "Stap Debuging: session 1------- \n";

        // Looping through all the questions.
        foreach ($args['choice'] as $key => $val) {
            $rudy_evaluate_questionnaire_eco = [
                'vendor_id'         => $vendor_id,
                'shop_id'           => $shop_id,
                'questionnaire_id'  => $args['sizing_id'],
                'h_choice_id'       => $val['h_choice_id'],
                'choice_id'         => $val['choice_id'],
                'score'             => $val['choice_score'],
                'created_by'        => $args['created_by'],
                'created_at'        => $dateNow,
                'ontime'            => $args['ontime'],
                'eva_number'        => $eva_number,
                'portfolio_id'      => $portfolio_id
            ];
            $insert_evaluate_question = DB::table('rudy_evaluate_questionnaire_eco')->insert($rudy_evaluate_questionnaire_eco);
            // echo "Loggin Choice Start : \n";
            // print_r($val['choice_id']);
            // echo "Loggin Choice End : \n";

            $sum_score += $val['choice_score'];
            if ($val['choice_id'] > 0) {
                $count_choice = $count_choice + 1;
            }
        }

        $grade = self::grade_sum($grade_type, $sum_score, $query_question->id, $count_choice);
        //print_r($grade);exit();//,$count_choice
        if ($args['question_answer']) {
            foreach ($args['question_answer'] as $val_q) {
                $rudy_evaluate_questionnaire_answer_eco = [
                    'question_id'  => $val_q['id'],
                    'answer'       => $val_q['answer'],
                    'created_at'   => $dateNow,
                    'eva_number'   => $eva_number
                ];

                $insert_avaluate_answer = DB::table('rudy_evaluate_questionnaire_answer_eco')->insert($rudy_evaluate_questionnaire_answer_eco);
                // echo "Start Loggin Insert question answer -----\n";
                // print_r($rudy_evaluate_questionnaire_answer_eco);
                // echo "Stop Insert question answer -----\n";
            }
        }

        //print_r($grade."เกรดที่ประเมิน"."<pre>");
        if ($grade != 'ผ่านเกณฑ์' || $grade != 'ไม่ผ่านเกณฑ์') {
            $query_v_grade = DB::table('rudy_vendor')->select('grade_eco')->where('id', $vendor_id)->first();
            $grade_vendor = $query_v_grade->grade_eco; //เกรดเดิม
        }

        if ($args['portfolio_id'] > 0) {
            if (count($args['solution']) > 0) { //มีการส่ง solution ประเมินผ่านฟอร์มแบบไม่มี solution แต่ส่วนใหญ่จะมีเพราะต้องบังคับเลือกมาจากหน้าบ้าน
                foreach ($args['solution'] as $val_so) {

                    $rudy_vendor_project_solution_eco = [
                        'vendor_id'         => $vendor_id,
                        'shop_id'           => $shop_id,
                        'portfolio_id'      => $args['portfolio_id'],
                        'solution_id'       => $val_so['solution_id'],
                        'sub_solution_id'   => $val_so['subso_id'],
                        'created_by'        => $args['created_by'],
                        'questionnaire_id'  => $args['sizing_id'],
                        'created_at'        => $dateNow,
                        'status_eva'        => 1, //ประเมินผ่านฟอร์ม
                        'score'             => $sum_score,
                        'grade'             => $grade,
                        'eva_number'        => $eva_number,
                        'draf'              => $args['draf'],
                    ];
                    $insert_vendor_project = DB::table('rudy_vendor_project_solution_eco')->insert($rudy_vendor_project_solution_eco);
                }
            } else {
                $rudy_vendor_project_solution_eco = [
                    'vendor_id'         => $vendor_id,
                    'shop_id'           => $shop_id,
                    'portfolio_id'      => $args['portfolio_id'],
                    'created_by'        => $args['created_by'],
                    'created_at'        => $dateNow,
                    'questionnaire_id'  => $args['sizing_id'],
                    'status_eva'        => 1, //ประเมินผ่านฟอร์ม
                    'score'             => $sum_score,
                    'grade'             => $grade,
                    'eva_number'        => $eva_number,
                    'draf'              => $args['draf'],
                ];
                $insert_vendor_project = DB::table('rudy_vendor_project_solution_eco')->insert($rudy_vendor_project_solution_eco);
            }
        }

        //สถานะปัจจุบันเป็น inactive ไม่ต้องเปลี่ยนสถานะใหม่
        $query_status = DB::table('rudy_vendor')
            ->select('status', 'status2')
            ->where('id', $vendor_id)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        // $questionnaire_type = $datas['questionnaire_type'];
        $query_question_eco = DB::table('rudy_questionnaire_eco')
            ->where('id', $sizing_id)
            ->first();
        $questionnaire_type = $query_question_eco->questionnaire_type;

        if ($query_status->status == '0') { //จาก register เป็นต้องเปลี่ยนเป็น Verified
            if ($questionnaire_type != 3) { //ทัศนคติ //โปรเฟส
                $rudy_vendor = [
                    'status'       => '2', //Verified
                    'status2'      =>  1, //active
                    'last_update'  => $dateNow,
                ];
                $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                $rudy_vendor_history_eco = [
                    'vendor_id'         => $vendor_id,
                    'user_id'           => $args['created_by'],
                    'status'            => 2,
                    'status2'           => 1, //active
                    'created_at'        => $dateNow,
                    'updated_at'        => $dateNow,
                ];
                $insert_vendor_history_eco = DB::table('rudy_vendor')->insert($rudy_vendor_history_eco);
            } else { //ประเมิน solution
                if ($args['draf'] == 1) { //save
                    $status_last = self::status_last($vendor_id);
                    echo "status last : " . $status_last . "\n";
                    if ($status_last['status2'] == 3 && $status_last['status'] != 4) { //inactive แต่ไม่ใช่แบน
                        $rudy_vendor = [
                            'status2'      =>  1, //active
                            'last_update'  => $dateNow,
                        ];
                        $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                        $rudy_vendor_history_eco = [
                            'status2'    =>  1, //active
                            'updated_at' => $dateNow,
                        ];
                        $update_vendor = DB::table('rudy_vendor_history_eco')->where('id', $status_last['id'])->update($rudy_vendor_history_eco);
                    } else {
                        $rudy_vendor = [
                            'status'      => '2', //Verified
                            'status2'     => 1, //active
                            'last_update' => $dateNow,
                        ];
                        $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                    }
                }
            }
        } else { //ถ้าไม่ใช่ 0 คือRegister (2=Verified,4=banned)
            if ($questionnaire_type == 3) { //ประเมิน solution
                $status_active = 0;
                if ($args['draf'] == 1) { //save
                    if ($query_status->status == '4') { //ถูกแบนอยู่ แต่มีการประเมิน solution หรือผลงาน
                        $rudy_vendor = [
                            'last_update' => $dateNow,
                        ];
                        $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                    } else {
                        $status_last = self::status_last($vendor_id);
                        if ($status_last['status2'] == 3 && $status_last['status'] != 4) { //inactive แต่ไม่ใช่แบน
                            $rudy_vendor = [
                                'status2'      =>  1, //active
                                'last_update'  => date('Y-m-d H:i:s'),
                            ];
                            $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                            // echo $vendor_id;
                            $rudy_vendor_history_eco = [
                                'status2'      =>  1, //active
                                'last_update'  => date('Y-m-d H:i:s'),
                            ];
                            // echo ($status_last['id']);
                            $update_vendor_history = DB::table('rudy_vendor_history_eco')->where('id', $status_last['id'])->update($rudy_vendor_history_eco);
                        } else {
                            $rudy_vendor = [
                                'status'       => '2', //Verified
                                'status2'      => 1, //active
                                'last_update'  => $dateNow,
                            ];
                            $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                            // echo "Rudy vendor  updated scope \n";
                            // print_r($rudy_vendor);
                            // echo "Rudy vendor  updated scope \n";

                            $query_active = DB::table('rudy_vendor_history_eco as rvh')
                                ->select('rvh.id', 'rvh.status', 'rvh.status2')
                                ->where('rvh.vendor_id', $vendor_id)
                                ->orderBy('rvh.id', 'desc')
                                ->limit(1)
                                ->first();
                            // echo "Query active start : \n";
                            // print_r($query_active);
                            // echo "Query active end : \n";

                            if ($query_active->status == '2' && $query_active->status2 != 1) { //Verified แต่ไม่ active
                                $rudy_vendor_history_eco = [
                                    'status2'     =>  1, //active
                                    'updated_at'  => date('Y-m-d H:i:s'),
                                ];
                                $update_vendor_history = DB::table('rudy_vendor_history_eco')->where('id', $query_active->id)->update($rudy_vendor_history_eco);
                                // echo "Rudy vendor history updated scope \n";
                                // print_r($rudy_vendor_history_eco);
                                // echo "Rudy vendor history updated scope \n";
                            }
                        }
                    }
                } else { //ประเมินโปรเฟส หรือทัศนคติ และไม่ใช่ 0 คือRegister
                    if ($query_status->status == '4') { //ติดแบน

                    } else { //เป็นverified+inactive
                        $status_last = $this->status_last($vendor_id);
                        if ($status_last['status2'] == 3 && $status_last['status'] != 4) { //inactive
                            $rudy_vendor = [
                                'status2'      => 1, //active
                                'last_update'  => date('Y-m-d H:i:s'),
                            ];
                            $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                            // echo "Rudy vendor updated scope \n";
                            // print_r($rudy_vendor);
                            // echo "Rudy vendor updated scope \n";

                            $rudy_vendor_history_eco = [
                                'status2'    => 1, //active
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];
                            $update_vendor_history = DB::table('rudy_vendor_history_eco')->where('id', $status_last['id'])->update($rudy_vendor_history_eco);
                            // echo "Rudy vendor history updated scope \n";
                            // print_r($rudy_vendor_history_eco);
                            // echo "Rudy vendor history updated scope \n";
                        }
                    }
                }
            }
        }


        if ($grade != 'ผ่านเกณฑ์' || $grade != 'ไม่ผ่านเกณฑ์') {
            if ($args['draf'] == 1) {
                $grade_last = self::average_grade_project($vendor_id);
                $rudy_vendor = [
                    'grade_eco'    => $grade_last,
                ];
                // echo "Grade : " . $grade . "\n";
                $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
            }
        }
        return $vendor_id;
    }

    public function average_grade_project($vendor_id)
    {
        $grade = "";
        $score = 0;

        // $sql_count = "SELECT * FROM rudy_vendor_project_solution_eco where vendor_id = $vendor_id AND grade not in('ไม่ผ่านเกณฑ์','ผ่านเกณฑ์') and draf = 1
        //  GROUP BY created_at ";
        // $re_count = $db->rows($sql_count);
        // $count_n = count($re_count);
        $query_count = DB::table('rudy_vendor_project_solution_eco')
            ->where('vendor_id', $vendor_id)
            ->whereNotIN('grade', ['ไม่ผ่านเกณฑ์', 'ผ่านเกณฑ์'])
            ->where('draf', 1)
            ->groupBy('created_at')
            ->get();
        $re_count = $query_count->count();

        $sum_aver = 0;
        $aa = 0;
        foreach ($query_count as $val) {
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
        if ($re_count != 0) {
            $score = ($sum_aver / $re_count);
            if (round($score) > 0 && round($score) <= 1) {
                $grade = 4; //D
            } else if (round($score) > 1 && round($score) <= 2) {
                $grade = 3; //C
            } else if (round($score) > 2 && round($score) <= 3) {
                $grade = 2; //B
            } else if (round($score) >= 4) { //A
                $grade = 1;
            }
        }
        return ($grade);
    }


    public function status_last($vendor_id)
    {
        $query_last = DB::table('rudy_vendor_history_eco')->where('vendor_id', $vendor_id)->orderBy('id', 'desc')->limit(1)->first();
        $befor_status = $query_last->status2;

        $ret = [
            'id'      => $query_last->id,
            'status'  => $query_last->status,
            'status2' => $query_last->status2,
        ];

        return $ret;
    }

    // questionnaire.
    public function grade_sum($grade_type, $sum_score, $id, $count_choice)
    {

        // $sql = "SELECT * FROM rudy_questionnaire_eco WHERE id  = $id";
        // $data = $db->row($sql);
        $query = DB::table('rudy_questionnaire_eco')->where('id', $id)->first();
        $grade = "";

        if ($grade_type == 1) { //1=คำนวณผ่านเกณฑ์,
            if ($sum_score >= $query->qualify_from) {
                $grade = "ผ่านเกณฑ์";
            } else if ($sum_score <= $query->fail_to) {
                $grade = "ไม่ผ่านเกณฑ์";
            }
        } else if ($grade_type == 2) { //2=คำนวณตัดเกรด
            //ร้อยละของคะแนนตั้งต้น
            $score_all = $count_choice * 10;
            $score_criteria = ($sum_score / $score_all) * 100;

            // $sql_c = "SELECT * FROM rudy_questionnaire_choice_eco WHERE questionnaire_id  = $id and ref_id = 0 ";
            // $data_c = $db->rows($sql_c);
            $query_c = DB::table('rudy_questionnaire_choice_eco')
                ->where('questionnaire_id', $id)
                ->where('ref_id', 0)
                ->get();

            $count_choice_all = count($query_c);
            $count_all = $count_choice_all * 10;
            $cri_d_to = ($query->grade_d_to / $count_all) * 100;
            $cri_d_from =  0;
            $cri_c_to = ($query->grade_c_to / $count_all) * 100;
            $cri_c_from = ($query->grade_c_from / $count_all) * 100;
            $cri_b_to = ($query->grade_b_to / $count_all) * 100;
            $cri_b_from = ($query->grade_b_from / $count_all) * 100;
            $cri_a_to =  $query->grade_a_to;
            $cri_a_from = ($query->grade_a_from / $count_all) * 100;

            if ($score_criteria <= $cri_d_to) {
                $grade = "D";
            } else if ($score_criteria <= $cri_c_to) {
                $grade = "C";
            } else if ($score_criteria <= $cri_b_to) {
                $grade = "B";
            } else if ($score_criteria >= $cri_a_from) {
                $grade = "A";
            }
        }
        return $grade;
    }

    public function getDataEvaluation($shop_id, $vendor_id)
    {
        // $sql = "select rvee.id,rvee.group,rvee.question as question_id,rev.question as question_name,rvee.answer as answer_id,reen.answer as answer_name,reen.score,rvee.updated_at
        // from rudy_vendor_evaluation_eco as rvee
        //     JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer
        //     JOIN rudy_eco_evaluation as rev ON rev.id = rvee.question
        //     where rvee.shop_id = '" . $params['shop_id'] . "' and rvee.vendor_id = '" . $params['vendor_id'] . "'";
        // $data = $db->rows($sql);
        $query = DB::table('rudy_vendor_evaluation_eco as rvee')
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
            ->join('rudy_eco_evaluation_answer as reen', 'reen.id', 'rvee.answer')
            ->join('rudy_eco_evaluation as rev', 'rev.id', 'rvee.question')
            ->where('rvee.shop_id', $shop_id)
            ->where('rvee.vendor_id', $vendor_id)
            ->get();

        return ['data' => $query, 'total' => count($query)];
    }

    public function detailEvaQuestionnaire($shop_id, $vendor_id, $questionnaire_id, $create_at)
    {
        $data_array = [];

        //หาชื่อแบบประเมิน
        // $sql_h = "SELECT questionnaire_name
        //         FROM rudy_questionnaire_eco WHERE id = $questionnaire_id ";
        // $data_h = $db->row($sql_h);
        // $header_name = $data_h['questionnaire_name'];

        // $query_header = DB::table('rudy_questionnaire_eco')
        //     ->select('questionnaire_name')
        //     ->where('id', $questionnaire_id)
        //     ->first()
        //     ->questionnaire_name;

        //หัวข้อหลักทั้งหมดที่ประเมิน
        // $sql_q = "SELECT reqe.h_choice_id,reqe.choice_id,(SELECT header from rudy_questionnaire_choice_eco WHERE id = reqe.h_choice_id) AS h_name
        // FROM rudy_evaluate_questionnaire_eco reqe WHERE reqe.vendor_id = $vendor_id
        // AND questionnaire_id = $questionnaire_id AND reqe.created_at = '" . $created_at . "' GROUP BY reqe.h_choice_id ";
        // $data_q = $db->rows($sql_q);
        $query_question_raw = DB::raw("(SELECT header from rudy_questionnaire_choice_eco WHERE id = reqe.h_choice_id) AS h_name");
        $query_question = DB::table('rudy_evaluate_questionnaire_eco as reqe')
            ->select('reqe.h_choice_id', 'reqe.choice_id', $query_question_raw)
            ->where('reqe.vendor_id', $vendor_id)
            ->where('questionnaire_id', $questionnaire_id)
            // ->where('reqe.created_at', $create_at)
            ->groupBy('reqe.h_choice_id')
            ->get();

        $array_header = [];
        foreach ($query_question as $val) {
            // $c_name = "";
            // if ($val->choice_id == 0) {
            //     $c_name = "ไม่เกี่ยวข้อง";
            // }

            $query_choice = DB::table('rudy_questionnaire_choice_eco as rqce')
                ->select('rqce.header as headers')
                ->where('rqce.id', $val->choice_id)
                ->get();

            $array_choice = [];

            if ($query_choice) {
                foreach ($query_choice as $val_choice) { //ลูปหาข้อย่อย
                    $choice_name = $val_choice->headers;
                    $array_choice[] = [
                        'choice_name' => $choice_name
                    ];
                }
            } else {
                $array_choice[] = [
                    'choice_name' => $choice_name
                ];
            }

            $array_header[] = [
                'h_choice_name' => $val->h_name,
                'choice'        => $array_choice
            ];
        }

        // $sql_answer = "SELECT rqqe.name,rqae.answer
        //     FROM rudy_questionnaire_question_eco rqqe
        //     left JOIN rudy_evaluate_questionnaire_eco reqe ON reqe.questionnaire_id = rqqe.questionnaire_id
        //     left JOIN rudy_evaluate_questionnaire_answer_eco rqae ON rqae.question_id = rqqe.id
        //     WHERE rqqe.questionnaire_id = $questionnaire_id and rqqe.active = 0 AND reqe.vendor_id = $vendor_id
        //     GROUP BY rqqe.id order by rqqe.number asc
        //     ";
        // $answer_data = $db->rows($sql_answer);
        $query_answer = DB::table('rudy_questionnaire_question_eco as rqqe')
            ->select('rqqe.name', 'rqae.answer')
            ->leftJoin('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', 'rqqe.questionnaire_id')
            ->leftJoin('rudy_evaluate_questionnaire_answer_eco as rqae', 'rqae.question_id', 'rqqe.id')
            ->where('rqqe.questionnaire_id', $questionnaire_id)
            ->where('rqqe.active', 0)
            ->where('reqe.vendor_id', $vendor_id)
            ->groupBy('rqqe.id')
            ->orderBy('rqqe.number', 'asc')
            ->get();

        $answer_array = [];
        foreach ($query_answer as $val_answer) {
            $answer_array[] = [
                'question_name' => $val_answer->name,
                'answer_name'   => $val_answer->answer,
            ];
        }
        $return_data = [
            'answer'  => $answer_array,
            'list'    => $array_header,
        ];
        return $return_data;
    }


    public function previewEvaluateSizing($args)
    {
        // { PAYLOAD_DETAILS
        //     "shop_id": 228,
        //     "vendor_id": 1329,
        //     "sizing_id": 104,
        //     "choice": [
        //         {
        //             "h_choice_id": 1573,
        //             "choice_id": 1574,
        //             "choice_score": 6
        //         },
        //         {
        //             "h_choice_id": 1577,
        //             "choice_id": 1578,
        //             "choice_score": 6
        //         },
        //         {
        //             "h_choice_id": 1581,
        //             "choice_id": 1582,
        //             "choice_score": 6
        //         }
        //     ],
        //     "created_by": 8208,
        //     "ontime": "",
        //     "portfolio_id": 3111
        // }


        $shop_id = $args['shop_id'];
        $vendor_id = $args['vendor_id'];
        $sizng_id = $args['sizing_id'];

        $sum_score = 0;
        $data_array = [];
        $array_header = [];
        $choice_n = 0;
        foreach ($args['choice'] as $key => $val) {
            // $sql_q = "SELECT rqce.header,id,number
            // FROM rudy_questionnaire_choice_eco rqce WHERE rqce.id = '" . $val['h_choice_id'] . "' ";
            // $data_q = $db->row($sql_q);
            $query_quesiton = DB::table('rudy_questionnaire_choice_eco as rqce')
                ->select('rqce.header', 'id', 'number')
                ->where('rqce.id', $val['h_choice_id'])
                ->first();

            $array_choice = [];
            $choice_name = '';
            // $sql_c = "SELECT rqce.header, rqce.score
            //         FROM rudy_questionnaire_choice_eco rqce
            //         WHERE rqce.id = '" . $val['choice_id'] . "' ";
            // $data_c = $db->row($sql_c);
            $query_choice = DB::table('rudy_questionnaire_choice_eco as rqce')
                ->select('rqce.header', 'rqce.score')
                ->where('rqce.id', $val['choice_id'])
                ->first();

            $choice_name = $query_choice->header;
            $array_choice[] = [
                'choice_name' => $choice_name
            ];

            $array_header[] = [
                'h_choice_id'      => $query_quesiton->id,
                'h_choice_number'  => $query_quesiton->number,
                'h_choice_name'    => $query_quesiton->header,
                'choice'           => $array_choice
            ];

            $sum_score += $query_choice->score;
        }

        // return [];
        $answer_array = [];
        if (!empty($args['question_answer'])) {
            foreach ($args['question_answer'] as $key => $val) {
                // $sql_q = "SELECT rqqe.name
                // FROM rudy_questionnaire_question_eco rqqe WHERE rqqe.id = '" . $val['id'] . "' ";
                // $data_q = $db->row($sql_q);
                $query_question_answer = DB::table('rudy_questionnaire_question_eco as rqqe')
                    ->select('rqqe.name')
                    ->where('rqqe.id', $val['id'])
                    ->first();

                $answer_array[] = [
                    'question_name' => $query_question_answer->name,
                    'answer_name'   => $val['answer'],
                ];
            }
        }

        $data_so = [];
        $rs_solution = [];
        if (!empty($args['solution'])) {
            foreach ($args['solution'] as $key => $value) {
                $rs_solution[] = $value['solution_id'];
            }
            $a = (array_values(array_unique($rs_solution)));
            $so_name = "";
            foreach ($a as $id) {
                // $sql1 = "SELECT name as solution_name FROM rudy_solution WHERE id = '" . $va . "'"; //
                // $data1 = $db->row($sql1);
                $query_solution = DB::table('rudy_solution')
                    ->select('name as solution_name')
                    ->where('id', $id)
                    ->first();
                $so_name = $query_solution->solution_name;

                $data_sub = [];
                foreach ($args['solution'] as $k => $val_solution) {
                    // $sql2 = "SELECT name as subsolution_name FROM rudy_sub_solution WHERE id = '" . $val_so['subso_id'] . "'"; //
                    // $data2 = $db->row($sql2);
                    $query_subsolution = DB::table('rudy_sub_solution')
                        ->select('name as subsolution_name')
                        ->where('id', $val_solution['subso_id'])
                        ->first();

                    if ($id == $val_solution['solution_id']) {
                        $data_sub[] = [
                            'subsolution_name' => $query_subsolution->subsolution_name,
                        ];
                    }
                }
                $data_so[] = [
                    'solution_name' => $so_name,
                    'module' => $data_sub
                ];
            }
        }

        // $sql = "SELECT * FROM rudy_questionnaire_eco WHERE id  = $questionnaire_id";
        // $data = $db->row($sql);
        $query_question = DB::table('rudy_questionnaire_eco')->where('id', $sizng_id)->first();
        $size = "";
        if (!$query_question) return 'Sizing id is missing';
        if ($sum_score > $query_question->size_s_from &&  $sum_score < $query_question->size_s_to) {
            $size = 'S';
        } elseif ($sum_score > $query_question->size_m_from &&  $sum_score < $query_question->size_m_to) {
            $size = 'M';
            // } elseif ($sum_score > $query_question->size_l_from &&  $sum_score < $query_question->size_l_to) {
        } elseif ($sum_score > $query_question->size_l_from) {
            $size = 'L';
        }

        $return_data = [
            'questionnaire_name' => $query_question->questionnaire_name,
            'grade'              => $size,
            'score'              => $sum_score,
            'count_score'        => ((count($array_header) - $choice_n) * 10),
            'solution'           => $data_so,
            'answer'             => $answer_array,
            'list'               => $array_header,
            'created_at'         => date('Y-m-d H:i:s')
        ];

        return $return_data;
    }

    public function getVendorPersonal($shop_id, $vendor_id)
    {
        $vendor = DB::table('rudy_vendor')
            ->select('id', 'name', 'nickname', 'email', 'phone', 'vendor_code', 'pic', 'added_date', 'last_update', 'admin_eco')
            ->where('shop_id', $shop_id)
            ->where('id', $vendor_id)

            ->first();

        if (empty($vendor)) return 'Vendor not found';

        return $vendor;
    }
}
