<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class FileServices
{
    /**
     * Global function for upload file to S3 bucket
     *
     * @param  $file
     * @param  $pathFile
     *
     * @return string $path
     */
    public static function uploadFile($file, $pathFile)
    {
        try {
            $imageName = time() . '.' . $file->extension();

            $path = Storage::disk('s3')->put($pathFile, $file);
            $path = Storage::disk('s3')->url($path);

            return $path;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * It takes a base64 encoded image, decodes it, and uploads it to S3
     *
     * @param file The file you want to upload.
     * @param folder The folder where you want to upload the file.
     *
     * @return The name of the file that was uploaded.
     */
    public static function uploadFileBase64($file, $folder, $fileName = null)
    {
        try {
            $image_64 = $file;
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = time() . gmdate('YmdHis') . rand(0000, 9999) . '.' . $extension;

            $upload_path = $folder . "/" . $imageName;
            $path = Storage::disk('s3')->put($upload_path, base64_decode($image));
            $path = Storage::disk('s3')->url($path);

            return $imageName;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function uploadFileName($file, $folder, $file_name)
    {
        try {
            $image_64 = $file;
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            // $imageName = $file_name . '-' . gmdate('Ymd') . '-' . time();
            $imageName = $file_name;
            $upload_path = $folder . "/" . $imageName;
            $path = Storage::disk('s3')->put($upload_path, base64_decode($image));
            $path = Storage::disk('s3')->url($path);

            return $imageName;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function uploadFilesBase64($file, $fileName, $folder = null, $specific_name = null, $id)
    {

        $img_name = '';
        if (!preg_match('/data:([^;]*);base64,(.*)/', $file, $matches)) {
            die("error");
        }
        $content = str_replace('data:image/', '', $matches[0]);
        $content = str_replace('data:application/', '', $content);
        $content = str_replace('data:text/plain', 'txt', $content);
        $content = str_replace('data:text/csv', 'csv', $content);
        $content = str_replace('data:text/xlsx', 'xlsx', $content);
        $content = explode(";", $content);
        $content = $content[0];
        if (strpos($content, 'ms-word')) {
            $content = 'docx';
        }
        if (strpos($content, 'ms-excel')) {
            $content = 'xlsx';
        }
        if (strpos($content, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
            $content = 'csv';
        }
        if (strpos($content, 'sheet')) {
            $content = 'xlsx';
        }
        if ($specific_name === null) {
            $pname = $id . gmdate('YmdHis') . rand(0000, 9999);
            $img_name = $pname . '.' . $content;
            $filenameext = $content;
        } else {
            $img_name = $specific_name;
            if (strpos($img_name, '.') !== false) {
                $a = explode(',', $img_name);
                $filenameext = end($a);
            } else {
                $filenameext = 'jpeg';
            }
        }

        if ($img_name) {
            $uploadPath = $folder . "/" . $img_name;
            $path = Storage::disk('s3')->put($uploadPath, base64_decode($file));
            $path = Storage::disk('s3')->url($path);
        }

        return $img_name;
    }

    /**
     * It deletes a file from the S3 bucket
     *
     * @param pathFile The path to the file you want to delete.
     *
     * @return The path of the file that was deleted.
     */
    public static function deleteFile($pathFile)
    {
        try {
            $path = Storage::disk('s3')->delete($pathFile);
            return $path;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * It takes the old path of the file, moves it to the new path, and then resizes it to 100x100
     *
     * @param oldPath The path of the file you want to move.
     * @param newPath The new path of the file.
     *
     * @return The file is being moved from the old path to the new path.
     */
    public function moveFile($oldPath, $newPath)
    {
        try {
            $s3 = Storage::disk('s3')->move($oldPath, $newPath);
            $s3 = Image::make($newPath)->resize(100, 100);
            return $s3;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
