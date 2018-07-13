<?php
namespace Admin\Model;

class BatchPolicyModel extends  BaseModel{

    public static $table = ['policy'];
    public static $field = [ 4=>'channel_name', 5 => 'pnumber'];

    public static function getPolicyListData($type, $order){
        $policies = M('policy')->where([
            'policy_type' => $type,
            'status' => 1
        ])->order("$order desc")->select();

        $response['data'] = json_encode($policies);
        return $response;
    }

}