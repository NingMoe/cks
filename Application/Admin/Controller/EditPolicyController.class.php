<?php
/**
 * Created by PhpStorm.
 * User: jql
 * Date: 12/07/2018
 * Time: 18:34
 */

namespace Admin\Controller;

use Admin\Model\BaseModel;

class EditPolicyController extends BaseController
{
    public function test()
    {
        $this->display();
    }

    public function edit()
    {
        $pnumber = I('get.pnumber');
        $policies = M('policy')->join('left join platform on policy.platform = platform.platform')
            ->where(['pnumber' => $pnumber, 'status' => 1])
            ->field(['policy.id', 'pnumber', 'policy_type', 'policy_value', 'start_time', 'end_time',
                'policy.platform', 'platform_name', 'flag', 'channel', 'rate'])
            ->select();
        $basicPolicies = array_values(array_filter($policies, function ($policy) {
            return $policy['policy_type'] == 1;
        }));
        $basicPolicy = $basicPolicies[0];
        $outTimePolicies = array_values(array_filter($policies, function ($policy) {
            return $policy['policy_type'] == 2;
        }));
        $activePolicies = array_values(array_filter($policies, function ($policy) {
            return $policy['policy_type'] == 3;
        }));
        $platformPolicies = array_values(array_filter($policies, function ($policy) {
            return $policy['policy_type'] == 4;
        }));
        $channelPolicies = array_values(array_filter($policies, function ($policy) {
            return $policy['policy_type'] == 5;
        }));

        $platform_list = M('platform')->select();
        $this->assign('basicPolicy', json_encode($basicPolicy));
        $this->assign('outTimePolicies', json_encode($outTimePolicies));
        $this->assign('activePolicies', json_encode($activePolicies));
        $this->assign('platformPolicies', json_encode($platformPolicies));
        $this->assign('channelPolicies', json_encode($channelPolicies));
        $this->assign('platform_list', json_encode($platform_list));
        $this->display();
    }

    public function addPolicy()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        if ($policy['policy_value'] <= 0) {
            $this->error('策略子必须大于0');
        }
        $policies = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'status' => 1])->select();
        if ($policy['policy_type'] == 2) {
            $res = BaseModel::checkPolicyTime($policies, $policy['start_time'], $policy['end_time']);
            if (count($res) > 0) {
                $this->error('时间冲突!');
            } else {
                $id = M('policy')->add($policy);
                BaseModel::log([$id], 'policy_id', '新增策略', 'insert');
                $policy = M('policy')->where(['id' => $id])->find();
                $this->success('添加成功！', '', $policy);
            }
        } elseif ($policy['policy_type'] == 3) {
            $res = BaseModel::checkPolicyTime($policies, $policy['start_time'], $policy['end_time']);
            if (count($res) > 0) {
                $this->error('时间冲突!');
            } else {
                $id = M('policy')->add($policy);
                $policy = M('policy')->where(['id' => $id])->find();
                BaseModel::log([$id], 'policy_id', '新增策略', 'insert');
                $this->success('添加成功！', '', $policy);
            }
        } elseif ($policy['policy_type'] == 4) {
            $res = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'platform' => $policy['platform'], 'status' => 1])->find();
            if (count($res) > 0) {
                $this->error('兑换渠道已存在!');
            } else {
                $no_flag = array('7-1','7-2');
                if (in_array($policy['platform'], $no_flag)) {
                    $policy['flag'] = 0;
                }
                
                $id = M('policy')->add($policy);
                BaseModel::log([$id], 'policy_id', '新增策略', 'insert');
                $policy = M('policy')->join('left join platform on policy.platform = platform.platform')
                    ->where(['policy.id' => $id])
                    ->field(['policy.id', 'pnumber', 'policy_type', 'policy_value', 'start_time', 'end_time',
                        'policy.platform', 'platform_name', 'flag', 'channel', 'rate'])
                    ->find();
                $this->success('添加成功！', '', $policy);
            }
        } elseif ($policy['policy_type'] == 5) {
            $res = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'channel' => $policy['channel'], 'status' => 1])->find();
            if (count($res) > 0) {
                $this->error('客户渠道已存在!');
            } else {
                $id = M('policy')->add($policy);
                BaseModel::log([$id], 'policy_id', '新增策略', 'insert');
                $policy = M('policy')->where(['id' => $id])->find();
                $this->success('添加成功！', '', $policy);
            }
        }
        else {
            $this->error('策略类型错误');
        }
    }

    public function changePolicy()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        if ($policy['policy_value'] <= 0) {
            $this->error('策略子必须大于0');
        }
        $policies = M('policy')->where("pnumber = {$policy['pnumber']} and policy_type = {$policy['policy_type']} and status = 1 and id <> {$policy['id']}")->select();
        if ($policy['policy_type'] == 1) {
            M('policy')->where(['id' => $policy['id']])->save($policy);
            BaseModel::log([$policy['id']], 'policy_id', '修改策略', 'update');
            $this->success('修改成功！');
        } elseif ($policy['policy_type'] == 2) {
            $res = BaseModel::checkPolicyTime($policies, $policy['start_time'], $policy['end_time']);
            if (count($res) > 0) {
                $this->error('时间冲突!');
            } else {
                M('policy')->where(['id' => $policy['id']])->save($policy);
                BaseModel::log([$policy['id']], 'policy_id', '修改策略', 'update');
                $this->success('修改成功！');
            }
        } elseif ($policy['policy_type'] == 3) {
            $res = BaseModel::checkPolicyTime($policies, $policy['start_time'], $policy['end_time']);
            if (count($res) > 0) {
                $this->error('时间冲突!');
            } else {
                M('policy')->where(['id' => $policy['id']])->save($policy);
                BaseModel::log([$policy['id']], 'policy_id', '修改策略', 'update');
                $this->success('修改成功！');
            }
        } elseif ($policy['policy_type'] == 4) {
            $where = ['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'platform' => $policy['platform'], 'status' => 1];
            $where['id']=array('neq',$policy['id']);
            $res = M('policy')->where($where)->find();
            if (count($res) > 0) {
                $this->error('兑换渠道已存在!');
            } else {
                M('policy')->where(['id' => $policy['id']])->save($policy);
                BaseModel::log([$policy['id']], 'policy_id', '修改策略', 'update');
                $this->success('修改成功！');
            }
            
        } elseif ($policy['policy_type'] == 5) {
            $where = ['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'channel' => $policy['channel'], 'status' => 1];
            $where['id']=array('neq',$policy['id']);
            $res = M('policy')->where($where)->find();
            if (count($res) > 0) {
                $this->error('客户渠道已存在!');
            } else {
                M('policy')->where(['id' => $policy['id']])->save($policy);
                BaseModel::log([$policy['id']], 'policy_id', '修改策略', 'update');
                $this->success('修改成功！');
            }
            
        }else {
            $this->error('策略类型错误');
        }
    }

    public function removePolicy()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        $policies = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'status' => 1])->select();
        if (count($policies) == 1) {
            $this->error('策略数不能小于1');
        }
        M('policy')->where(['id' => $policy['id']])->setField('status', 0);
        BaseModel::log([$policy['id']], 'policy_id', '删除策略', 'delete');
        $this->success('删除成功！');
    }

    public function getPolicies()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        $policies = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'status' => 1])->select();
        $this->success($policies);
    }

    public function queryChannel(){
        $channel = I('channel');
        $where['channel_name']=array('like',"%$channel%");
        $res = M('channel')->field('channel_name as value')->where($where)->select();
        exit(json_encode($res,JSON_UNESCAPED_UNICODE));
    }
}