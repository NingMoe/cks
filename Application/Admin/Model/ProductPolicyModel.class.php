<?php
namespace Admin\Model;



class ProductPolicyModel extends  BaseModel{

    public static $table = ['pn_type', 'policy', 'platform'];

    //型号是否存在
    public static function getPnTypeData($pname){

        return BaseModel::getDbData(
            [
                'table'=>self::$table[0],
                'where' => ['pname' => $pname],
            ]
        );

    }

    //获取型号的策略状态
    public static function getGeneratedPolicy($pname, $flag){

        return BaseModel::getDbData(
            [
                'table'=>self::$table[0],
                'where' => ['pname' =>$pname,'flag' => $flag],
            ]
        );
    }

    //初始化策略
    public static function  initializePolicy($data){

        return BaseModel::addData([
            'table' => self::$table[1],
            'data' => $data,
        ]);
    }


    //pn_type 更新flag
    public  static function updateTnTypeFlagStatus($pnumber){
       return BaseModel::saveData([
            'table' => ProductPolicyModel::$table[0],
            'where' => ['pnumber' => $pnumber],
            'data' => ['flag' => 1]
        ]);
    }

    //查询型号对应料号生成的策略
    public static  function getPolicyListData($pnumberStr){
        //p($pnumberStr);die;
         return BaseModel::getDbData([
            'table' => ProductPolicyModel::$table[1],
            'where' => [
                'pnumber' => ['in', $pnumberStr],
                'status' => 1
            ]
        ]);
         //echo M()->getLastSql();die;
    }

    //通过参数获取渠道名字
    public static function getChannelName($param){

         return BaseModel::getDbData([

            'table' => ProductPolicyModel::$table[2],
            'where' => ['platform' => $param],
            'fields' => 'platform_name'
        ], false);

    }

    //模糊搜索
    public static function getDimSearchData($pname){

        return [ 'data' => BaseModel::getDbData([

            'table' => self::$table[0],
            'fields' => ['pname'],
            'where' => [ 'pname' => ['like', "%".$pname."%"]  ],

        ]), 'field' =>'pname'];

    }

    //搜索
    public static function searchPnameShowPolicy($pname, $tag=0){

        return BaseModel::getDbData([
            'table' => self::$table[0],
            'fields' => ['pnumber'],
            'where' => !$tag ? [ 'pname' => ['like', "%".$pname."%"]]:'',

        ]);
    }

    //修改兑换平台策略
    public static function updateExchangePlatformPolicy($policyId, $flag){

        return BaseModel::saveData([
            'table' => self::$table[1],
            'where' => [ 'id' => $policyId],
            'data' => ['flag' => $flag]

        ]);
    }


}