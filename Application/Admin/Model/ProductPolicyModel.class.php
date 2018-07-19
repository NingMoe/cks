<?php
namespace Admin\Model;



class ProductPolicyModel extends  BaseModel{

    public static $table = ['pn_type', 'policy', 'platform', 'channel', 'oplog' ];

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

    //模糊搜索 产品型号
    public static function getDimSearchData($pname){

        return [ 'data' => BaseModel::getDbData([

            'table' => self::$table[0],
            'fields' => ['pname'],
            'where' => [ 'pname' => ['like', "%".$pname."%"]  ],

        ], true, true), 'field' =>'pname'];

    }


    //搜索料号
    public static function searchPnameShowPolicy($pname){

        return BaseModel::getDbData([
            'table' => self::$table[0],
            'fields' => ['pnumber'],
            'where' => [ 'pname' => $pname],
        ]);
    }

    //模糊搜索产品渠道
    public static function getDimSearchChannelNameData($channelName){

        return [ 'data' => BaseModel::getDbData([

            'table' => self::$table[3],
            'fields' => ['channel_name'],
            'where' => [ 'channel_name' => ['like', "%".$channelName."%"]  ],

        ]),'field' => 'channel_name'];
    }

    //模糊搜索兑换平台
    public static function getDimSearchPlatformNameData($platformName){

        return [ 'data' => BaseModel::getDbData([

            'table' => self::$table[2],
            'fields' => ['platform_name'],
            'where' => [ 'platform_name' => ['like', "%".$platformName."%"]  ],

        ]),'field' => 'platform_name'];
    }

    //修改兑换平台策略
    public static function updateExchangePlatformPolicy($policyId, $flag){

        return BaseModel::saveData([
            'table' => self::$table[1],
            'where' => [ 'id' => $policyId],
            'data' => ['flag' => $flag]

        ]);
    }

    //日志记录
    public static function policyLogRecord($data){

        return BaseModel::addData([

            'table' => self::$table[4],
            'data' => $data,
        ]);
    }


}