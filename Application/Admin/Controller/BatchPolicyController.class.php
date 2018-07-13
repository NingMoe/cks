<?php
namespace Admin\Controller;

use Admin\Model\BaseModel;
use Admin\Model\BatchPolicyModel;

/**
 * 策略管理
 * @author observerstar
 *
 */
class BatchPolicyController extends BaseController
{
    //策略类型：1-料号策略；2-出货时间策略；3-激活时间策略；4-兑换平台策略；5-销售渠道策略

    public function test()
    {
        echo 'aaa';
        $policies = M('policy')->where([
            'policy_type' => 2,
            'status' => 1
        ])->order("pnumber desc")->select();
        $conflict = BaseModel::checkPolicyTime($policies, '2018-07-11 15:53:20', '2018-07-12 15:53:20');
        foreach ($conflict as $item) {
            $id = $item['id'];
            echo $id . '\n';
        }
        p($conflict);die();
    }

    //出货时间策略
    public function shippingTime()
    {
        $policies = BatchPolicyModel::getPolicyListData(2, 'pnumber');
        $this->assign('policies', json_encode($policies));
        $this->display();
    }

    //提交批量更新出货时间策略
    public function batchModifyShipingSubmit()
    {
        $policies = $_POST['policies'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $conflict = BaseModel::checkPolicyTime($policies, $start, $end);
        if(empty($conflict))
        {
            $value = $_POST['policy_value'];
            //批量增加出货时间策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 2,
                    'policy_value'=> $value,
                    'start_time'=> $start,
                    'end_time'=> $end,
                );
            }
            M('policy')->addAll($dataList);
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行出货时间策略修改
    public function batchModifyShipingHard()
    {
        $policies = $_POST['policies'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $conflict = BaseModel::checkPolicyTime($policies, $start, $end);
        //删除冲突的时间策略，状态改为已删除
        foreach ($conflict as $item) {
            $id = $item['id'];
            M('policy')->where("id = $id")->setField('status', 0);
        }

        //批量增加出货时间策略
        $pnumbers = array_unique(array_column($policies, 'pnumber'));
        $value = $_POST['policy_value'];
        foreach ($pnumbers as $pnumber) {
            $dataList[] = array(
                'pnumber'=>$pnumber,//去重
                'policy_type'=> 2,
                'policy_value'=> $value,
                'start_time'=> $start,
                'end_time'=> $end,
            );
        }
        $res = M('policy')->addAll($dataList);
        //TODO 记录日志
        if($res)$this->ajaxReturn(['status' => 1, 'info' => '操作成功']);
    }

    //激活时间策略
    public function activationTime()
    {
        $this->assign('policies', BatchPolicyModel::getPolicyListData(3, 'pnumber'));
        $this->display();
    }

    //提交批量更新激活时间策略
    public function batchModifyActivationSubmit()
    {
        $policies = $_POST['policies'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $conflict = BaseModel::checkPolicyTime($policies, $start, $end);
        if(empty($conflict))
        {
            $value = $_POST['policy_value'];
            //批量增加激活时间策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));
            foreach ($pnumbers as $pnumber) {
                $dataList[] = array(
                    'pnumber'=>$pnumber,//去重
                    'policy_type'=> 3,
                    'policy_value'=> $value,
                    'start_time'=> $start,
                    'end_time'=> $end,
                );
            }
            M('policy')->addAll($dataList);
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行激活时间策略修改
    public function batchModifyActivationHard()
    {
        $policies = $_POST['policies'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $conflict = BaseModel::checkPolicyTime($policies, $start, $end);
        //删除冲突的时间策略，状态改为已删除
        foreach ($conflict as $item) {
            $id = $item['id'];
            M('policy')->where("id = $id")->setField('status', 0);
        }

        //批量增加激活时间策略
        $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
        $value = $_POST['policy_value'];
        foreach ($pnumbers as $pnumber) {
            $dataList[] = array(
                'pnumber'=>$pnumber,
                'policy_type'=> 3,
                'policy_value'=> $value,
                'start_time'=> $start,
                'end_time'=> $end,
            );
        }
        $res = M('policy')->addAll($dataList);
        //TODO 记录日志
        if($res)$this->ajaxReturn(['status' => 1, 'info' => '操作成功']);
    }

    //兑换平台策略
    public function exchangePlatform()
    {
        $this->assign('policies', BatchPolicyModel::getPolicyListData(4, 'pnumber'));
        $this->display();
    }

    //检测平台冲突
    public static function checkPlatform($policies, $platform)
    {
        $conflict = [];
        foreach ($policies as $policy){
            if ($policy['platform'] == $platform) {
                array_push($conflict, $policy);
            }
        }

        return $conflict;
    }

    //提交批量更新兑换平台策略
    public function batchModifyPlatformSubmit()
    {
        $policies = $_POST['policies'];
        $platform = $_POST['platform'];
        $conflict = $this->checkPlatform($policies, $platform);
        if(empty($conflict))
        {
            $value = $_POST['policy_value'];
            //批量增加兑换平台策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 4,
                    'policy_value'=> $value,
                    'platform'=> $platform,
                );
            }
            M('policy')->addAll($dataList);
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行激活兑换平台策略修改
    public function batchModifyPlatformHard()
    {
        $policies = $_POST['policies'];
        $platform = $_POST['platform'];
        $conflict = $this->checkPlatform($policies, $platform);
        //删除冲突的平台策略，状态改为已删除
        foreach ($conflict as $item) {
            $id = $item['id'];
            M('policy')->where("id = $id")->setField('status', 0);
        }

        //批量增加平台策略
        $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
        $value = $_POST['policy_value'];
        foreach ($pnumbers as $pnumber) {
            $dataList[] = array(
                'pnumber'=>$pnumber,
                'policy_type'=> 4,
                'policy_value'=> $value,
                'platform'=> $platform,
            );
        }
        $res = M('policy')->addAll($dataList);
        //TODO 记录日志
        if($res)$this->ajaxReturn(['status' => 1, 'info' => '操作成功']);
    }

    //客户渠道策略
    public function customerChannel()
    {
        $this->assign('policies', BatchPolicyModel::getPolicyListData(5, 'pnumber'));
        $this->display();
    }

    //检测客户渠道冲突
    public static function checkChannel($policies, $channel)
    {
        $conflict = [];
        foreach ($policies as $policy){
            if ($policy['channel'] == $channel) {
                array_push($conflict, $policy);
            }
        }

        return $conflict;
    }

    //提交批量更新客户渠道策略
    public function batchModifyChannelSubmit()
    {
        $policies = $_POST['policies'];
        $channel = $_POST['channel'];
        $conflict = $this->checkChannel($policies, $channel);
        if(empty($conflict))
        {
            $value = $_POST['policy_value'];
            //批量增加客户渠道策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 5,
                    'policy_value'=> $value,
                    'channel'=> $channel,
                );
            }
            M('policy')->addAll($dataList);
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行激活客户渠道策略修改
    public function batchModifyChannelHard()
    {
        $policies = $_POST['policies'];
        $channel = $_POST['channel'];
        $conflict = $this->checkChannel($policies, $channel);
        //删除冲突的平台策略，状态改为已删除
        foreach ($conflict as $item) {
            $id = $item['id'];
            M('policy')->where("id = $id")->setField('status', 0);
        }

        //批量增加平台策略
        $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
        $value = $_POST['policy_value'];
        foreach ($pnumbers as $pnumber) {
            $dataList[] = array(
                'pnumber'=>$pnumber,
                'policy_type'=> 5,
                'policy_value'=> $value,
                'channel'=> $channel,
            );
        }
        $res = M('policy')->addAll($dataList);
        //TODO 记录日志
        if($res)$this->ajaxReturn(['status' => 1, 'info' => '操作成功']);
    }

}