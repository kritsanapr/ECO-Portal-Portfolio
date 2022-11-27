<?php

use App\Models\project_card;
use App\Services\FileService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// image
if (!function_exists('generateNextId')) {
    function generateNextId($tb)
    {
        // $query = DB::statement("SELECT MAX(id)+1 as id FROM $tb");
        $query = DB::Table($tb)->selectRaw("MAX(id)+1 as id")->first('id');
        $id = $query->id;
        if (($id == NULL) || ($id == '')) $id = 1;
        return ($id);
    }
}
