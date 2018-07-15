<?php
namespace Admin\Controller;

use Admin\Model\BaseModel;
use Admin\Model\ProductPolicyModel;

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
        $policies = M()->query('select distinct pnumber from policy');
        foreach ($policies as $policy) {
            $dataList[] = array(
                'pnumber'=>$policy['pnumber'],
                'policy_type'=> 2,
            );
        }

        p($dataList);die();
    }

    //出货时间策略
    public function shippingTime()
    {
        $this->display();
    }


    //提交批量更新出货时间策略
    public function batchModifyShipingSubmit()
    {
        $policyIds = $_POST['policy_ids'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $value = $_POST['policy_value'];
        //全选所有型号
        if ($_POST['tag'] == 1) {
            //删除所有出货时间策略，状态改为已删除
            M('policy')->where(['policy_type' => 2, 'status' => 1])->setField('status', 0);

            //批量增加出货时间策略
            $policies = M()->query('select distinct pnumber from policy');
            foreach ($policies as $policy) {
                $dataList[] = array(
                    'pnumber'=>$policy['pnumber'],
                    'policy_type'=> 2,
                    'policy_value'=> $value,
                    'start_time'=> $start,
                    'end_time'=> $end,
                );
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        }

        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkPolicyTime($policies, $start, $end);
        if(empty($conflict))
        {
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
            $res = M('policy')->addAll($dataList);
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
            $this->ajaxReturn(['status' => 1, 'msg' =>  '时间冲突', 'conflict' => $conflict]);
        }
    }

    //强制执行出货时间策略修改
    public function batchModifyShipingHard()
    {
        $policyIds = $_POST['policy_ids'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
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
        if($res) {
            $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
        }
    }

    //激活时间策略
    public function activationTime()
    {
        $this->display();
    }

    //提交批量更新激活时间策略
    public function batchModifyActivationSubmit()
    {
        $policyIds = $_POST['policy_ids'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $value = $_POST['policy_value'];
        //全选所有型号
        if ($_POST['tag'] == 1) {
            //删除所有激活时间策略，状态改为已删除
            M('policy')->where(['policy_type' => 3, 'status' => 1])->setField('status', 0);

            //批量增加激活时间策略
            $policies = M()->query('select distinct pnumber from policy');
            foreach ($policies as $policy) {
                $dataList[] = array(
                    'pnumber'=>$policy['pnumber'],
                    'policy_type'=> 3,
                    'policy_value'=> $value,
                    'start_time'=> $start,
                    'end_time'=> $end,
                );
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        }

        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkPolicyTime($policies, $start, $end);
        if(empty($conflict))
        {
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
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行激活时间策略修改
    public function batchModifyActivationHard()
    {
        $policyIds = $_POST['policy_ids'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
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
        if($res) {
            $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
        }
    }

    //兑换平台策略
    public function exchangePlatform()
    {
        $this->display();
    }

    //提交批量更新兑换平台策略
    public function batchModifyPlatformSubmit()
    {
        $policyIds = $_POST['policy_ids'];
        $platform = $_POST['platform'];
        $value = $_POST['policy_value'];
        //全选所有型号
        if ($_POST['tag'] == 1) {
            //删除所有激活时间策略，状态改为已删除
            M('policy')->where(['policy_type' => 4, 'status' => 1])->setField('status', 0);

            //批量增加激活时间策略
            $policies = M()->query('select distinct pnumber from policy');
            foreach ($policies as $policy) {
                $dataList[] = array(
                    'pnumber'=>$policy['pnumber'],
                    'policy_type'=> 4,
                    'policy_value'=> $value,
                    'platform'=> $platform,
                    //TODO flag默认值
                );
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        }

        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkPlatform($policies, $platform);
        if(empty($conflict))
        {
            //批量增加兑换平台策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                //TODO 一组还是多组策略，多组使用双层循环
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 4,
                    'policy_value'=> $value,
                    'platform'=> $platform,
                );
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行激活兑换平台策略修改
    public function batchModifyPlatformHard()
    {
        $policyIds = $_POST['policy_ids'];
        $platform = $_POST['platform'];
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkPlatform($policies, $platform);
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
        if($res) {
            $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
        }
    }

    //客户渠道策略
    public function customerChannel()
    {
        $this->display();
    }



    //提交批量更新客户渠道策略
    public function batchModifyChannelSubmit()
    {
        $policyIds = $_POST['policy_ids'];
        $channel = $_POST['channel'];
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkChannel($policies, $channel);
        if(empty($conflict))
        {
            $value = $_POST['policy_value'];
            //批量增加客户渠道策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                //TODO 一组还是多组策略，多组使用双层循环
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 5,
                    'policy_value'=> $value,
                    'channel'=> $channel,
                );
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
        }
    }

    //强制执行激活客户渠道策略修改
    public function batchModifyChannelHard()
    {
        $policyIds = $_POST['policy_ids'];
        $channel = $_POST['channel'];
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkChannel($policies, $channel);
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
        if($res) {
            $this->ajaxReturn(['status' => 0, 'info' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'info' => '操作失败']);
        }
    }

    //模糊搜索
    public function dimSearch(){
        $this->ajaxReturn(ProductPolicyModel::getDimSearchData(I('post.pname')));
    }

    //搜索
    public function searchPnameShowPolicy(){
        //p($this->getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))));die;

        //echo 121;die;
        //p(ProductPolicyController::getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))));die;
        $this->assign(['data' => ProductPolicyController::getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))),'pname' => $_GET['pname']]);

        $this->display('shippingTime');
    }

}