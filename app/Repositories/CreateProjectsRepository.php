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

class CreateProjectsRepository
{

    // Get dropdown list of contact type master.
    public function getContactTypes()
    {
        $query = DB::table('rudy_master_eco_type_contact')
            ->select('id', 'type_contact_name')
            ->where('active', 1)
            ->get();
        return $query;
    }
    // Insert ECO data
    public function insertECO($args)
    {
        $dateNow = date("Y-m-d H:i:s");
        $id = generateNextId('rudy_vendor');
        try {
            $rudy_vendor = [
                'id'                => $id,
                'type_id'           => $args['customer_type'],
                'shop_id'           => $args['shop_id'],
                'vendor_code'       => self::genCodevendor($args['shop_id']),
                'vendor_type'       => (string) $args['vendor_type'],
                'gender'            => $args['gender'],
                'grade'             => $args['grade'],
                'prefix_name'       => $args['prefix_name'],
                'name'              => $args['name'],
                'nickname'          => $args['nickname'],
                'company_id'        => $args['company_id'],
                'company'           => $args['company'],
                'region'            => $args['region'],
                'csc'               => $args['csc'],
                'phone'             => $args['tel'],
                'phone_extension'   => $args['ext'],
                'tax_no'            => $args['tax_no'],
                'email'             => $args['email'],
                'line_id'           => $args['line_id'],
                'address'           => $args['address'],
                'note'              => $args['note'],
                'status'            => '0',
                'created_by'        => $args['created_by'],
                'username'          => $args['username'],
                'admin_eco'         => $args['admin_eco'],
                'added_date'        => $dateNow,
                'last_update'       => $dateNow,
                'entity_type'       => $args['entity_type']
            ];

            DB::table('rudy_vendor')->insert($rudy_vendor);

            $rudy_vendor_history_eco = [
                'vendor_id'         => $id,
                'user_id'           => $args['created_by'],
                'status'            => '0',
                'comment'           => '',
                'created_at'        => $dateNow,
                'updated_at'        => $dateNow,
            ];
            DB::table('rudy_vendor_history_eco')->insert($rudy_vendor_history_eco);

            $rudy_vendor_details_eco = [
                'vendor_id'          => $id,
                'boq'                => $args['boq'],
                'design'             => $args['design'],
                'join_scg'           => $args['join_scg'],
                'typeget_work'       => $args['typeget_work'],
                'staff_type'         => $args['staff_type'],
                'staff_name'         => $args['staff'], //ตัวใหม่กรอกจำนวนทีมงาน
                'capital'            => $args['capital'],
                'experience'         => $args['experience'],
                'machine'            => $args['machine'],
                'size'               => $args['size'],
                'register_date'      => $args['register_date'],
                'viewpoint'          => $args['viewpoint'],
                'qualify'            => $args['qualify'],
                'month_qualify'      => $args['month_qualify'],
                'created_at'         => $dateNow,
                'updated_at'         => $dateNow,
            ];
            DB::table('rudy_vendor_details_eco')->insert($rudy_vendor_details_eco);


            // Todo
            // field of table 'rudy_eco_contactors'

            // id is primaryKey
            // shop_id
            // vendor_id
            // name
            // nickname
            // position
            // contact_detail
            // contact_type_id
            // profile_img
            // contact_code

            $contactors = $args['contactors'];
            if (!empty($contactors) && count($contactors) > 0) {
                foreach ($contactors as $contact) {
                    $arrayContact = [
                        'shop_id'            => $contact['shop_id'],
                        'vendor_id'          => $id,
                        'name'               => $contact['name'],
                        'nickname'           => $contact['nickname'],
                        'position'           => $contact['position'],
                        'contact_detail'     => $contact['contact_detail'],
                        'contact_type_id'    => $contact['contact_type_id'],
                        'profile_img'        => $contact['profile_img'],
                        'extension_number'   => $contact['extension_number'],
                        'contact_code'       => self::genCodecontact($contact['shop_id']),
                    ];

                    // "shop_id": 228,
                    // "vendor_id": 1992,
                    // "name": "ตั้งใจ ทดสอบ",
                    // "nickname": "ตั้งใจ ทดสอบ",
                    // "position": "Developer",
                    // "contact_detail": "test@email.com",
                    // "contact_type_id": "1",
                    // "profile_img" : ""

                    $insertContactors = self::insertContactor($arrayContact);
                }
            }


            $fdImage = "eco_portal/vendor/profile";
            $fdFile = "eco_portal/vendor/files";

            if (!empty($args['profile_img'])) {
                $name = File::uploadFileBase64($args['profile_img'], $fdImage);

                if ($name) {
                    DB::table('rudy_vendor')
                        ->where('id', $id)
                        ->update(['pic' => $name]);
                }
            }

            if (!empty($args['fileupload'])) {
                foreach ($args['fileupload'] as $file) {
                    $name = File::uploadFileName($file['file'], $fdFile, $file['name']);
                    // $name = File::uploadFilesBase64($file['file'], $file['name'], $fdFile, $specific_name = null, $id);
                    if ($name) {
                        $vendor_file_eco = [
                            'vendor_id'             => $id,
                            'filename'              => $name,
                            'name'                  => $name,
                            'created_at'            => $dateNow,
                            'updated_at'            => $dateNow,
                        ];
                        DB::table('rudy_vendor_file_eco')->insert($vendor_file_eco);
                    }
                }
            }

            if (count($args['segment']) > 0) {
                foreach ($args['segment'] as $rs) {
                    $vendor_segment_eco = [
                        'vendor_id'     => $id,
                        'segment'       => $rs['segment_id'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_segment_eco')->insert($vendor_segment_eco);
                }
            }

            if (count($args['subsegment']) > 0) {
                foreach ($args['subsegment'] as $ar) {
                    $vendor_subsegment_eco = [
                        'vendor_id'     => $id,
                        'sub_segment'   => $ar['subseg_id'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_subsegment_eco')->insert($vendor_subsegment_eco);
                }
            }

            if (count($args['solution']) > 0) {
                foreach ($args['solution'] as $st) {
                    $vendor_solution_eco = [
                        'vendor_id'     => $id,
                        'solution'      => $st['solution_id'],
                        'note'          => $st['solution_note'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_solution_eco')->insert($vendor_solution_eco);
                }
            }

            if (count($args['subsolution']) > 0) {
                foreach ($args['subsolution'] as $sst) {
                    $vendor_subsolution_eco = [
                        'vendor_id'     => $id,
                        'solution'      => $sst['solution_id'],
                        'subsolution'   => $sst['subso_id'],
                        'note'          => $sst['subso_note'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_subsolution_eco')->insert($vendor_subsolution_eco);
                }
            }

            if (count($args['worktype']) > 0) {
                foreach ($args['worktype'] as $wt) {
                    $vendor_worktype_eco = [
                        'vendor_id'     => $id,
                        'worktype'      => $wt['work_type_id'],
                        'note'          => $wt['worktype_note'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_worktype_eco')->insert($vendor_worktype_eco);
                }
            }

            if (count($args['province1']) > 0) {
                foreach ($args['province1'] as $prov) {
                    $vendor_work_province = [
                        'province_id'   => $prov['province_id'],
                        'type'          => $prov['type'],
                        'vendor_id'     => $id,
                        'last_updated'  => $dateNow
                    ];
                    DB::table('rudy_vendor_work_province')->insert($vendor_work_province);
                }
            }

            if (count($args['province2']) > 0) {
                foreach ($args['province2'] as $prov) {
                    $vendor_work_province = [
                        'province_id'   => $prov['province_id'],
                        'type'          => $prov['type'],
                        'vendor_id'     => $id,
                        'last_updated'  => $dateNow
                    ];
                    DB::table('rudy_vendor_work_province')->insert($vendor_work_province);
                }
            }

            if (count($args['province3']) > 0) {
                foreach ($args['province3'] as $prov) {
                    $vendor_work_province = [
                        'province_id'   => $prov['province_id'],
                        'type'          => $prov['type'],
                        'vendor_id'     => $id,
                        'last_updated'  => $dateNow
                    ];
                    DB::table('rudy_vendor_work_province')->insert($vendor_work_province);
                }
            }

            $data_return = self::getReturnVender($id);

            return $data_return;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Update ECO data
    public function updateECO($args)
    {
        $id = $args['vendor_id'];
        $dateNow = date('Y-m-d H:i:s');

        try {
            DB::table('rudy_vendor')
                ->where('id', $id)
                ->update([
                    'type_id'           => $args['customer_type'],
                    'vendor_type'       => (string) $args['vendor_type'],
                    'gender'            => $args['gender'],
                    'grade'             => $args['grade'],
                    'prefix_name'       => $args['prefix_name'],
                    'name'              => $args['name'],
                    'nickname'          => $args['nickname'],
                    'company_id'        => $args['company_id'],
                    'company'           => $args['company'],
                    'region'            => $args['region'],
                    'csc'               => $args['csc'],
                    'phone'             => $args['tel'],
                    'phone_extension'   => $args['ext'],
                    'tax_no'            => $args['tax_no'],
                    'email'             => $args['email'],
                    'line_id'           => $args['line_id'],
                    'address'           => $args['address'],
                    'note'              => $args['note'],
                    'created_by'        => $args['created_by'],
                    'username'          => $args['username'],
                    'admin_eco'         => $args['admin_eco'],
                    'last_update'       => $dateNow,
                    'entity_type'       => $args['entity_type'],
                ]);

            DB::table('rudy_vendor_details_eco')
                ->where('vendor_id', $id)
                ->update([
                    'boq'                => $args['boq'],
                    'design'             => $args['design'],
                    'join_scg'           => $args['join_scg'],
                    'typeget_work'       => $args['typeget_work'],
                    //'staff'              => $args['staff'],
                    'staff_type'         => $args['staff_type'],
                    'staff_name'         => $args['staff'], //ตัวใหม่กรอกจำนวนทีมงาน
                    'capital'            => $args['capital'],
                    'experience'         => $args['experience'],
                    'machine'            => $args['machine'],
                    'size'               => $args['size'],
                    'register_date'      => $args['register_date'],
                    'viewpoint'          => $args['viewpoint'],
                    'qualify'            => $args['qualify'],
                    'month_qualify'      => $args['month_qualify'],
                    'updated_at'         => $dateNow,
                ]);


            $contactors = $args['contactors'];
            // echo $contactors[0]['shop_id'];
            // return $contactors;
            if (!empty($contactors)) {
                // DB::table('rudy_eco_contactors')->where('vendor_id', $id)->delete();
                foreach ($contactors as $contact) {
                    $arrayContact = [
                        'id'                 => $contact['id'],
                        'shop_id'            => $contact['shop_id'],
                        'vendor_id'          => $id,
                        'name'               => $contact['name'],
                        'nickname'           => $contact['nickname'],
                        'position'           => $contact['position'],
                        'contact_detail'     => $contact['contact_detail'],
                        'contact_type_id'    => $contact['contact_type_id'],
                        'profile_img'        => $contact['profile_img'],
                        'extension_number'   => $contact['extension_number'],
                        // 'created_at'         => $contact['created_at'],
                        'contact_code'       => self::genCodecontact($contact['shop_id']),
                    ];
                    $updateContactors = self::updateContactorPerson($arrayContact);
                }
            }

            $fdImage = "eco_portal/vendor/profile";
            $fdFile = "eco_portal/vendor/files";

            if (!empty($args['profile_img'])) {
                $name = File::uploadFileBase64($args['profile_img'], $fdImage);
                if ($name) {
                    DB::table('rudy_vendor')
                        ->where('id', $id)
                        ->update(['pic' => $name]);
                }
            }

            if (!empty($args['fileupload'])) {
                foreach ($args['fileupload'] as $file) {
                    $name = File::uploadFileName($file['file'], $fdFile, $file['name']);
                    // $name = File::uploadFilesBase64($file['file'], $file['name'], $fdFile, $specific_name = null, $id);
                    if ($name) {
                        $vendor_file_eco = [
                            'vendor_id'             => $id,
                            'filename'              => $name,
                            'name'                  => $name,
                            'created_at'            => $dateNow,
                            'updated_at'            => $dateNow,
                        ];
                        DB::table('rudy_vendor_file_eco')->insert($vendor_file_eco);
                    }
                }
            }

            if (count($args['segment']) > 0) {
                DB::table('rudy_vendor_segment_eco')->where('vendor_id', $id)->delete();
                foreach ($args['segment'] as $rs) {
                    $vendor_segment_eco = [
                        'vendor_id'     => $id,
                        'segment'       => $rs['segment_id'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_segment_eco')->insert($vendor_segment_eco);
                }
            }

            if (count($args['subsegment']) > 0) {
                DB::table('rudy_vendor_subsegment_eco')->where('vendor_id', $id)->delete();
                foreach ($args['subsegment'] as $ar) {
                    $vendor_subsegment_eco = [
                        'vendor_id'     => $id,
                        'sub_segment'   => $ar['subseg_id'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ];
                    DB::table('rudy_vendor_subsegment_eco')->insert($vendor_subsegment_eco);
                }
            }

            if (count($args['subsolution']) > 0) {
                DB::table('rudy_vendor_solution_eco')
                    ->where('vendor_id', $id)
                    ->update(['active' => 0,]); //0 = ไม่ใช้งานให้หมด

                DB::table('rudy_vendor_subsolution_eco')
                    ->where('vendor_id', $id)
                    ->update(['active' => 0,]); //0 = ไม่ใช้งานให้หมด

                foreach ($args['subsolution'] as $sst) {
                    $solution = $sst['solution_id'];
                    $subso_id = $sst['subso_id'];

                    /* เช็คว่ามี solution  */
                    $data_solution = DB::table('rudy_vendor_solution_eco')
                        ->select('id')
                        ->where('vendor_id', $id)
                        ->where('solution', $solution)
                        ->first();

                    if (empty($data_solution->id)) {
                        DB::table('rudy_vendor_solution_eco')->insert([
                            'vendor_id'     => $id,
                            'solution'      => $sst['solution_id'],
                            'created_at'    => $dateNow,
                            'updated_at'    => $dateNow,
                        ]);
                    } else {
                        DB::table('rudy_vendor_solution_eco')->where('id', $data_solution->id)
                            ->update([
                                'active' => 1,
                            ]);
                    }

                    $query = DB::table('rudy_vendor_subsolution_eco')
                        ->where('vendor_id', $id)
                        ->where('solution', $solution)
                        ->where('subsolution', $subso_id)
                        ->first();

                    // echo $id . "\n";
                    // echo $solution . "\n";
                    // echo $subso_id . "\n";
                    // echo "----------";
                    // echo gettype($query->id) . "\n";
                    // echo "----------";
                    // print_r($query);
                    // exit;

                    $subsolutionId = !empty($query->id) ? $query->id : '';
                    if ($subsolutionId) { //มีอัพเดทให้ใช้งาน
                        DB::table('rudy_vendor_subsolution_eco')
                            ->where('id', $query->id)
                            ->update([
                                'active' => 1,
                            ]);
                    } else { //ไม่มีจากที่ส่งมาให้เพิ่ม
                        DB::table('rudy_vendor_subsolution_eco')->insert([
                            'vendor_id'     => $id,
                            'solution'      => $sst['solution_id'],
                            'subsolution'   => $sst['subso_id'],
                            'note'          => $sst['subso_note'],
                            'created_at'    => $dateNow,
                            'updated_at'    => $dateNow,
                        ]);
                    }
                }
            }

            if (count($args['worktype']) > 0) {
                DB::table('rudy_vendor_worktype_eco')->where('vendor_id', $id)->delete();
                foreach ($args['worktype'] as $wt) {
                    DB::table('rudy_vendor_worktype_eco')->insert([
                        'vendor_id'     => $id,
                        'worktype'      => $wt['work_type_id'],
                        'note'          => $wt['worktype_note'],
                        'created_at'    => $dateNow,
                        'updated_at'    => $dateNow,
                    ]);
                }
            }

            if (count($args['province1']) > 0) {
                DB::table('rudy_vendor_work_province')->where('vendor_id', $id)->delete();
                foreach ($args['province1'] as $prov) {
                    DB::table('rudy_vendor_work_province')->insert([
                        'province_id'   => $prov['province_id'],
                        'type'          => $prov['type'],
                        'vendor_id'     => $id,
                        'last_updated'  => $dateNow
                    ]);
                }
            }

            if (count($args['province2']) > 0) {
                foreach ($args['province2'] as $prov) {
                    DB::table('rudy_vendor_work_province')->insert([
                        'province_id'   => $prov['province_id'],
                        'type'          => $prov['type'],
                        'vendor_id'     => $id,
                        'last_updated'  => $dateNow
                    ]);
                }
            }

            if (count($args['province3']) > 0) {
                foreach ($args['province3'] as $prov) {
                    DB::table('rudy_vendor_work_province')->insert([
                        'province_id'   => $prov['province_id'],
                        'type'          => $prov['type'],
                        'vendor_id'     => $id,
                        'last_updated'  => $dateNow
                    ]);
                }
            }

            $data_return = self::getReturnVender($id);

            return $data_return;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Generate code for Codeevendor
    public function genCodevendor($shop_id)
    {
        $dcode = 'CT-' . date('Y') . '-';
        $query = DB::table('rudy_vendor')
            ->where('shop_id', $shop_id)
            ->where('vendor_code', 'LIKE', "{$dcode}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($query) {
            $number =  substr($query->vendor_code, -5) + 1;
            $code = 'CT-' . date('Y') . '-' . sprintf('%05d', $number);
        } else {
            $code = 'CT-' . date('Y') . '-00001';
        }

        return $code;
    }

    // Retrieve data from database when function insertECO($args) is successfully
    public function getReturnVender($id)
    {
        $query = DB::table('rudy_vendor')
            ->select('id', 'vendor_type', 'type_id', 'name', 'company', 'region', 'csc', 'grade', 'status_maomao', 'added_date', 'status_question', 'pic', 'shop_id', 'admin_eco')
            ->where('id', $id)
            ->first();
        $returnData = [
            'id'              => $query->id,
            'vendor_type'     => $query->vendor_type,
            'type_id'         => $query->type_id,
            'name'            => $query->name,
            'company'         => $query->company,
            'region'          => $query->region,
            'csc'             => $query->csc,
            'grade'           => $query->grade,
            'status_maomao'   => $query->status_maomao,
            'added_date'      => $query->added_date,
            'status_question' => $query->status_question,
            'pic'             => 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/' . $query->pic,
            'shop_id'         => $query->shop_id,
            'admin_eco'       => $query->admin_eco
        ];
        return $returnData ?? [];
    }

    // Check user already exists
    public static function checkDataAlready($args)
    {
        $shopId = '228';
        $phone = implode(explode("-", $args['phone']));
        $name = $args['name'];
        $taxNo = $args['tax_no'];
        $company = $args['company'];
        $type = $args['type'];

        $returnData = [];

        $dataPhone = [];
        $dataName = [];
        $dataTaxNo = [];
        $dataCompany = [];

        $countPhone = 0;
        $countName = 0;
        $countTaxNo = 0;
        $countCompany = 0;
        try {

            if (!empty($phone) || !empty($name) || !empty($taxNo) || !empty($company)) {

                if (!empty($phone)) {
                    $queryPhone = self::queryAlready('phone', $shopId, $phone);
                    $countPhone = $queryPhone['total'];
                    $dataPhone = $queryPhone['query'];
                }

                if ($type == 0 || $type == '') {
                    if (!empty($name)) {
                        $queryName = self::queryAlready('name', $shopId, $name);
                        $countName = $queryName['total'];
                        $dataName = $queryName['query'];
                    }
                }

                if (!empty($taxNo)) {
                    $queryTaxNo = self::queryAlready('tax_no', $shopId, $taxNo);
                    $countTaxNo = $queryTaxNo['total'];
                    $dataTaxNo = $queryTaxNo['query'];
                }

                if ($type == 1) {
                    if (!empty($company)) {
                        $queryCompany = self::queryAlready('company', $shopId, $company);
                        $countCompany = $queryCompany['total'];
                        $dataCompany = $queryCompany['query'];
                    }
                }
            }
            // echo $taxNo;
            // return $queryTaxNo;
            $mergeValue = array_merge($dataPhone, $dataName, $dataTaxNo, $dataCompany);
            $responseArray = array_values(array_unique($mergeValue, SORT_REGULAR));
            $dataTotal = $countPhone + $countName + $countCompany + $countTaxNo;

            $returnData = [
                'count' => $dataTotal ? $dataTotal : 0,
                'list'  => $responseArray ? $responseArray : [],
                // 'list'  => $mergeValue ? $mergeValue : [],
                'msg'   => $dataTotal > 0 ? 'มีผู้ช้งานนี้แล้ว' : ''
            ];
            return $returnData;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // เหมารวมก่อสร้าง
    // 0364546536
    // 3567546875874
    // Functoin reuseable query for retrieving and structured data
    public static function queryAlready($option = '', $shopId, $arg)
    {
        try {

            $query = DB::table('rudy_vendor')
                ->where('shop_id', $shopId);
            if ($option && $option != 'name' && $option != 'company') {
                $query->where($option, $arg);
            }
            if ($option == 'name') {
                // $query->where($option, 'like', '%' . $arg . '%');
                $query->where($option, $arg);
            }

            if ($option == 'company') {
                $query->where($option, $arg);
            }


            $query = $query->get();
            $queryCount = $query->count();

            $exist = [];
            if (empty($query)) {
                return [
                    "query" => $exist,
                    "total" => 0
                ];
            } else {
                foreach ($query as $val) {
                    $exist[] = [
                        'id'            => $val->id,
                        'name'          => $val->name,
                        'csc_name'      => $val->region . ' (' . $val->csc . ')',
                        'company'       => !empty($val->company) ? $val->company : $val->name,
                        'phone'         => $val->phone,
                        'tax_no'        => $val->tax_no,
                        'profile_img'   => (empty($val->pic) ? 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg' : 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/' . $val->pic),
                    ];
                }
                return [
                    "query" => $exist,
                    "total" => $queryCount
                ];
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * Field data in database : rudy_eco_contactors
     * id int auto increment primaryKey
     * vendor_id int foreinkey
     * name verchar 225
     * position varchar 255
     * phone varchar 50
     * email verchar 225
     * lineID varchar 255
     * */
    public function insertContactor($args)
    {

        $shopId   = $args['shop_id'];
        $vendorId = $args['vendor_id'];

        $name     = $args['name'];
        $nickname = $args['nickname'];
        $position = $args['position'];

        $contact_type_id  = $args['contact_type_id'];
        $contact_detail   = $args['contact_detail'];
        $extension_number = $args['extension_number'];
        $contact_code     = $args['contact_code'];
        $image    = $args['profile_img'];
        $dateNow  = Date('Y-m-d H:i:s');

        try {
            // Insert contactors
            $id = generateNextId('rudy_eco_contactors');
            $rudy_eco_contactors = [
                'id'                 => $id,
                'shop_id'            => $shopId,
                'vendor_id'          => $vendorId,
                'name'               => $name,
                'nickname'           => $nickname,
                'position'           => $position,
                'contact_detail'     => $contact_detail,
                'contact_type_id'    => $contact_type_id,
                'contact_code'       => self::genCodecontact($shopId),
                'extension_number'   => $extension_number,
                'created_at'         => $dateNow
            ];

            DB::table('rudy_eco_contactors')->insert($rudy_eco_contactors);
            $fdImage = "eco_portal/vendor/profile/contactors";

            if (!empty($image) || $image != null || $image != '') {
                $name = File::uploadFileBase64($image, $fdImage);
                if ($name) {
                    DB::table('rudy_eco_contactors')
                        ->where('id', $id)
                        ->update(['profile_image' => 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/contactors/' . $name]);
                }
            }

            $returnData = self::getOwnerContactor($shopId, $vendorId);
            return $returnData;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function insertContactorPerson($args)
    {
        // Recieved from controller.
        // Send data to insertContactor function.
        // Get callback value from function.
        $insertContact = self::insertContactor($args);
        return $insertContact ? true : false;
    }

    public function updateContactorPerson($args)
    {
        // Recieved from controller.
        // Send data to insertContactor function.
        // Get callback value from function.
        if ($args['id'] == 0) {
            $insertContact = self::insertContactor($args);
        } elseif ($args['id'] != 0 || $args['id'] != '') {
            if (empty($args['id'])) return ['msg' => 'Undefind variable id.'];
            $rudy_eco_contactors = [
                'id'                 => $args['id'],
                'shop_id'            => $args['shop_id'],
                'vendor_id'          => $args['vendor_id'],
                'name'               => $args['name'],
                'nickname'           => $args['nickname'],
                'position'           => $args['position'],
                'contact_detail'     => $args['contact_detail'],
                'contact_type_id'    => $args['contact_type_id'],
                'contact_code'       => $args['contact_code'],
                'extension_number'   => $args['extension_number'],
                // 'created_at'         => $args['created_at'],
            ];
            $updateContact = DB::table('rudy_eco_contactors')
                ->where('id', $args['id'])
                ->where('vendor_id', $args['vendor_id'])
                ->update($rudy_eco_contactors);

            $fdImage = "eco_portal/vendor/profile/contactors";
            $image = $args['profile_img'];

            if (!empty($image) || $image != null || $image != '') {
                $name = File::uploadFileBase64($image, $fdImage);
                if ($name) {
                    DB::table('rudy_eco_contactors')
                        ->where('id', $args['id'])
                        ->update(['profile_image' => 'https://merudy.s3.ap-southeast-1.amazonaws.com/eco_portal/vendor/profile/contactors/' . $name]);
                }
            }
        }
        return 'Update contactor success.';
    }

    public function deleteContactor($contact_ids)
    {
        if (count($contact_ids) > 0) {
            foreach ($contact_ids as $val) {
                DB::table('rudy_eco_contactors')
                    ->where('id', $val['id'])
                    ->delete();
            }
            return 'Deleted!.';
        } else {
            return ['msg' => 'Undefind variable id.'];
        }
    }

    // Function get contactor when user update
    // Sort by created date
    public function getOwnerContactor($shopId, $vendorId)
    {
        try {
            $query = DB::table('rudy_eco_contactors')
                ->select('id', 'shop_id', 'vendor_id', 'name', 'nickname', 'position', 'contact_type_id', 'contact_detail', 'extension_number', 'profile_image as profile_img', 'created_at')
                ->where('shop_id', $shopId)
                ->where('vendor_id', $vendorId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $query;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function genCodecontact($shopId)
    {
        $dcode = 'CP-' . date('Y') . '-';

        $data = DB::table('rudy_eco_contactors')
            ->select('id', 'contact_code')
            ->where('shop_id', $shopId)
            ->where('contact_code', 'like', "$dcode%")
            ->orderBy('id', 'desc')
            ->first();

        if (!empty($data)) {
            $number =  substr($data->contact_code, -5) + 1;
            $code = 'CP-' . date('Y') . '-' . sprintf('%05d', $number);
        } else {
            $code = 'CP-' . date('Y') . '-00001';
        }

        return $code;
    }

    public function uploadDocuments($args)
    {
        $fdImage = "eco_portal/vendor/profile";
        $fdFile = "eco_portal/vendor/files";

        // if (!empty($args['profile_img'])) {
        //     $name = File::uploadFileBase64($args['profile_img'], $fdImage);

        //     if ($name) {
        //         DB::table('rudy_vendor')
        //             ->where('id', $id)
        //             ->update(['pic' => $name]);
        //     }
        // }

        // if (!empty($args['fileupload'])) {
        //     foreach ($args['fileupload'] as $file) {
        //         $name = File::uploadFilesBase64($file['file'], $file['name'], $fdFile, $specific_name = null, $id);
        //         if ($name) {
        //             $vendor_file_eco = [
        //                 'vendor_id'             => $id,
        //                 'filename'              => $name,
        //                 'name'                  => $name,
        //                 'created_at'            => $dateNow,
        //                 'updated_at'            => $dateNow,
        //             ];
        //             DB::table('rudy_vendor_file_eco')->insert($vendor_file_eco);
        //         }
        //     }
        // }
        return '';
    }


    public function deleteFile($id)
    {
        $query = DB::table('rudy_vendor_file_eco')->where('id', $id)->first();
        if (!empty($query)) {
            $fd = 'eco_portal/vendor/files';
            $image = $query->filename;
            $pathFile = $fd . '/' . $image;
            $unlink = File::deleteFile($pathFile);
            if ($unlink) {
                $deleteFile = DB::table('rudy_vendor_file_eco')->where('id', $id)->delete();
                return "File has deleted successfully";
            }
            return "Can't find file in Storage.";
        }
        return "This file id missing.";
    }
}
