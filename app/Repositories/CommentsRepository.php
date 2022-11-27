<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Throwable;


class CommentsRepository
{
    /**
     * It inserts a new row into the rudy_comment_eco table
     *
     * @param args an array of arguments that will be passed to the function.
     */
    public function insertComment($args)
    {
        $vendorId  = $args['vendorId'];
        $shopId    = $args['shopId'];
        $score     = $args['score'];
        $createdBy = $args['createdBy'];
        $comments  = $args['comments'];

        if ($vendorId) {
            $id = generateNextId('rudy_comment_eco');
            DB::table('rudy_comment_eco')->insert([
                'id'          => $id,
                'vendor_id'   => $vendorId,
                'shop_id'     => $shopId,
                'comments'    => $comments,
                'score'       => $score,
                'created_by'  => $createdBy,
                'created_at'  => Date('Y-m-d H:i:s'),
                'updated_at'  => Date('Y-m-d H:i:s'),
            ]);

            if ($id) {
                return ['message' => 'Successfully added comment'];
            } else {
                return 'ข้อมูลไม่ครบ';
            }
        } else {
            return 'ข้อมูลไม่ครบ';
        }
    }

    public function updateComment($args)
    {

        $comment_id = $args['comment_id'];
        $query = DB::table('rudy_comment_eco as rce')
            ->where('rce.id', $comment_id)
            ->first();

        if (!empty($query)) {
            DB::table('rudy_comment_eco')->where('id', $comment_id)->update(
                [
                    'comments'    => $args['comments'],
                    'score'       => $args['score'],
                    'created_by'  => $args['created_by'],
                    'updated_at'  => date('Y-m-d H:i:s')
                ]
            );

            $queryComment = DB::table('rudy_comment_eco as rce')
                ->where('rce.id', $comment_id)
                ->first();

            return [
                'message' => 'Successfully updated comment',
                'comment' => $queryComment
            ];
        } else {
            return ['message' => 'Something went wrong'];
        }
    }

    /**
     * It returns a list of comments for a particular vendor.
     *
     * @param args an array of arguments that you can pass to the function.
     */
    public function listCommentData($args)
    {
        $shopId    = $args['shop_id'];
        $vendorId  = $args['vendor_id'];
        $userId    = $args['user_id'];
        $score     = $args['score'];

        try {
            $queryRaw = DB::raw("
                CASE
                    WHEN ru.pic is null THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                    WHEN ru.pic = '' THEN 'https://merudy.s3-ap-southeast-1.amazonaws.com/conex/vendor/avatar-1.jpg'
                    ELSE CONCAT('https://files.merudy.com/users/',ru.pic)
                END AS img_pic,
                DATE_FORMAT(rce.created_at,'%d/%m/%y') AS date_create
            ");
            $query = DB::table('rudy_comment_eco as rce')
                ->join('rudy_users as ru', 'ru.id', 'rce.created_by')
                ->select('rce.*', 'ru.name', 'ru.pic', $queryRaw)
                ->where('rce.shop_id', $shopId)
                ->where('rce.vendor_id', $vendorId)
                ->where('rce.active', '0');

            if ($score == '0') { //fail
                $query->where('rce.score', "0");
            } else if ($score == '1') {
                $query->where('rce.score', "1");
            }

            $query = $query->get();

            $data_array = [];
            if ($query) {
                foreach ($query as $val) {
                    $status = 0;
                    if ($userId == $val->created_by) {
                        $status = 1;
                    }
                    $data_array[] = [
                        'comment_id' => $val->id,
                        'img'        => $val->img_pic,
                        'name'       => $val->name,
                        'date'       => $val->date_create,
                        'comments'   => $val->comments,
                        'score'      => $val->score,
                        'status'     => $status,
                    ];
                }
                return $data_array;
            } else {
                return $data_array;
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function deleteComment($comment_id)
    {
        try {
            $query = DB::table('rudy_comment_eco as rce')
                ->where('rce.id', $comment_id)
                ->first();

            if (!empty($query)) {
                DB::table('rudy_comment_eco')
                    ->where('id', $comment_id)
                    ->update([
                        'active'      => 1,
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                return 'Delete comment successfully';
            } else {
                return 'This comment_id does not exist or undefined !.';
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
