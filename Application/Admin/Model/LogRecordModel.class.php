<?php
namespace Admin\Model;

/**
 *日志管理
 *
 */
class LogRecordModel extends  BaseModel
{
    public static $table = ['oplog'];

    public static function log($table, $policyIds, $pnumber, $action, $sqlType, $beforeData='', $afterData=''){
        $dataList = [];
        foreach ($policyIds as $policyId) {

            $dataList[] = array(
                'table' => $table,
                'operator' => self::username(),
                'policy_id' => $policyId,
                'action' => $action,
                'pnumber' => $pnumber,
                'sql_type' => $sqlType,
                'update_time' => date('Y-m-d H:i:s', time()),
                'before_data' => $beforeData,
                'after_data' => $afterData
            );

        }


        return M(self::$table[0])->addAll($dataList);
    }

    //日志添加单条
    public static function logAddRow($table, $policyId, $pnumber, $action, $sqlType, $beforeData='', $afterData=''){

        return BaseModel::addData([

            'table' => self::$table[0],

            'data' => [
                'table' => $table,
                'operator' => self::username(),
                'policy_id' => $policyId,
                'action' => $action,
                'pnumber' => $pnumber,
                'sql_type' => $sqlType,
                'update_time' => date('Y-m-d H:i:s', time()),
                'before_data' => $beforeData,
                'after_data' => $afterData

            ]
        ]);

    }


}