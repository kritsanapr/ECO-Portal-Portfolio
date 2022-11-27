<?php

namespace App\Repositories;

use App\Services\FileServices as File;
use Illuminate\Support\Facades\DB;

use Throwable;

class SubECODetailRepository
{
    /**
     * Function to retrieve solution data.
     * @param string $sub_solution_id.
     * @uses Table rudy_sub_solution_data, rudy_solution.
     * @return array $data.
     */
    public static function get_solution_data($sub_solution_id)
    {
        $data = DB::table('rudy_sub_solution as rss')
            ->join('rudy_solution as rs', 'rs.id', 'rss.solution_id')
            ->select('rs.id as solution_id', 'rs.name as solution_name', 'rss.name as sub_solution_name')
            ->where('rss.id', $sub_solution_id)
            ->get();

        return $data;
    }

    /**
     * Function to get eveluate data to show in ECO Detail page on the 3 card.
     * @param int $shop_id.
     * @param int $vendor_id.
     * @return array: typeof array of object $data_array.
     */
    public static function get_evaluate_dataEx($shop_id, $vendor_id)
    {
        $data_array = [];
        $vendor_id = $vendor_id;

        $data_vendor  = DB::table('rudy_vendor as cv')
            ->where('cv.id', $vendor_id)
            ->first();

        /* ประเมินทัศนคติ */
        $data_evamore = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
            ->where('rqce.region', $data_vendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 1)
            ->where('reqe.vendor_id', $vendor_id)
            ->orderby('reqe.created_at', 'desc')
            ->limit(1)
            ->get();

        $data_c = DB::table('rudy_questionnaire_eco as rqe')
            // ->select('rqe.id')
            ->select('*')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
            ->where('rqce.region', $data_vendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.STATUS', 1)
            ->where('rqe.questionnaire_type', 1)
            ->where('reqe.vendor_id', $vendor_id)
            ->groupby('reqe.created_at')
            ->get();
        return $data_c;
        $c_new = count($data_c);
        $status_evamore = "";

        foreach ($data_evamore as $val_evamore) {

            if ($val_evamore->grade_type == 1) {
                $data_score = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS evaluation"))
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', '=', 'reqe.questionnaire_id')
                            ->where('rqe.questionnaire_type', '=', 1)
                            ->where('rqe.grade_type', '=', 1)
                            ->where('reqe.choice_id', '!=', 0);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('rqe.id', $val_evamore->questionnaire_id)
                    ->groupby('reqe.questionnaire_id')
                    ->orderby('evaluation', 'desc')
                    ->limit(1)
                    ->first();
                if ($data_score) {
                    if ($data_score->evaluation >= $val_evamore->qualify_from && $data_score->evaluation <= $val_evamore->qualify_to) {
                        $status_evamore = "ผ่านเกณฑ์";
                    } else if ($data_score->evaluation >= $val_evamore->fail_from && $data_score->evaluation <= $val_evamore->fail_to) {
                        $status_evamore = "ไม่ผ่านเกณฑ์";
                    }
                }
            } else if ($val_evamore->grade_type == 2) {
                $data_score = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS evaluation"))
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', '=', 'reqe.questionnaire_id')
                            ->where('rqe.questionnaire_type', '=', 1)
                            ->where('rqe.grade_type', '=', 2)
                            ->where('reqe.choice_id', '!=', 0);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('rqe.id', $val_evamore->questionnaire_id)
                    ->groupby('reqe.questionnaire_id')
                    ->orderby('evaluation', 'desc')
                    ->limit(1)
                    ->first();

                $score = 0;
                if ($data_score) {
                    $score = $data_score->evaluation;

                    if ($score <= $val_evamore->grade_d_to) {
                        $status_evamore = "D";
                    } else if ($score >= $val_evamore->grade_c_from && $score <= $val_evamore->grade_c_to) {
                        $status_evamore = "C";
                    } else if ($score >= $val_evamore->grade_b_from && $score <= $val_evamore->grade_b_to) {
                        $status_evamore = "B";
                    } else if ($score >= $val_evamore->grade_a_from) {
                        $status_evamore = "A";
                    }
                }
            }
        }

        $data_eva_c = DB::table('rudy_vendor_evaluation_eco as rvee')
            ->where('rvee.vendor_id', $vendor_id)
            ->limit(1)
            ->get();
        $c_eva = count($data_eva_c);

        $data_eva = DB::table('rudy_vendor_evaluation_eco as rvee')
            ->select(DB::raw(
                "case
                    when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
                            JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = $vendor_id) >= 70 then 'ผ่านเกณฑ์'
                    when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
                            JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = $vendor_id) < 70 then 'ไม่ผ่านเกณฑ์'
                    ELSE 'รอประเมิน'
                    END AS evaluation"
            ))
            ->where('rvee.vendor_id', $vendor_id)
            ->first();

        $status_eva = "รอประเมิน";
        if ($c_new > 0) {
            $status_eva = $status_evamore;
        } else {
            $status_eva = $data_eva->evaluation;
        }

        $data_pro = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
            ->where('rqce.region', $data_vendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 2)
            ->where('reqe.vendor_id', $vendor_id)
            ->orderby('reqe.created_at', 'desc')
            ->limit(1)
            ->get();

        $data_cp = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
            ->where('rqce.region', $data_vendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 2)
            ->where('reqe.vendor_id', $vendor_id)
            ->groupby('reqe.created_at')
            ->get();
        $c_pro = count($data_cp);

        $status_pro = "รอประเมิน";
        $grade = "";
        $status_end = "";

        foreach ($data_pro as $val_pro) { //วนหาแยกตามชนิดการตัดเกรด กรณีแบบฟอร์มเปลี่ยนทีหลัง

            if ($val_pro->grade_type == 1) { //1=คำนวณผ่านเกณฑ์,
                $data_score_pro = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS evaluation"))
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', '=', 'reqe.questionnaire_id')
                            ->where('rqe.questionnaire_type', '=', 2)
                            ->where('rqe.grade_type', '=', 1)
                            ->where('reqe.choice_id', '!=', 0);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('rqe.id', $val_pro->questionnaire_id)
                    ->first();
                if ($data_score_pro) {
                    if ($data_score_pro->evaluation >= $val_pro->qualify_from) {
                        $status_pro = "ผ่านเกณฑ์";
                    } else if ($data_score_pro->evaluation <= $val_pro->fail_to) {
                        $status_pro = "ไม่ผ่านเกณฑ์";
                    }
                }
            } else if ($val_pro->grade_type == 2) { //2=คำนวณตัดเกรด
                $data_sum_pro = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS sum_score"))
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', '=', 'reqe.questionnaire_id')
                            ->where('rqe.questionnaire_type', '=', 2)
                            ->where('rqe.grade_type', '=', 2)
                            ->where('reqe.choice_id', '!=', 0);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('rqe.id', $val_pro->questionnaire_id)
                    ->first();
                $score = 0;
                if ($data_sum_pro) {
                    $score = $data_sum_pro->sum_score;

                    if ($score <= $val_pro->grade_d_to) {
                        $status_pro = "D";
                    } else if ($score >= $val_pro->grade_c_from && $score <= $val_pro->grade_c_to) {
                        $status_pro = "C";
                    } else if ($score >= $val_pro->grade_b_from && $score <= $val_pro->grade_b_to) {
                        $status_pro = "B";
                    } else if ($score >= $val_pro->grade_a_from) {
                        $status_pro = "A";
                    }
                }
            }
        }

        $data_sol_grade = DB::table('rudy_vendor as rv')
            ->select(DB::raw("case
                                when rv.grade_eco = 1 then 'A'
                                when rv.grade_eco = 2 then 'B'
                                when rv.grade_eco = 3 then 'C'
                                when rv.grade_eco = 4 then 'D'
                                ELSE null
                            END AS grade "))
            ->where('rv.id', $vendor_id)
            ->first();

        $data_sol = DB::table('rudy_vendor_portfolio_eco as rvpe')
            ->select('rvpe.id')
            ->join('rudy_vendor_project_solution_eco as rvpse', 'rvpse.portfolio_id', '=', 'rvpe.id')
            ->where('rvpe.vendor_id', $vendor_id)
            ->where('rvpse.draf', 1)
            ->where('rvpse.status', 1)
            ->groupby('rvpe.id')
            ->get();
        $c_solution = count($data_sol);

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
            'status' => $data_sol_grade->grade
        ];

        $data_array = [
            'count_all' => ($c_new + $c_eva) + ($c_pro + $c_solution),
            'data_attitude' => $data_attitude,
            'data_professional' => $data_professional,
            'data_solution' => $data_solution,
        ];

        return $data_array;
    }


    /**
     * Function to get eveluate data to show in ECO Detail page on tab การประเมิน ECO.
     * @param int $shop_id.
     * @param int $vendor_id.
     * @param string $search.
     * @return array: typeof array of object $myarray.
     * @uses static function questionnaire_types() -> parameter q_id.
     */
    public static function list_evaluate_attitude($shop_id, $vendor_id, $search)
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
                        //'system' => 0,
                        'vendor_id' => $vendor_id,
                        'questionnaire_name' => 'แบบประเมินทัศนคติ Ver.1',
                        'q_type_name' => 'ทัศนคติ',
                        'csc' => $data_vendor->region, //.' '.$data_vendor['csc'],
                        'status' => $data_vendor->evaluation,
                        'create_at' => $create_at,
                        'user_created' => $user_created,
                        'updated_at' => $update_at,
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
                    //'system' => 2,
                    'questionnaire_id' => $val->questionnaire_id,
                    'questionnaire_name' => $val->questionnaire_name,
                    'q_type_name' => self::questionnaire_types($val->questionnaire_type),
                    'csc' => $val->region, //.' '.$val['csc'],
                    'status' => $status_q,
                    'create_at' => date('d/m', strtotime($val->created_at)) . '/' . $year_e . date(' H:i', strtotime($val->created_at)),
                    'user_created' => $val['name_created'],
                    'updated_at' => date('d/m', strtotime($val->updated_at)) . '/' . $year_e . date(' H:i', strtotime($val->updated_at)),
                    'created' => $val['created_at'],

                ];
            }
        }

        $data_array = [
            'data_old' => $data_old,
            'data_new'  => $data_port
        ];
        return $data_array;
    }

    /**
     * Function to get name of meaning about q name.
     * @param int $q_id.
     * @return string $q_name.
     */
    public static function questionnaire_types($q_id)
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


    public static function get_evaluate_data($shop_id, $vendor_id)
    {
        $data_array = [];
        $vendor_id = $vendor_id;

        $data_vendor  = DB::table('rudy_vendor as cv')
            ->where('cv.id', $vendor_id)
            ->first();

        /* ประเมินทัศนคติ */
        // $data_evamore = DB::table('rudy_questionnaire_eco as rqe')
        //     ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
        //     ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
        //     ->where('rqce.region', $data_vendor->region)
        //     ->where('rqe.active', 0)
        //     ->where('rqe.questionnaire_type', 1)
        //     ->where('reqe.vendor_id', $vendor_id)
        //     ->orderby('reqe.created_at', 'desc')
        //     ->limit(1)
        //     ->get();

        // $data_c = DB::table('rudy_questionnaire_eco as rqe')
        //     ->select('rqe.id')
        //     ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
        //     ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
        //     ->where('rqce.region', $data_vendor->region)
        //     ->where('rqe.active', 0)
        //     ->where('rqe.STATUS', 1)
        //     ->where('rqe.questionnaire_type', 1)
        //     ->where('reqe.vendor_id', $vendor_id)
        //     ->groupby('reqe.created_at')
        //     ->get();

        // $c_new = count($data_c);
        // $status_evamore = "";

        // foreach ($data_evamore as $val_evamore) {

        //     if ($val_evamore->grade_type == 1) {
        //         $data_score = DB::table('rudy_evaluate_questionnaire_eco as reqe')
        //             ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS evaluation"))
        //             ->join('rudy_questionnaire_eco as rqe', function ($join) {
        //                 $join->on('rqe.id', '=', 'reqe.questionnaire_id')
        //                     ->where('rqe.questionnaire_type', '=', 1)
        //                     ->where('rqe.grade_type', '=', 1)
        //                     ->where('reqe.choice_id', '!=', 0);
        //             })
        //             ->where('reqe.vendor_id', $vendor_id)
        //             ->where('rqe.id', $val_evamore->questionnaire_id)
        //             ->groupby('reqe.questionnaire_id')
        //             ->orderby('evaluation', 'desc')
        //             ->limit(1)
        //             ->first();
        //         if ($data_score) {
        //             if ($data_score->evaluation >= $val_evamore->qualify_from && $data_score->evaluation <= $val_evamore->qualify_to) {
        //                 $status_evamore = "ผ่านเกณฑ์";
        //             } else if ($data_score->evaluation >= $val_evamore->fail_from && $data_score->evaluation <= $val_evamore->fail_to) {
        //                 $status_evamore = "ไม่ผ่านเกณฑ์";
        //             }
        //         }
        //     } else if ($val_evamore->grade_type == 2) {
        //         $data_score = DB::table('rudy_evaluate_questionnaire_eco as reqe')
        //             ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS evaluation"))
        //             ->join('rudy_questionnaire_eco as rqe', function ($join) {
        //                 $join->on('rqe.id', '=', 'reqe.questionnaire_id')
        //                     ->where('rqe.questionnaire_type', '=', 1)
        //                     ->where('rqe.grade_type', '=', 2)
        //                     ->where('reqe.choice_id', '!=', 0);
        //             })
        //             ->where('reqe.vendor_id', $vendor_id)
        //             ->where('rqe.id', $val_evamore->questionnaire_id)
        //             ->groupby('reqe.questionnaire_id')
        //             ->orderby('evaluation', 'desc')
        //             ->limit(1)
        //             ->first();

        //         $score = 0;
        //         if ($data_score) {
        //             $score = $data_score->evaluation;

        //             if ($score <= $val_evamore->grade_d_to) {
        //                 $status_evamore = "D";
        //             } else if ($score >= $val_evamore->grade_c_from && $score <= $val_evamore->grade_c_to) {
        //                 $status_evamore = "C";
        //             } else if ($score >= $val_evamore->grade_b_from && $score <= $val_evamore->grade_b_to) {
        //                 $status_evamore = "B";
        //             } else if ($score >= $val_evamore->grade_a_from) {
        //                 $status_evamore = "A";
        //             }
        //         }
        //     }
        // }

        // $data_eva_c = DB::table('rudy_vendor_evaluation_eco as rvee')
        //     ->where('rvee.vendor_id', $vendor_id)
        //     ->limit(1)
        //     ->get();
        // $c_eva = count($data_eva_c);

        // $data_eva = DB::table('rudy_vendor_evaluation_eco as rvee')
        //     ->select(DB::raw(
        //         "case
        //             when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
        //                     JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = $vendor_id) >= 70 then 'ผ่านเกณฑ์'
        //             when (select sum(reen.score) from rudy_vendor_evaluation_eco as rvee
        //                     JOIN rudy_eco_evaluation_answer as reen ON reen.id = rvee.answer where vendor_id = $vendor_id) < 70 then 'ไม่ผ่านเกณฑ์'
        //             ELSE 'รอประเมิน'
        //             END AS evaluation"
        //     ))
        //     ->where('rvee.vendor_id', $vendor_id)
        //     ->first();

        // $status_eva = "รอประเมิน";
        // if ($c_new > 0) {
        //     $status_eva = $status_evamore;
        // } else {
        //     $status_eva = $data_eva->evaluation;
        // }

        // $query_vendor = DB::table('rudy_vendor as rv')
        //     ->select(
        //         'rv.id',
        //         'rv.csc',
        //         'rv.region',
        //         'rv.vendor_type'
        //     )
        //     ->where('rv.id', $vendor_id)
        //     ->first();

        // ถ้าเจอ Vendor.
        if ($data_vendor) {
            $query_raw = DB::raw('SUM(reqe.score) AS sum_score');
            $query_avaluate = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                ->select('*', $query_raw)
                ->join('rudy_questionnaire_eco as rqe', 'rqe.id', 'reqe.questionnaire_id')
                ->join('rudy_users as ru', 'ru.id', 'reqe.created_by')
                ->where('vendor_id', $vendor_id)
                // Add for query sizing only.
                ->whereNotNull('size_s_from')
                ->whereNotNull('size_s_to');

            $query_avaluate = $query_avaluate
                ->groupBy('rqe.id')
                ->orderBy('reqe.id', 'desc')
                ->orderBy('reqe.created_at', 'desc')
                ->get();

            $size = '';
            $c_new = count($query_avaluate);
            $data_array = [];
            // return $query_avaluate;
            // foreach ($query_avaluate as $val) {
            if ($data_vendor->vendor_type == 0) {
                $size = 'S';
            } else {
                if ($query_avaluate[0]->sum_score > $query_avaluate[0]->size_s_from && $query_avaluate[0]->sum_score < $query_avaluate[0]->size_s_to) {
                    $size = 'S';
                } elseif ($query_avaluate[0]->sum_score > $query_avaluate[0]->size_m_from && $query_avaluate[0]->sum_score < $query_avaluate[0]->size_m_to) {
                    $size = 'M';
                    // } elseif ($query_avaluate[0]->sum_score > $query_avaluate[0]->size_l_from && $query_avaluate[0]->sum_score < $query_avaluate[0]->size_l_to) {
                } elseif ($query_avaluate[0]->sum_score > $query_avaluate[0]->size_l_from) {
                    $size = 'L';
                }
            }

            // }
        }

        $data_pro = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
            ->where('rqce.region', $data_vendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 2)
            ->where('reqe.vendor_id', $vendor_id)
            ->orderby('reqe.created_at', 'desc')
            ->limit(1)
            ->get();

        $data_cp = DB::table('rudy_questionnaire_eco as rqe')
            ->join('rudy_questionnaire_csc_eco as rqce', 'rqce.questionnaire_id', '=', 'rqe.id')
            ->join('rudy_evaluate_questionnaire_eco as reqe', 'reqe.questionnaire_id', '=', 'rqce.questionnaire_id')
            ->where('rqce.region', $data_vendor->region)
            ->where('rqe.active', 0)
            ->where('rqe.questionnaire_type', 2)
            ->where('reqe.vendor_id', $vendor_id)
            ->groupby('reqe.created_at')
            ->get();
        $c_pro = count($data_cp);

        $status_pro = "รอประเมิน";
        $grade = "";
        $status_end = "";

        foreach ($data_pro as $val_pro) { //วนหาแยกตามชนิดการตัดเกรด กรณีแบบฟอร์มเปลี่ยนทีหลัง

            if ($val_pro->grade_type == 1) { //1=คำนวณผ่านเกณฑ์,
                $data_score_pro = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS evaluation"))
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', '=', 'reqe.questionnaire_id')
                            ->where('rqe.questionnaire_type', '=', 2)
                            ->where('rqe.grade_type', '=', 1)
                            ->where('reqe.choice_id', '!=', 0);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('rqe.id', $val_pro->questionnaire_id)
                    ->first();
                if ($data_score_pro) {
                    if ($data_score_pro->evaluation >= $val_pro->qualify_from) {
                        $status_pro = "ผ่านเกณฑ์";
                    } else if ($data_score_pro->evaluation <= $val_pro->fail_to) {
                        $status_pro = "ไม่ผ่านเกณฑ์";
                    }
                }
            } else if ($val_pro->grade_type == 2) { //2=คำนวณตัดเกรด
                $data_sum_pro = DB::table('rudy_evaluate_questionnaire_eco as reqe')
                    ->select('questionnaire_id', DB::raw("SUM(reqe.score) AS sum_score"))
                    ->join('rudy_questionnaire_eco as rqe', function ($join) {
                        $join->on('rqe.id', '=', 'reqe.questionnaire_id')
                            ->where('rqe.questionnaire_type', '=', 2)
                            ->where('rqe.grade_type', '=', 2)
                            ->where('reqe.choice_id', '!=', 0);
                    })
                    ->where('reqe.vendor_id', $vendor_id)
                    ->where('rqe.id', $val_pro->questionnaire_id)
                    ->first();
                $score = 0;
                if ($data_sum_pro) {
                    $score = $data_sum_pro->sum_score;

                    if ($score <= $val_pro->grade_d_to) {
                        $status_pro = "D";
                    } else if ($score >= $val_pro->grade_c_from && $score <= $val_pro->grade_c_to) {
                        $status_pro = "C";
                    } else if ($score >= $val_pro->grade_b_from && $score <= $val_pro->grade_b_to) {
                        $status_pro = "B";
                    } else if ($score >= $val_pro->grade_a_from) {
                        $status_pro = "A";
                    }
                }
            }
        }

        $data_sol_grade = DB::table('rudy_vendor as rv')
            ->select(DB::raw("case
                                when rv.grade_eco = 1 then 'A'
                                when rv.grade_eco = 2 then 'B'
                                when rv.grade_eco = 3 then 'C'
                                when rv.grade_eco = 4 then 'D'
                                ELSE null
                            END AS grade "))
            ->where('rv.id', $vendor_id)
            ->first();

        $data_sol = DB::table('rudy_vendor_portfolio_eco as rvpe')
            ->select('rvpe.id')
            ->join('rudy_vendor_project_solution_eco as rvpse', 'rvpse.portfolio_id', '=', 'rvpe.id')
            ->where('rvpe.vendor_id', $vendor_id)
            ->where('rvpse.draf', 1)
            ->where('rvpse.status', 1)
            ->groupby('rvpe.id')
            ->get();
        $c_solution = count($data_sol);

        $data_attitude[] = [
            // 'count_attitude' => $c_new + $c_eva,
            'count_sizing' => $data_vendor->vendor_type == 0 ? 0 : $c_new,
            'status' => $size
        ];

        $data_professional[] = [
            'count_professional' => $c_pro,
            'status' => $status_pro
        ];

        $data_solution[] = [
            'count_solution' => $c_solution,
            'status' => $data_sol_grade->grade
        ];

        $data_array = [
            // 'count_all' => ($c_new + $c_eva) + ($c_pro + $c_solution),
            'data_sizing' => $data_attitude,
            'data_professional' => $data_professional,
            'data_solution' => $data_solution,
        ];

        return $data_array;
    }
}
