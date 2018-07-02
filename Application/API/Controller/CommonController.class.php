<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
/**
 * 基类
 * 
 * @author yzw
 *
 */
class CommonController extends Controller
{
    //更改K码状态，记录兑换记录表，记录日志
    /*
     * $kcode, 暗码值
     * $rate=1, 兑换比例
     * $dhtotal, 兑换了多少个
     * $phone,   手机号
     * $status,  状态0 表示成功1 表示失败
     * $channel, 兑换通道
     * exratio   兑换比例
    */
    public static function ChangeLog($kcode,$rate=1,$dhtotal,$phone,$status,$channel,$exratio){
            if($status==1){
                $data=M("relation")->filed("money,orderid")->where(["secretcd"=>$kcode])->find();
                $save["status"]=2;
                $result1=M("relation")->where(["secretcd"=>$kcode])->save($save);
                if($result1===false){
                    return false;
                }
                //新增兑换记录
                $add["atvphone"]=$phone;
                $add["kvalue"]=$data["money"];
                $add["exchannel"]=$channel;
                $add["activate_time"]=date("Y-m-d H:i:s",time());
                $add["orderid"]=$data["orderid"];
                $add["secretcd"]=$kcode;
                $add["status"]=2;
                $add["exratio"]=$exratio;
                $add["exrate"]=$rate;
                $result2=M("use_details")->add($add);
                if($result2===false){
                    return false;
                }else{
                    return true;
                }
            }

    }


    //将请求的数据放回到指定的表中
    public static function setlog($request,$response,$url){
        $add_data["url"]=$url;
        $add_data["request"]=$request;
        $add_data["response"]=$response;
        $add_data["create_at"]=date("Y-m-d H:i:s",time());
        M("loglist")->add($add_data);
    }

}