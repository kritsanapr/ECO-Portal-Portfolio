<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Throwable;


class QuestionRepository
{

    // Insert quesions : บันทึกประเมินผลงาน
    public function insertQuestion($args)
    {
        $shop_id = $args['shop_id'];

        // D>=,C>=,B>=,A>=
        $a_to    = 0;
        $a_from  = 0;
        $b_to    = 0;
        $b_from  = 0;
        $c_to    = 0;
        $c_from  = 0;
        $d_to    = 0;
        $d_from  = 0;
        $grade_a = $args['grade_a_to']; //20
        $grade_b = $args['grade_b_to']; //15
        $grade_c = $args['grade_c_to']; //10
        $grade_d = $args['grade_d_to']; //5
        $dateNow = date('Y-m-d H:i:s');

        // Insert data in table : rudy_questionnaire_eco
        $question_eco_id = generateNextId('rudy_questionnaire_eco');
        $rudy_questionnaire_eco = [
            'id'                 => $question_eco_id,
            'shop_id'            => $shop_id,
            'questionnaire_name' => $args['questionnaire_name'],
            'questionnaire_type' => $args['questionnaire_type'],
            'created_by'         => $args['created_by'],
            'active'             => 0,
            'status'             => $args['status'],
            'created_at'         => $dateNow,
            'updated_at'         => $dateNow,
            'grade_a_from'       => $args['grade_a_from'],
            'grade_a_to'         => $args['grade_a_to'],
            'grade_b_from'       => $args['grade_b_from'],
            'grade_b_to'         => $args['grade_b_to'],
            'grade_c_from'       => $args['grade_c_from'],
            'grade_c_to'         => $args['grade_c_to'],
            'grade_d_from'       => $args['grade_d_from'],
            'grade_d_to'         => $args['grade_d_to'],
            'qualify_from'       => $args['qualify_from'],
            'qualify_to'         => $args['qualify_to'],
            'fail_from'          => $args['fail_from'],
            'fail_to'            => $args['fail_to'],
            'grade_type'         => $args['grade_type'],
            'ontime'             => $args['ontime'],
        ];

        $excute_question = DB::table('rudy_questionnaire_eco')->insert($rudy_questionnaire_eco);

        // print_r($rudy_questionnaire_eco);
        // print_r($args['question']);

        // Insert data in table : rudy_questionnaire_choice_eco
        // Array of objects. use foreach.
        // Condition and has topic and has settopic.
        foreach ($args['question'] as $key_q => $question) { //array แรก
            if (count($question['header']) > 0) { //มีหัวข้อหลักส่งมา
                // echo ("question header : \n");
                // print_r($args['question'][$key_q]['header']['name']);
                // echo ("\n");

                $header_id = generateNextId('rudy_questionnaire_choice_eco');
                $insert_header = [
                    'id'                => $header_id,
                    'questionnaire_id'  => $question_eco_id,
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
                            'questionnaire_id'  => $question_eco_id,
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

        // Insert data in table : rudy_questionnaire_question_eco
        // Array of objects. use foreach.
        foreach ($args['question_answer'] as $key_a => $question_a) {
            // print_r($args['question'][$key_a]['header']['name']);
            // echo ("\n");
            $insert_answer = [
                'questionnaire_id'  => $question_eco_id,
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

        // Insert data in table : rudy_questionnaire_csc_eco
        // Array of objects. use foreach.
        foreach ($args['chain'] as $key_chain => $chain) { //วนลูปบันทึกตามจำนวน region ที่ส่งมา
            // print_r($chain);
            // echo ("\n");
            $rudy_questionnaire_csc_eco = [
                'questionnaire_id' => $question_eco_id,
                'region'           => $chain['region'],
            ];
            $excute_chain = DB::table('rudy_questionnaire_csc_eco')->insert($rudy_questionnaire_csc_eco);
        }

        // Insert data in table : rudy_questionnaire_solution_eco
        // Array of objects. use foreach.
        foreach ($args['solution'] as $key_solution => $solution) { //วนลูปบันทึกตามจำนวน solution ที่ส่งมา
            // print_r($solution);
            // echo ("\n");
            $rudy_questionnaire_solution_eco = [
                'questionnaire_id'  => $question_eco_id,
                'solution'          => $solution['solution_id'],
                'subsolution'       => $solution['subso_id'],
            ];

            $excute_solution = DB::table('rudy_questionnaire_solution_eco')->insert($rudy_questionnaire_solution_eco);
        }

        return $question_eco_id;
    }

    // Update quesions : แก้ไขประเมินผลงาน
    public function updateQuestion($args)
    {
        // dd($args);

        $question_id = $args['id'];
        $shop_id = $args['shop_id'];

        // D>=,C>=,B>=,A>=
        $a_to    = 0;
        $a_from  = 0;
        $b_to    = 0;
        $b_from  = 0;
        $c_to    = 0;
        $c_from  = 0;
        $d_to    = 0;
        $d_from  = 0;
        $grade_a = $args['grade_a_to']; //20
        $grade_b = $args['grade_b_to']; //15
        $grade_c = $args['grade_c_to']; //10
        $grade_d = $args['grade_d_to']; //5
        $dateNow = date('Y-m-d H:i:s');

        $rudy_questionnaire_eco = [
            'shop_id'            => $shop_id,
            'questionnaire_name' => $args['questionnaire_name'],
            'questionnaire_type' => $args['questionnaire_type'],
            'created_by'         => $args['created_by'],
            'active'             => 0,
            'status'             => $args['status'],
            'updated_at'         => $dateNow,
            'grade_a_from'       => $args['grade_a_from'],
            'grade_a_to'         => $args['grade_a_to'],
            'grade_b_from'       => $args['grade_b_from'],
            'grade_b_to'         => $args['grade_b_to'],
            'grade_c_from'       => $args['grade_c_from'],
            'grade_c_to'         => $args['grade_c_to'],
            'grade_d_from'       => $args['grade_d_from'],
            'grade_d_to'         => $args['grade_d_to'],
            'qualify_from'       => $args['qualify_from'],
            'qualify_to'         => $args['qualify_to'],
            'fail_from'          => $args['fail_from'],
            'fail_to'            => $args['fail_to'],
            'grade_type'         => $args['grade_type'],
            'ontime'             => $args['ontime'],
        ];
        $update_question_eco = DB::table('rudy_questionnaire_eco')->where('id', $question_id)->update($rudy_questionnaire_eco);

        // $sql_q = "SELECT * FROM rudy_questionnaire_eco WHERE active = 0 and id = $questionnaire_id";
        // $q_data = $db->row($sql_q);
        $query_question = DB::table('rudy_questionnaire_eco')
            ->where('active', 0)
            ->where('id', $question_id)
            ->first();

        // echo "Debuging \n";
        // print_r($query_question);
        // echo "\n";

        if ($query_question->eva_answer_id == 1) {
            if (count($args['question']) > 0) {
                foreach ($args['question'] as $key_q => $question) { //array แรก
                    $h_choice_id = $args['question'][$key_q]['header']['id'];
                    $active_old = 0;
                    if ($args['question'][$key_q]['header']['active'] == 0) {
                        $active_old = 1;
                    }
                    if ($h_choice_id > 0) { //แก้ไข
                        $rudy_eco_evaluation = [
                            'question' => $args['question'][$key_q]['header']['name'],
                            'number'   => $args['question'][$key_q]['header']['number'],
                            'score'    => 10,
                            'status'   => $active_old,
                        ];
                        $update_eco_evaluation = DB::table('rudy_eco_evaluation')->where('id', $h_choice_id)->update($rudy_eco_evaluation);

                        // echo "Debuging Update : Header \n";
                        // print_r($rudy_eco_evaluation);
                        // echo "\n";
                    } elseif ($h_choice_id == 0) { //เพิ่มหัวข้อใหม่
                        $rudy_eco_evaluation = [
                            'question'  => $args['question'][$key_q]['header']['name'],
                            'number'    => $args['question'][$key_q]['header']['number'],
                            'score'     => 10,
                        ];

                        $update_eco_evaluation = DB::table('rudy_eco_evaluation')->insert($rudy_eco_evaluation);

                        // echo "Debuging Insert : Header \n";
                        // print_r($rudy_eco_evaluation);
                        // echo "\n";
                    }
                    //if(count($question['choice']) > 0){//มีข้อย่อยภายในข้อหลักส่งมา
                    foreach ($args['question'][$key_q]['choice'] as $key_c => $val_c) { //array ย่อยช้อย
                        $choice_id = $val_c['id'];
                        $active_c = 0;
                        if ($val_c['active'] == 0) {
                            $active_c = 1;
                        }
                        if ($choice_id > 0) {
                            $rudy_eco_evaluation_answer = [
                                'answer'  => $val_c['name'],
                                'number'  => $val_c['number'],
                                'score'   => $val_c['score'],
                                'status'  => $active_c
                            ];
                            $update_evaluation_answer = DB::table('rudy_eco_evaluation_answer')->where('id', $choice_id)->update($rudy_eco_evaluation_answer);
                            // echo "Debuging Update : Answer \n";
                            // print_r($rudy_eco_evaluation_answer);
                            // echo "\n";
                        } elseif ($choice_id == 0) {
                            if ($h_choice_id > 0) {
                                $rudy_eco_evaluation_answer = [
                                    'evaluation_id'  => $h_choice_id,
                                    'answer'         => $val_c['name'],
                                    'number'         => $val_c['number'],
                                    'score'          => $val_c['score'],
                                    'status'         => $active_c,
                                ];
                                $insert_evaluation_answer = DB::table('rudy_eco_evaluation_answer')->insert($rudy_eco_evaluation_answer);

                                // echo "Debuging Insert : Answer \n";
                                // print_r($rudy_eco_evaluation_answer);
                                // echo "\n";
                            }
                        }
                    }
                    //}
                }
            }
        } else {
            if (count($args['question']) > 0) {
                foreach ($args['question'] as $key_q => $question) { //array แรก
                    $h_choice_id = $args['question'][$key_q]['header']['id'];
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
                    } elseif ($h_choice_id == 0) { //เพิ่มหัวข้อใหม่
                        //print($args['question'][$key_q]['header']['active']);
                        $rudy_questionnaire_choice_eco = [
                            'questionnaire_id'  => $question_id,
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
                        } elseif ($choice_id == 0) {
                            if ($h_choice_id > 0) {
                                $rudy_questionnaire_choice_eco = [
                                    'header'  => $val_c['name'],
                                    'number'  => $val_c['number'],
                                    'score'   => $val_c['score'],
                                    'active'  => $val_c['active'],
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
        }



        if (count($args['question_answer']) > 0) {
            foreach ($args['question_answer'] as $key_a => $question_a) {
                $question_id = $question_a['id'];
                if ($question_id > 0) {
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
                    $rudy_questionnaire_question_eco = [
                        'questionnaire_id'  => $question_id,
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
            $delete_question_csc = DB::table('rudy_questionnaire_csc_eco')->where('questionnaire_id', $question_id)->delete();
            foreach ($args['chain'] as $key_chain => $chain) { //วนลูปบันทึกใหม่
                $rudy_questionnaire_csc_eco = [
                    'questionnaire_id' => $question_id,
                    'region'           => $chain['region']
                ];
                $insert_question_csc = DB::table('rudy_questionnaire_csc_eco')->insert($rudy_questionnaire_csc_eco);

                // echo "Debuging Insert : rudy_questionnaire_csc_eco table \n";
                // print_r($rudy_questionnaire_csc_eco);
                // echo "\n";
            }
        }
        if (count($args['solution']) > 0) {
            // $db->delete('rudy_questionnaire_solution_eco', ['questionnaire_id' => $questionnaire_id]);

            $delete_question_solution = DB::table('rudy_questionnaire_solution_eco')->where('questionnaire_id', $question_id)->delete();
            foreach ($args['solution'] as $key_solution => $solution) { //วนลูปบันทึกใหม่
                $rudy_questionnaire_solution_eco = [
                    'questionnaire_id'  => $question_id,
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

        return $question_id;
    }

    // Delete quesiton.
    public function deleteQuestion($args)
    { }

    // Get question list.
    public function getQuestionList($args)
    {
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
    }

    // Function for transform id type to text.
    public function questionnaire_types($q_id)
    {
        $q_name = "";
        if ($q_id == 1) {
            $q_name = "ทัศนคติ";
        } elseif ($q_id == 2) {
            $q_name = "Professional";
        } elseif ($q_id == 3) {
            $q_name = "ประเมินผลงาน";
        }
        return $q_name;
    }

    // Function query question choice detail.
    public function listQuestionChoice($shop_id, $question_id)
    {
        $questionnaire_id = $question_id;
        // $sql_q = "SELECT * FROM rudy_questionnaire_eco WHERE active = 0 and id = $questionnaire_id";
        // $q_data = $db->rows($sql_q);
        $query = DB::table('rudy_questionnaire_eco')
            ->where('active', 0)
            ->where('id', $questionnaire_id)
            ->get();

        $data_array = [];
        $grade_array = [];
        foreach ($query as $val) {
            $q_id = $val->id;
            $q_eva_answer_id = $val->eva_answer_id;
            if ($q_eva_answer_id == 1) { //เป็นแบบประเมินทัศนคติตัวเก่า
                // $sql = "Select * from rudy_eco_evaluation where shop_id = '" . $shop_id . "' and status =1";
                // $data = $db->rows($sql);
                $query_evolution = DB::table('rudy_eco_evaluation')
                    ->where('shop_id', $shop_id)
                    ->where('status', 1)
                    ->get();

                $my_array = [];
                if ($query_evolution) {
                    foreach ($query_evolution as $key => $rs) {
                        $data_header_array[] = [
                            'h_choice_id'     => $rs->id,
                            'h_choice_name'   => $rs->question,
                            'h_choice_number' => $rs->number,
                            'h_choice_score'  => 10,
                            // 'choice'          => $this->getAnswer_($rs->id),
                        ];
                    }
                }
            } else {

                // $sql_h = "SELECT * FROM rudy_questionnaire_choice_eco WHERE questionnaire_id = $q_id and active = 0 and ref_id = 0 ORDER BY NUMBER ASC "; //หัวข้อหลักของข้อย่อย
                // $h_data = $db->rows($sql_h);
                $query_header = DB::table('rudy_questionnaire_choice_eco')
                    ->where('questionnaire_id', $q_id)
                    ->where('ref_id', 0)
                    ->orderBy('NUMBER', 'asc')
                    ->get();

                $data_header_array = [];
                foreach ($query_header as $val_h) {
                    $h_id = $val_h->id;

                    // $sql_c = "SELECT * FROM rudy_questionnaire_choice_eco WHERE
                    // questionnaire_id = $q_id
                    //  and active = 0
                    // and ref_id = $h_id ORDER BY NUMBER ASC "; //หัวข้อหลักของข้อย่อย
                    // $c_data = $db->rows($sql_c);
                    $query_choice = DB::table('rudy_questionnaire_choice_eco')
                        ->where('questionnaire_id', $q_id)
                        ->where('active', 0)
                        ->where('ref_id', $h_id)
                        ->orderBy('NUMBER', 'asc')
                        ->get();

                    $data_choice_array = [];
                    $c_num = 0;
                    foreach ($query_choice as $val_c) {
                        $data_choice_array[] = [
                            'choice_id'     => $val_c->id,
                            'choice_name'   => $val_c->header,
                            'choice_number' => $val_c->number,
                            'choice_score'  => $val_c->score,
                        ];

                        $c_num = $val_c->number;
                    }
                    $c_num = $c_num + 1;
                    // $a = [
                    //     'choice_id'     => "0",
                    //     'choice_name'   => "ไม่เกี่ยวข้อง",
                    //     'choice_number' => "$c_num",
                    //     'choice_score'  => "0",
                    // ];

                    // array_push($data_choice_array, $a);
                    $data_header_array[] = [
                        'h_choice_id'     => $val_h->id,
                        'h_choice_name'   => $val_h->header,
                        'h_choice_number' => $val_h->number,
                        'h_choice_score'  => $val_h->score,
                        'choice'          => $data_choice_array
                    ];
                }
            }
            // $sql_question = "SELECT * FROM rudy_questionnaire_question_eco WHERE
            // questionnaire_id = $q_id and active = 0 order by number asc";
            // $question_data = $db->rows($sql_question);
            $query_question_data = DB::table('rudy_questionnaire_question_eco')
                ->where('questionnaire_id', $q_id)
                ->where('active', 0)
                ->orderBy('number', 'asc')
                ->get();

            $question_array = [];
            foreach ($query_question_data as $val_question) {
                $question_array[] = [
                    'question_id'     => $val_question->id,
                    'question_name'   => $val_question->name,
                    'question_number' => $val_question->number,
                ];
            }

            $grade_type_name = "";
            if ($val->grade_type == 1) { //คำนวณผ่านเกณ
                $grade_type_name = "คำนวณผ่านเกณฑ์";
            } elseif ($val->grade_type == 2) {
                $grade_type_name = "คำนวณตัดเกรด";
            }

            // $sql_csc = "select * from rudy_questionnaire_csc_eco where questionnaire_id = $q_id";
            // $csc_data = $db->rows($sql_csc);
            $csc_data = DB::table('rudy_questionnaire_csc_eco')
                ->where('questionnaire_id', $q_id)
                ->get();

            $r_csc = [];
            $csc = [];

            foreach ($csc_data as $key => $val_csc) {
                $r_csc[$key] = [
                    'csc' => $val_csc->region
                ];
                $csc[$key] = [
                    'region' => $val_csc->region,
                    'csc'    => $val_csc->csc
                ];
            }

            // $sql_solution = "SELECT rqs.id,rqs.solution,rs.name
            //     FROM rudy_questionnaire_solution_eco rqs
            //     JOIN rudy_solution rs ON rs.id = rqs.solution
            //     WHERE rqs.questionnaire_id = $questionnaire_id GROUP BY rqs.solution";
            // $data_solution = $db->rows($sql_solution);
            $query_solution = DB::table('rudy_questionnaire_solution_eco as rqs')
                ->join('rudy_solution as rs', 'rs.id', 'rqs.solution')
                ->select('rqs.id', 'rqs.solution', 'rs.name')
                ->where('rqs.questionnaire_id', $questionnaire_id)
                ->groupBy('rqs.solution')
                ->get();
            $data_solution_array = [];

            if ($query_solution) {
                foreach ($query_solution as $key_so => $rs_so) {
                    $solution_id = $rs_so->solution;
                    $data_subsolution_array = [];
                    // $sql_subsolution = "SELECT rqs.id,rqs.subsolution,rss.name
                    //     FROM rudy_questionnaire_solution_eco rqs
                    //     JOIN rudy_sub_solution rss ON rss.id = rqs.subsolution
                    //     WHERE rqs.questionnaire_id = $questionnaire_id AND rqs.solution = $solution_id ";
                    // $data_subsolution = $db->rows($sql_subsolution);
                    $query_subsolution = DB::table('rudy_questionnaire_solution_eco as rqs')
                        ->join('rudy_sub_solution as rss', 'rss.id', 'rqs.subsolution')
                        ->select('rqs.id', 'rqs.subsolution', 'rss.name')
                        ->where('rqs.questionnaire_id', $questionnaire_id)
                        ->where('rqs.solution', $solution_id)
                        ->get();

                    foreach ($query_subsolution as $key_subso => $rs_subso) {
                        $data_subsolution_array[] = [
                            'subsolution_id'   => $rs_subso->subsolution,
                            'subsolution_name' => $rs_subso->name,
                        ];
                    }

                    $data_solution_array[] = [
                        'solution_id'      => $rs_so->solution,
                        'solution_name'    => $rs_so->name,
                        'module'           => $data_subsolution_array
                    ];
                }
            }

            $create_by = DB::table('rudy_users')
                ->select('id', 'name', 'email')
                ->where('id', $val->created_by)->first();
            $grade_array[] = [
                'grade_type'    => $val->grade_type,
                'qualify_from'  => $val->qualify_from,
                'qualify_to'    => $val->qualify_to,
                'fail_from'     => $val->fail_from,
                'fail_to'       => $val->fail_to,
                // Grad.
                'grade_a_from'  => $val->grade_a_from,
                'grade_a_to'    => $val->grade_a_to,
                'grade_b_from'  => $val->grade_b_from,
                'grade_b_to'    => $val->grade_b_to,
                'grade_c_from'  => $val->grade_c_from,
                'grade_c_to'    => $val->grade_c_to,
                'grade_d_from'  => $val->grade_d_from,
                'grade_d_to'    => $val->grade_d_to,
                // Size.
                'size_l_from'   => $val->size_l_from,
                'size_l_to'     => $val->size_l_to,
                'size_m_from'   => $val->size_m_from,
                'size_m_to'     => $val->size_m_to,
                'size_s_from'   => $val->size_s_from,
                'size_s_to'     => $val->size_s_to,
            ];

            $data_array[] = [
                'id'                    => $val->id,
                'q_name'                => $val->questionnaire_name,
                'questionnaire_type'    => $val->questionnaire_type,
                'q_type_name'           => self::questionnaire_types($val->questionnaire_type),
                'csc'                   => $csc,
                'csc_name'              => $r_csc,
                'grade_type'            => $val->grade_type,
                'grade_type_name'       => $grade_type_name,
                'question'              => $question_array,
                'questionnaire'         => $data_header_array,
                'grade'                 => $grade_array,
                'status'                => $val->status,
                'ontime'                => $val->ontime,
                'solution'              => $data_solution_array,
                'created_by'            => $create_by,
            ];
        }
        //print_r($data_array);
        return $data_array;
    }


    // บันทึกการประเมิน Sizing.
    public function insertEvaluteQuestion($args)
    {
        $shop_id      = $args['shop_id'];
        $vendor_id    = $args['vendor_id'];
        $sizing_id    = $args['sizing_id'];
        $portfolio_id = $args['portfolio_id'];
        $dateNow      = date('Y-m-d H:i:s');

        $data_array   = [];

        // $sql_n = "SELECT eva_number FROM rudy_vendor_project_solution_eco
        //     WHERE vendor_id = $vendor_id AND portfolio_id = $portfolio_id ORDER BY eva_number desc
        //     LIMIT 1";
        // $data_n = $db->row($sql_n);
        $query_ava_num = DB::table('rudy_vendor_project_solution_eco')
            ->select('eva_number')
            ->where('vendor_id', $vendor_id)
            ->where('portfolio_id', $portfolio_id)
            ->orderBy('eva_number', 'desc')
            ->limit(1)
            ->first();

        $eva_number = 0;
        if ($query_ava_num->eva_number > 0) { //ถ้ามี
            $eva_number = $query_ava_num->eva_number + 1;
        } else if ($query_ava_num->eva_number == 0) {
            $eva_number = 1;
        } else {
            $eva_number = 1;
        }

        // $sql = "SELECT * FROM rudy_questionnaire_eco WHERE id  = $sizing_id";
        // $datas = $db->row($sql);
        $query_question = DB::table('rudy_questionnaire_eco')
            ->where('id', $sizing_id)
            ->first();

        $grade_type = $query_question->grade_type;
        $sum_score = 0;
        $count_choice = 0;
        $count_choice_all = 0;

        echo "Start Debuging: session 1------- \n";
        print_r($query_ava_num);
        echo $eva_number, "\n";
        print_r($query_question);
        echo $grade_type, "\n";
        echo "Stap Debuging: session 1------- \n";

        // Looping through all the questions.
        foreach ($args['choice'] as $key => $val) {
            $rudy_evaluate_questionnaire_eco = [
                'vendor_id'         => $vendor_id,
                'shop_id'           => $shop_id,
                'questionnaire_id'  => $args['questionnaire_id'],
                'h_choice_id'       => $val['h_choice_id'],
                'choice_id'         => $val['choice_id'],
                'score'             => $val['choice_score'],
                'created_by'        => $args['created_by'],
                'created_at'        => $this->now,
                'ontime'            => $args['ontime'],
                'eva_number'        => $eva_number,
                'portfolio_id'      => $portfolio_id
            ];
            // $insert_evaluate_question = DB::table('rudy_evaluate_questionnaire_eco')->insert($rudy_evaluate_questionnaire_eco);
            echo "Loggin Choice : \n";
            print_r($val['choice_id']);

            $sum_score += $val['choice_score'];
            if ($val['choice_id'] > 0) {
                $count_choice = $count_choice + 1;
            }
        }

        $grade = $this->grade_sum($grade_type, $sum_score, $query_question->id, $count_choice);
        //print_r($grade);exit();//,$count_choice
        if ($args['question_answer']) {
            foreach ($args['question_answer'] as $val_q) {
                $rudy_evaluate_questionnaire_answer_eco = [
                    'question_id'  => $val_q['id'],
                    'answer'       => $val_q['answer'],
                    'created_at'   => $dateNow,
                    'eva_number'   => $eva_number
                ];

                // $insert_avaluate_answer = DB::table('rudy_evaluate_questionnaire_answer_eco')->insert($rudy_evaluate_questionnaire_answer_eco);
                echo "Start Loggin Insert question answer -----\n";
                print_r($rudy_evaluate_questionnaire_answer_eco);
                echo "Stop Insert question answer -----\n";
            }
        }

        // //print_r($grade."เกรดที่ประเมิน"."<pre>");
        if ($grade != 'ผ่านเกณฑ์' || $grade != 'ไม่ผ่านเกณฑ์') {
            // $sql_v_grade = "SELECT grade_eco FROM rudy_vendor where id = '" . $vendor_id . "'";
            // $data_v_grade = $db->row($sql_v_grade);
            $query_v_grade = DB::table('rudy_vendor')->select('grade_eco')->where('id', $vendor_id)->first();
            $grade_vendor = $query_v_grade->grade_eco; //เกรดเดิม
        }


        if ($args['portfolio_id'] > 0) {
            if (count($args['solution']) > 0) { //มีการส่ง solution ประเมินผ่านฟอร์มแบบไม่มี solution แต่ส่วนใหญ่จะมีเพราะต้องบังคับเลือกมาจากหน้าบ้าน
                foreach ($args['solution'] as $val_so) {
                    // $db->insert('rudy_vendor_project_solution_eco', [
                    //     'vendor_id'    => $vendor_id,
                    //     'shop_id'      => $shop_id,
                    //     'portfolio_id' => $args['portfolio_id'],
                    //     'solution_id'       => $val_so['solution_id'],
                    //     'sub_solution_id'   => $val_so['subso_id'],
                    //     'created_by'        => $args['created_by'],
                    //     'questionnaire_id'  => $args['questionnaire_id'],
                    //     'created_at'   => $this->now,
                    //     'status_eva'   => 1, //ประเมินผ่านฟอร์ม
                    //     'score'       => $sum_score,
                    //     'grade'       => $grade,
                    //     'eva_number' => $n_number,
                    //     'draf'  => $args['draf'],
                    // ]);
                    $rudy_vendor_project_solution_eco = [
                        'vendor_id'         => $vendor_id,
                        'shop_id'           => $shop_id,
                        'portfolio_id'      => $args['portfolio_id'],
                        'solution_id'       => $val_so['solution_id'],
                        'sub_solution_id'   => $val_so['subso_id'],
                        'created_by'        => $args['created_by'],
                        'questionnaire_id'  => $args['questionnaire_id'],
                        'created_at'        => $dateNow,
                        'status_eva'        => 1, //ประเมินผ่านฟอร์ม
                        'score'             => $sum_score,
                        'grade'             => $grade,
                        'eva_number'        => $eva_number,
                        'draf'              => $args['draf'],
                    ];
                    // $insert_vendor_project = DB::table('rudy_vendor_project_solution_eco')->insert($rudy_vendor_project_solution_eco);
                }
            } else {
                // $db->insert('rudy_vendor_project_solution_eco', [
                //     'vendor_id'    => $vendor_id,
                //     'shop_id'      => $shop_id,
                //     'portfolio_id' => $args['portfolio_id'],
                //     'created_by'   => $args['created_by'],
                //     'created_at'   => $this->now,
                //     'questionnaire_id'  => $args['questionnaire_id'],
                //     'status_eva'   => 1, //ประเมินผ่านฟอร์ม
                //     'score'       => $sum_score,
                //     'grade'       => $grade,
                //     'eva_number' => $n_number,
                //     'draf'  => $args['draf'],
                // ]);
                $rudy_vendor_project_solution_eco = [
                    'vendor_id'         => $vendor_id,
                    'shop_id'           => $shop_id,
                    'portfolio_id'      => $args['portfolio_id'],
                    'created_by'        => $args['created_by'],
                    'created_at'        => $dateNow,
                    'questionnaire_id'  => $args['questionnaire_id'],
                    'status_eva'        => 1, //ประเมินผ่านฟอร์ม
                    'score'             => $sum_score,
                    'grade'             => $grade,
                    'eva_number'        => $eva_number,
                    'draf'              => $args['draf'],
                ];
                // $insert_vendor_project = DB::table('rudy_vendor_project_solution_eco')->insert($rudy_vendor_project_solution_eco);
            }
        }

        // $sql_status = "SELECT status,status2 FROM rudy_vendor WHERE id = $vendor_id order by id desc LIMIT 1";
        // $data_status = $db->row($sql_status);
        //สถานะปัจจุบันเป็น inactive ไม่ต้องเปลี่ยนสถานะใหม่
        $query_status = DB::table('rudy_vendor')
            ->select('status', 'status2')
            ->where('id', $vendor_id)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        // $sql = "SELECT * FROM rudy_questionnaire_eco WHERE id  = $sizing_id";
        // $datas = $db->row($sql);
        // $questionnaire_type = $datas['questionnaire_type'];
        $query_question_eco = DB::table('rudy_questionnaire_eco')
            ->where('id', $sizing_id)
            ->first();
        $questionnaire_type = $query_question_eco->questionnaire_type;

        if ($query_status->status == '0') { //จาก register เป็นต้องเปลี่ยนเป็น Verified
            if ($questionnaire_type != 3) { //ทัศนคติ //โปรเฟส
                // $db->update('rudy_vendor', [
                //     'status'            =>  '2', //Verified
                //     'status2'            =>  1, //active
                //     'last_update'       => date('Y-m-d H:i:s'),
                // ], ['id' => $vendor_id]);
                $rudy_vendor = [
                    'status'       =>  '2', //Verified
                    'status2'      =>  1, //active
                    'last_update'  => $dateNow,
                ];
                // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                // $db->insert('rudy_vendor_history_eco', [
                //     'vendor_id'         => $vendor_id,
                //     'user_id'           => $args['created_by'],
                //     'status'            => 2,
                //     'status2'           =>  1, //active
                //     'created_at'        => date('Y-m-d H:i:s'),
                //     'updated_at'        => date('Y-m-d H:i:s'),
                // ]);
                $rudy_vendor_history_eco = [
                    'vendor_id'         => $vendor_id,
                    'user_id'           => $args['created_by'],
                    'status'            => 2,
                    'status2'           => 1, //active
                    'created_at'        => $dateNow,
                    'updated_at'        => $dateNow,
                ];
                // $insert_vendor_history_eco = DB::table('rudy_vendor')->insert($rudy_vendor_history_eco);

            } else { //ประเมิน solution
                if ($args['draf'] == 1) { //save
                    $status_last = $this->status_last($vendor_id);
                    if ($status_last['status2'] == 3 && $status_last['status'] != 4) { //inactive แต่ไม่ใช่แบน
                        // $db->update('rudy_vendor', [
                        //     'status2'            =>  1, //active
                        //     'last_update'       => date('Y-m-d H:i:s'),
                        // ], ['id' => $vendor_id]);
                        $rudy_vendor = [
                            'status2'      =>  1, //active
                            'last_update'  => $dateNow,
                        ];
                        // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                        // $db->update('rudy_vendor_history_eco', [
                        //     'status2'            =>  1, //active
                        //     'updated_at'       => date('Y-m-d H:i:s'),
                        // ], ['id' => $status_last['id']]);
                        $rudy_vendor = [
                            'status2'    =>  1, //active
                            'updated_at' => $dateNow,
                        ];
                        // $update_vendor = DB::table('rudy_vendor_history_eco')->where('id', $status_last['id'])->update($rudy_vendor);
                    } else {
                        // $db->update('rudy_vendor', [
                        //     'status'            =>  '2', //Verified
                        //     'status2'            =>  1, //active
                        //     'last_update'       => date('Y-m-d H:i:s'),
                        // ], ['id' => $vendor_id]);

                        $rudy_vendor = [
                            'status'      => '2', //Verified
                            'status2'     => 1, //active
                            'last_update' => $dateNow,
                        ];
                        // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                    }
                }
            }
        } else { //ถ้าไม่ใช่ 0 คือRegister (2=Verified,4=banned)
            if ($questionnaire_type == 3) { //ประเมิน solution
                $status_active = 0;
                if ($args['draf'] == 1) { //save
                    if ($query_status->status == '4') { //ถูกแบนอยู่ แต่มีการประเมิน solution หรือผลงาน
                        // $db->update('rudy_vendor', [
                        //     'last_update'       => date('Y-m-d H:i:s'),
                        // ], ['id' => $vendor_id]);
                        $rudy_vendor = [
                            'status'      => '2', //Verified
                            'status2'     => 1, //active
                            'last_update' => $dateNow,
                        ];
                        // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
                    } else {
                        $status_last = $this->status_last($vendor_id);
                        if ($status_last['status2'] == 3 && $status_last['status'] != 4) { //inactive แต่ไม่ใช่แบน
                            // $db->update('rudy_vendor', [
                            //     'status2'            =>  1, //active
                            //     'last_update'       => date('Y-m-d H:i:s'),
                            // ], ['id' => $vendor_id]);
                            $rudy_vendor = [
                                'status2'      =>  1, //active
                                'last_update'  => date('Y-m-d H:i:s'),
                            ];
                            // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                            // $db->update('rudy_vendor_history_eco', [
                            //     'status2'            =>  1, //active
                            //     'updated_at'       => date('Y-m-d H:i:s'),
                            // ], ['id' => $status_last['id']]);
                            $rudy_vendor_history_eco = [
                                'status2'      =>  1, //active
                                'last_update'  => date('Y-m-d H:i:s'),
                            ];
                            // $update_vendor_history = DB::table('rudy_vendor_history_eco')->where('id', $status_last['id'])->update($rudy_vendor_history_eco);
                        } else {
                            // $db->update('rudy_vendor', [
                            //     'status'            =>  '2', //Verified
                            //     'status2'            =>  1, //active
                            //     'last_update'       => date('Y-m-d H:i:s'),
                            // ], ['id' => $vendor_id]);
                            $rudy_vendor = [
                                'status'       => '2', //Verified
                                'status2'      => 1, //active
                                'last_update'  => $dateNow,
                            ];
                            // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                            // $sql_active = "SELECT rvh.id,rvh.status,rvh.status2 FROM rudy_vendor_history_eco rvh WHERE rvh.vendor_id = $vendor_id ORDER BY rvh.id DESC LIMIT 1"; //วันล่าสุดที่ถูกแบน
                            // $data_active = $db->row($sql_active);
                            $query_active = DB::table('rudy_vendor_history_eco as rvh')
                                ->select('rvh.id', 'rvh.status', 'rvh.status2')
                                ->where('rvh.vendor_id', $vendor_id)
                                ->orderBy('rvh.id', 'desc')
                                ->limit(1)
                                ->first();

                            if ($query_active->status == '2' && $query_active->status2 != 1) { //Verified แต่ไม่ active
                                // $db->update('rudy_vendor_history_eco', [
                                //     'status2'            =>  1, //active
                                //     'updated_at'       => date('Y-m-d H:i:s'),
                                // ], ['id' => $data_active['id']]);

                                $rudy_vendor_history_eco = [
                                    'status2'     =>  1, //active
                                    'updated_at'  => date('Y-m-d H:i:s'),
                                ];
                                // $update_vendor_history = DB::table('rudy_vendor_history_eco')->where('id', $query_active->id)->update($rudy_vendor_history_eco);
                            }
                        }
                    }
                } else { //ประเมินโปรเฟส หรือทัศนคติ และไม่ใช่ 0 คือRegister
                    if ($query_status->status == '4') { //ติดแบน

                    } else { //เป็นverified+inactive
                        $status_last = $this->status_last($vendor_id);
                        if ($status_last['status2'] == 3 && $status_last['status'] != 4) { //inactive
                            // $db->update('rudy_vendor', [
                            //     'status2'      =>  1, //active
                            //     'last_update'  => date('Y-m-d H:i:s'),
                            // ], ['id' => $vendor_id]);
                            $rudy_vendor = [
                                'status2'      => 1, //active
                                'last_update'  => date('Y-m-d H:i:s'),
                            ];
                            // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);

                            // $db->update('rudy_vendor_history_eco', [
                            //     'status2'    =>  1, //active
                            //     'updated_at' => date('Y-m-d H:i:s'),
                            // ], ['id' => $status_last['id']]);
                            $rudy_vendor_history_eco = [
                                'status2'    => 1, //active
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];
                            // $update_vendor_history = DB::table('rudy_vendor_history_eco')->where('id', $status_last['id'])->update($rudy_vendor_history_eco);
                        }
                    }
                }
            }
        }


        if ($grade != 'ผ่านเกณฑ์' || $grade != 'ไม่ผ่านเกณฑ์') {
            if ($args['draf'] == 1) {
                $grade_last = self::average_grade_project($vendor_id);
                // $db->update('rudy_vendor', [
                //     'grade_eco'    => $grade_last,
                // ], ['id' => $vendor_id]);
                $rudy_vendor = [
                    'grade_eco'    => $grade_last,
                ];
                // $update_vendor = DB::table('rudy_vendor')->where('id', $vendor_id)->update($rudy_vendor);
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
}
