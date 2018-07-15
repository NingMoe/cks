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
        $this->assign('basicPolicy', json_encode($basicPolicy));
        $this->assign('outTimePolicies', json_encode($outTimePolicies));
        $this->assign('activePolicies', json_encode($activePolicies));
        $this->assign('platformPolicies', json_encode($platformPolicies));
        $this->assign('channelPolicies', json_encode($channelPolicies));
        $this->display();
    }

    public function addPolicy()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        $policies = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'status' => 1])->select();
        if ($policy['policy_type'] == 2) {
            $res = BaseModel::checkPolicyTime($policies, $policy['start_time'], $policy['end_time']);
            if (count($res) > 0) {
                $this->error('时间冲突!');
            } else {
                $id = M('policy')->add($policy);
                $policy = M('policy')->where(['id' => $id])->find();
                $this->success('添加成功！', '', $policy);
            }
        } else {
            $this->error('策略类型错误');
        }
    }

    public function changePolicy()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        $policies = M('policy')->where("pnumber = {$policy['pnumber']} and policy_type = {$policy['policy_type']} and status = 1 and id <> {$policy['id']}")->select();
        if ($policy['policy_type'] == 1) {
            M('policy')->where(['id' => $policy['id']])->save($policy);
            $this->success('修改成功！');
        } elseif ($policy['policy_type'] == 2) {
            $res = BaseModel::checkPolicyTime($policies, $policy['start_time'], $policy['end_time']);
            if (count($res) > 0) {
                $this->error('时间冲突!');
            } else {
                M('policy')->where(['id' => $policy['id']])->save($policy);
                $this->success('修改成功！');
            }
        } else {
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
        $this->success('删除成功！');
    }

    public function getPolicies()
    {
        $policy = json_decode(file_get_contents('php://input'), true);
        $policies = M('policy')->where(['pnumber' => $policy['pnumber'], 'policy_type' => $policy['policy_type'], 'status' => 1])->select();
        $this->success($policies);
    }
}