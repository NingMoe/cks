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
    //策略类型：1-料号策略；2-出货时间策略；3-激活时间策略；4-兑换平台策略；5-客户渠道策略
    public static $innerPlatforms = ['1-1', '1-2', '1-3', '1-4'];

    public $policyType = [ 2=>'shippingTime', 3=>'activationTime', 4=>'exchangePlatform', 5=>'customerChannel'];

    public function test()
    {
        $count = M('policy')->group('pnumber')->select();
        p($count);die();
        $channels = [
            ['JD', 1.2],
            ['Suning', 1.0],
            ['Phicomm', 1.5],
        ];
        $columes = array_column($channels, 0);
        foreach ($channels as $channel) {
            echo $channel['0'];
            echo $channel['1'];
        }
        p($columes);die();
    }

    //出货时间策略
    public function shippingTime()
    {
        $this->display();
    }


    //提交批量更新出货时间策略
    public function batchModifyShipingSubmit()
    {
        //p($_POST);die;
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
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
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
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
            $this->ajaxReturn(['status' => 1, 'msg' =>  '时间冲突,您确认删除原有出货时间策略，新增出货时间策略吗？', 'conflict' => $conflict]);
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
            $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
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
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
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
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
            $this->ajaxReturn(['status' => 1, 'msg' => '时间冲突,您确认删除原有激活时间策略，新增激活时间策略吗？', 'conflict' => $conflict]);
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
            $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
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
        $platforms = $_POST['platforms'];//[platform, value]数组
        //全选所有型号
        if ($_POST['tag'] == 1) {
            //删除所有兑换平台策略，状态改为已删除
            M('policy')->where(['policy_type' => 4, 'status' => 1])->setField('status', 0);

            //批量增加激活时间策略
            $policies = M()->query('select distinct pnumber from policy');
            foreach ($policies as $policy) {
                //多组平台策略，双层循环
                foreach ($platforms as $platform) {
                    $dataList[] = array(
                        'pnumber'=>$policy['pnumber'],
                        'policy_type'=> 4,
                        'platform'=> $platform[0],
                        'policy_value'=> $platform[1],
                        'flag'=> in_array(static::$innerPlatforms, $platform[0]) ? 1 : 0,
                    );
                }
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        }

        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkPlatform($policies, array_column($platforms, 0));
        if(empty($conflict))
        {
            //批量增加兑换平台策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                //多组平台策略，双层循环
                foreach ($platforms as $platform) {
                    $dataList[] = array(
                        'pnumber'=>$pnumber,
                        'policy_type'=> 4,
                        'platform'=> $platform[0],
                        'policy_value'=> $platform[1],
                        'flag'=> in_array(static::$innerPlatforms, $platform[0]) ? 1 : 0,
                    );
                }
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
            $this->ajaxReturn(['status' => 1, 'msg' =>  '平台冲突,您确认删除原有兑换兑换平台策略，新增新兑换平台策略吗？', 'conflict' => $conflict]);
        }
    }

    //强制执行激活兑换平台策略修改
    public function batchModifyPlatformHard()
    {
        $policyIds = $_POST['policy_ids'];
        $platforms = $_POST['platforms'];//[platform, value]数组
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkPlatform($policies, array_column($platforms, 0));
        //删除冲突的平台策略，状态改为已删除
        foreach ($conflict as $item) {
            $id = $item['id'];
            M('policy')->where("id = $id")->setField('status', 0);
        }

        //批量增加平台策略
        $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
        foreach ($pnumbers as $pnumber) {
            //多组平台策略，双层循环
            foreach ($platforms as $platform) {
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 4,
                    'platform'=> $platform[0],
                    'policy_value'=> $platform[1],
                    'flag'=> in_array(static::$innerPlatforms, $platform[0]) ? 1 : 0,
                );
            }
        }
        $res = M('policy')->addAll($dataList);
        //TODO 记录日志
        if($res) {
            $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
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
        $channels = $_POST['channels'];//[channel, value]数组
        //全选所有型号
        if ($_POST['tag'] == 1) {
            //删除所有兑换平台策略，状态改为已删除
            M('policy')->where(['policy_type' => 5, 'status' => 1])->setField('status', 0);

            //批量增加激活时间策略
            $policies = M()->query('select distinct pnumber from policy');
            foreach ($policies as $policy) {
                //多组渠道策略，双层循环
                foreach ($channels as $channel) {
                    $dataList[] = array(
                        'pnumber'=>$policy['pnumber'],
                        'policy_type'=> 5,
                        'policy_value'=> $channel[1],
                        'channel'=> $channel[0],
                    );
                }
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        }

        //p($policyIds);die;
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkChannel($policies, array_column($channels, 0));
        //p($conflict);die;
        if(empty($conflict))
        {
            //批量增加客户渠道策略
            $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
            foreach ($pnumbers as $pnumber) {
                //多组渠道策略，多组使用双层循环
                foreach ($channels as $channel) {
                    $dataList[] = array(
                        'pnumber'=>$pnumber,
                        'policy_type'=> 5,
                        'policy_value'=> $channel[1],
                        'channel'=> $channel[0],
                    );
                }
            }
            $res = M('policy')->addAll($dataList);
            //TODO 记录日志
            if($res) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
            }
        } else {
            //跳转冲突提示框
            $this->ajaxReturn(['status' => 1, 'msg' =>  '渠道冲突,您确认删除原有客户渠道策略，新增客户渠道策略吗？', 'conflict' => $conflict]);
        }
    }

    //强制执行激活客户渠道策略修改
    public function batchModifyChannelHard()
    {
        $policyIds = $_POST['policy_ids'];
        $channels = $_POST['channels'];//(channel, value)数组
        $policies = M('policy')->where(['id' => ['in', implode(',', $policyIds)]])->select();
        $conflict = BaseModel::checkChannel($policies, array_column($channels, 0));
        //删除冲突的平台策略，状态改为已删除
        foreach ($conflict as $item) {
            $id = $item['id'];
            M('policy')->where("id = $id")->setField('status', 0);
        }

        //批量增加平台策略
        $pnumbers = array_unique(array_column($policies, 'pnumber'));//去重
        foreach ($pnumbers as $pnumber) {
            //多组渠道策略，多组使用双层循环
            foreach ($channels as $channel) {
                $dataList[] = array(
                    'pnumber'=>$pnumber,
                    'policy_type'=> 5,
                    'policy_value'=> $channel[1],
                    'channel'=> $channel[0],
                );
            }
        }
        $res = M('policy')->addAll($dataList);
        //TODO 记录日志
        if($res) {
            $this->ajaxReturn(['status' => 0, 'msg' => '操作成功']);
        } else {
            $this->ajaxReturn(['status' => -1, 'msg' => '操作失败']);
        }
    }

    //模糊搜索
    public function dimSearch(){
        $this->ajaxReturn(ProductPolicyModel::getDimSearchData(I('post.pname')));
    }

    //模糊搜索渠道
    public function dimSearchChannelName(){
        $this->ajaxReturn(ProductPolicyModel::getDimSearchChannelNameData(I('post.channelName')));
    }

    //搜索
    public function searchPnameShowPolicy(){
        //echo I('get.policyType');die;
        //p(ProductPolicyController::getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))));die;
        $this->assign(['data' => ProductPolicyController::getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))),'pname' => $_GET['pname']]);

       $this->display($this->policyType[I('get.policyType')]);
    }

}