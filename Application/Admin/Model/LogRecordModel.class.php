<?php
namespace Admin\Model;

/**
 *日志管理
 *
 */
class LogRecordModel extends  BaseModel
{
    public static $table = ['oplog' ];

    /**
     * 记录操作日志，单条操作记录策略id，批量操作记录料号
     * @param $object_ids array 料号或者策略id数组
     * @param $object_type string 操作对象类型，批量操作时为pnumber，单个操作为policy_id
     * @param $action string 用户操作如：生成策略，批量添加出货时间
     * @param $sql_type string 数据库操作类型：insert update delete
     */

    public static function log($table, $policyIds, $pnumber, $action, $sqlType, $beforeData='', $afterData=''){
        $dataList = [];
        foreach ($policyIds as $objectId) {

            $dataList[] = array(
                'table' => $table,
                'operator' => self::username(),
                'policy_id' => $objectId,
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