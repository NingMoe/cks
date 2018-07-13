<?php
namespace Admin\Model;



class ProductPolicyModel extends  BaseModel{

    public static $table = ['pn_type', 'policy'];

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
            'where' => ['pnumber' => ['in', $pnumberStr]]
        ]);
         //echo M()->getLastSql();die;
    }


}