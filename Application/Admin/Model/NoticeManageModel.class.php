<?php
namespace Admin\Model;

class NoticeManageModel extends  BaseModel{

    public static $table = ['notice'];


    public static function uploads($files){

        $ext = pathinfo(strip_tags($files['imgFile']['name']), PATHINFO_EXTENSION);

        $newName = dirname($files['imgFile']['tmp_name']).'/'.date("YmdHis").rand(0,99).".".$ext;

        rename($files['imgFile']['tmp_name'],$newName);

        $fields['img'] = new \CURLFile($newName);

        //初始化curl
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, C('uploadCksApiUrl'));
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //运行curl
        $output = curl_exec($ch);

        curl_close($ch);

        @unlink($newName);

        return $output;

    }


}