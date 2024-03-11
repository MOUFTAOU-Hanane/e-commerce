<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;
use Intervention\Image\Facades\Image;

class FileService
{
    public function saveImage($file)
    {
        try {
            $baseUrl ="http://127.0.0.1:8000/api/image/"; //20230615_1686825525_821182.png
            $baseUrlCover = "/uploads/"; //
            $extension  = "";
            $fileName = "";
            $extension = $file->getClientOriginalExtension();
            $fileName = date('Ymd') . '_' . time() . '_' . mt_rand(1000, 1000000) . '.' . $extension;
            $pathName =   $baseUrlCover . $fileName;
            Storage::disk('local')->put($pathName,  File::get($file));
            $fileUrl =   $baseUrl. $fileName;

            log::error( $baseUrl);
            log::error( $fileUrl);


            return $fileUrl;
        } catch (\Exception $th) {
         throw $th;


        }
    }//end saveImage

    //get image url or send a default image
   public function getImageUrl($name) {
    try {
        $baseUrl ="http://127.0.0.1:8000/api/image/";
        $fileName = storage_path('app/uploads/' . $name);


        if (Storage::disk('local')->exists($baseUrl .  $name))  {

             return response()->file($fileName);
        }

        throw new Exception('Image non trouvée');
    } catch (\Exception $th) {
        throw $th;
    }
} //end getImageUrl





}
