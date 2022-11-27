<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Filter\FilterController;

Route::group([
  'middleware' => ['jwt.verify'],
  'prefix' => 'filter'
], function () {
  Route::post('/filter_work_type', [FilterController::class, 'filterWorkType']);

  Route::post('/filter_chain', [FilterController::class, 'filterChain']);
  Route::post('/filter_csc', [FilterController::class, 'filterCSC']);
  Route::get('/filter_geography', [FilterController::class, 'filterGeography']);
  Route::post('/filter_solution', [FilterController::class, 'filterSolution']);
  Route::post('/filter_sub_solution', [FilterController::class, 'filterSubSolution']);
  Route::post('/filter_provinces', [FilterController::class, 'filterProvinces']);
  Route::post('/filter_group_provinces', [FilterController::class, 'filterGroupProvinces']);
  Route::get('/filter_regions', [FilterController::class, 'filterRegions']);
  Route::post('/filter_user_admin', [FilterController::class, 'filterUserAdmin']);
  Route::get('/filter_user', [FilterController::class, 'filterUser']);
  Route::get('/filter_status', [FilterController::class, 'filterStatus']);
  Route::post('/filter_subsegment', [FilterController::class, 'filterSubsegment']);
  Route::post('/filter_solution_all', [FilterController::class, 'filterSolutionAll']);
  Route::get('/filter_staff', [FilterController::class, 'filterStaff']);
  Route::get('/filter_work_exp', [FilterController::class, 'filterWorkExp']);
  Route::get('/filter_capital', [FilterController::class, 'filterCapital']);
  Route::post('/filter_data_company', [FilterController::class, 'filterDataCompany']);
  Route::get('/filter_entity_type', [FilterController::class, 'filterEntityType']);
  Route::post('/filter_customer_type', [FilterController::class, 'filterCustomerType']);
});
