<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Common\EncryptSignVerify;
use Admin\Model\BaseModel;
/**
 * 登录类
 * 
 * @author Jan
 *
 */
class ExGiftController extends Controller
{

    public static $table = ['allot_policy'];

    /**
     * @ Purpose: 礼包兑换金额页面显示
     * @param string $money
     * @return []
     */
    public function inquireUserExRatio($money){

        $resData = [];

        $data = BaseModel::getDbData([
            'table' => self::$table[0],
            'where' => ['cash' => 7]
        ]);

        if($data)

            foreach ($data as $val)
                $resData[] = ['describe' => $val['describe'], 'price' => $val['exratio'] * $money];


        return $resData;

    }


    /**
     * @ Purpose:1.1 用户信息与兑换资格查询接口
     * @param [] $parmArr 若参数值为空 不传
     * e.g. $parmArr = [
     * 'Phone' => 13333333333 //手机号
     * 'Kcodetype' => 'S7' //产品型号
     * ];
     * @return []
     */
    public function inquireUserExStatus($paramArr, $source){

        return $this->curlPostSend($paramArr, $source);

    }

    /**
     * @ Purpose: 1.2 礼包推送接口
     * @param [] $parmArr 若参数值为空 不传
     * e.g. $parmArr = [
     * 'phone' => 13333333333 //手机号
     * 'kcodeType' => 'S7' //产品型号
     * 'kcode' => 'am123' //暗码
     * 'kcodeSn' => 'mm1234' //明码
     * 'deviceSn' => 'sb1234' //设备码
     * 'bingSn' => 'bd123'  //绑定码
     * 'Amount' => 666  //礼包金额
     * ];
     * @return []
     */
    public  function pushGift($paramArr, $source){

        return $this->curlPostSend($paramArr, $source);
    }


    //发送数据
    public function curlPostSend($paramArr, $source){

        return Curl::curl_header_post(
            C($source),
            json_encode(EncryptSignVerify::sign($paramArr)),
            ["content-type: application/json;charset=UTF-8"]
        );
    }


}

