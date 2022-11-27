<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Managements\ManagementController;

Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'manage'
], function () {
    Route::post('/get_project_list', [ManagementController::class, 'getProjectList']);
    Route::post('/get_vendor_view', [ManagementController::class, 'getVendorView']);
    Route::post('/list_portfolio', [ManagementController::class, 'listPortfolio']);
    Route::post('/list_questionnaire', [ManagementController::class, 'listQuestionnaire']);
    Route::post('/get_m_solution_list', [ManagementController::class, 'getMSolutioList']);
    Route::post('/list_solution_data', [ManagementController::class, 'listSolutionData']);
    Route::post('/insert_eco', [ManagementController::class, 'insertECO']);
    Route::post('/update_eco', [ManagementController::class, 'updateECO']);
    Route::post('/check_data_already', [ManagementController::class, 'checkDataAlready']);
    Route::post('/list_evaluate_attitude', [ManagementController::class, 'listEvaluateAttitude']);
    Route::post('/list_evaluate_professional', [ManagementController::class, 'listEvaluateProfessional']);
    Route::post('/get_evaluate_data', [ManagementController::class, 'getEvaluateData']);
    Route::post('/data_evaluation', [ManagementController::class, 'dataEvaluation']);
    Route::delete('/delete_file', [ManagementController::class, 'deleteFile']);

    // Contactors.
    Route::post('/insert_contact_person', [ManagementController::class, 'insertContactorPerson']);
    Route::put('/update_contact_person', [ManagementController::class, 'updateContactorPerson']);
    Route::get('/get_contact_type', [ManagementController::class, 'getContactTypes']);
    Route::post('/get_contactors', [ManagementController::class, 'getContactor']);
    Route::post('/get_owner_contactor', [ManagementController::class, 'getOwnerContactor']);
    Route::delete('/delete_contact_person', [ManagementController::class, 'deleteContactPerson']);
    Route::post('/test_gen_contactcode', [ManagementController::class, 'testGenContactcode']);

    // UpSkills.
    Route::post('/list_upskills', [ManagementController::class, 'listUpskill']);
    Route::get('/learn_solution', [ManagementController::class, 'learnSolution']);
    Route::post('/insert_upskill', [ManagementController::class, 'insertUpskill']);
    Route::put('/update_upskill', [ManagementController::class, 'updateUpskill']);

    // Comments.
    Route::post('/list_comment_data', [ManagementController::class, 'listCommentData']);
    Route::post('/insert_comment', [ManagementController::class, 'insertComment']);
    Route::put('/update_comment', [ManagementController::class, 'updateComment']);
    Route::delete('/delete_comment', [ManagementController::class, 'deleteComment']);

    // start mark.
    Route::post('/starmark', [ManagementController::class, 'starMark']);

    // update status in detail page.
    Route::post('/update_status', [ManagementController::class, 'updateStatus']);

    // Mange portfolio data.
    Route::post('/insert_portfolio', [ManagementController::class, 'insertPortfolio']);
    Route::post('/update_portfolio', [ManagementController::class, 'updatePortfolio']);
    Route::post('/delete_portfolio', [ManagementController::class, 'deletePortfolio']);

    // List Projects.
    Route::post('/list_project', [ManagementController::class, 'listProjects']);

    // sub eco detail data. 🧑🏻‍💼 Helper Guid.
    Route::post('/get_solution_data', [ManagementController::class, 'get_solution_data']);
    Route::post('/get_evaluate_data', [ManagementController::class, 'get_evaluate_data']);
    Route::post('/list_evaluate_attitude', [ManagementController::class, 'list_evaluate_attitude']);

    // Question.
    Route::post('/insert_question', [ManagementController::class, 'insertQuestion']);
    Route::put('/update_question', [ManagementController::class, 'updateQuestion']);
    Route::delete('/delete_question', [ManagementController::class, 'deleteQuestion']);
    Route::get('/get_question_type', [ManagementController::class, 'getQuestionSizing']);
    Route::post('/list_question_choice', [ManagementController::class, 'listQuestionChoice']);

    Route::post('/get_question_list', [ManagementController::class, 'getQuestionList']);

    // Sizing
    Route::post('/insert_sizing', [ManagementController::class, 'insertSizing']);
    Route::put('/update_sizing', [ManagementController::class, 'updateSizing']);
    Route::post('/list_sizing', [ManagementController::class, 'listSizing']);
    Route::post('/list_evaluate_sizing', [ManagementController::class, 'listEvaluateSizing']);
    Route::post('/insert_evaluate_sizing', [ManagementController::class, 'insertEvaluateSizing']);
    Route::post('/get_data_evaluation', [ManagementController::class, 'getDataEvaluation']);
    Route::post('/detail_eva_questionnaire', [ManagementController::class, 'detailEvaQuestionnaire']);
    Route::post('/preview_eva_sizing', [ManagementController::class, 'previewEvaluateSizing']);
    Route::post('/get_vendor_personal', [ManagementController::class, 'getVendorPersonal']);
    // Route::delete('/delete_sizing', [ManagementController::class, 'deleteSizing']);
    // Route::post('/get_sizing_list', [ManagementController::class, 'getSizingList']);
    // Route::get('/get_question_type', [ManagementController::class, 'getQuestionSizing']);
    // Route::post('/list_question_choice', [ManagementController::class, 'listQuestionChoice']);
});
