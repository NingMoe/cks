<?php
namespace Admin\Controller;
use Admin\Model\BaseModel;
use Admin\Model\ProductPolicyModel;

/**
 * 策略管理
 * @author jan
 *
 */
class ProductPolicyController extends BaseController
{

    public $label = ['search', 'generate'];
    public $statusMsg = [
        ['status' => 0, 'msg' =>  '该产品不存在'],
        ['status' => 1, 'msg' =>  '请注意该产品型号存在新料号策略生成'],
        ['status' => 2, 'msg' =>  '策略生成成功'],
        ['status' => 3, 'msg' =>  '策略生成失败,请重新生成'],
        ['status' => 4, 'msg' =>  '已存在策略，请进行搜索']
    ];

    public function index() {

        if($_GET['pname'])

            $this->assign(['data' =>$this->getPolicyListData(ProductPolicyModel::getPnTypeData($_GET['pname'])),'pname' => $_GET['pname']]);

        $this->display();

    }


    public function  policyRuleFind(){
        $panme = I('post.pname');

        //搜索是否存在该型号
        if(!ProductPolicyModel::getPnTypeData($panme)) $this->ajaxReturn($this->statusMsg[0]);

        $finishedPnumber = ProductPolicyModel::getGeneratedPolicy($panme, 1); //已经生成的料号策略
        $planPnumber = ProductPolicyModel::getGeneratedPolicy($panme, 0); //准备生成的料号策略

        if(!$planPnumber) $this->ajaxReturn($this->statusMsg[4]);

        //有型号 将料号生成策略 flag=0生成 flag=1已经生成
        //是否有已经生成的料号策略
        if($finishedPnumber)

            if($planPnumber)

                $this->ajaxReturn($this->statusMsg[1]);
            else
                $this->ajaxReturn(array_merge($this->statusMsg[2], ['data' => $this->getPolicyListData($finishedPnumber)]));

        else

            $this->generatingStrategy($planPnumber); //将该型号的产品料号进行策略生成

    }

    //生成策略--初始化 1个料号 九条策略
    public function generatingStrategy($pnumber = []){

        if(I('post.panme'))
            $pnumber = ProductPolicyModel::getGeneratedPolicy(I('post.panme'), 0); //准备生成的料号策略


        foreach ($pnumber as $val){

            foreach ($this->insertData($val['pnumber']) as $va)
                $initRes = ProductPolicyModel::initializePolicy($va);//初始化策略

            $uptRes = ProductPolicyModel::updateTnTypeFlagStatus($val['pnumber']);//更新tn_type表 flag
            //策略初始化日志
            ProductPolicyModel::policyLogRecord($this->logRecordParm($val['pnumber'], 'pnumber', '初始化策略', 'insert'));
            //更新 tn_type 写入日志
            ProductPolicyModel::policyLogRecord($this->logRecordParm($val['pnumber'], 'pnumber', '策略初始化完成修改tn_type.flag', 'update'));
        }
        //dump($initRes);
        //dump($uptRes);die;
        if($initRes && $uptRes)$this->ajaxReturn(array_merge($this->statusMsg[2], ['data' => $this->getPolicyListData($pnumber)]));

        else $this->ajaxReturn($this->statusMsg[3]);

    }

    //查询该型号生成的策略
    public function getPolicyListData($pnumber){

        return self::structureShowObj(ProductPolicyModel::getPolicyListData(implode(',', array_column($pnumber, 'pnumber'))));

    }

    //构造显示对象
    public function structureShowObj($arrData){

        $showArr = [];
        foreach ($arrData as $val){

            if($val['policy_type'] == 1)
                $showArr[$val['pnumber']][1][] = ['policy_value'=>$val['policy_value']];

            if($val['policy_type'] == 2)
                $showArr[$val['pnumber']][2][] = ['id'=>$val['id'], 'start_time'=>$val['start_time'],'end_time'=>$val['end_time'],'policy_value'=>$val['policy_value']];

            if($val['policy_type'] == 3)
                $showArr[$val['pnumber']][3][] = ['id'=>$val['id'], 'start_time'=>$val['start_time'],'end_time' =>$val['end_time'],'policy_value' => $val['policy_value']];

            if($val['policy_type'] == 4)
                $showArr[$val['pnumber']][4][] = ['id'=>$val['id'], 'platform'=>$val['platform'],'platform_name' =>ProductPolicyModel::getChannelName($val['platform'])['platform_name'],'flag'=>$val['flag'],'policy_value' => $val['policy_value']];

            if($val['policy_type'] == 5)
                $showArr[$val['pnumber']][5][] = ['id'=>$val['id'], 'channel'=> $val['channel'], 'policy_value' => $val['policy_value']];

            if($val['policy_type'] == 6)
                $showArr[$val['pnumber']][6][] = ['id'=>$val['id'], 'policy_value' => $val['policy_value']];
        }

        return $showArr;
    }

    //生成策略数据构造
    public function insertData($pnumber){

        return [
            ['pnumber' => $pnumber, 'policy_type' => 1 ],
            ['pnumber' => $pnumber, 'policy_type' => 2, 'start_time' => date('Y-m-d H:i:s', time()),'end_time' =>  date('Y', time()) + 2 . '-' . date('m-d H:i:s'),],
            ['pnumber' => $pnumber, 'policy_type' => 3, 'start_time' => date('Y-m-d H:i:s', time()),'end_time' =>  date('Y', time()) + 2 . '-' . date('m-d H:i:s'),],
            ['pnumber' => $pnumber, 'policy_type' => 5],
            ['pnumber' => $pnumber, 'policy_type' => 6, 'policy_value' => 0],
            ['pnumber' => $pnumber, 'policy_type' => 4, 'platform' => '1-1', 'flag' => 1, 'rate' => 100],
            ['pnumber' => $pnumber, 'policy_type' => 4, 'platform' => '1-2', 'flag' => 1],
            ['pnumber' => $pnumber, 'policy_type' => 4, 'platform' => '1-3', 'flag' => 1],
            ['pnumber' => $pnumber, 'policy_type' => 4, 'platform' => '1-4', 'flag' => 1],
            ['pnumber' => $pnumber, 'policy_type' => 4, 'platform' => '7-1', 'flag' => 0],
            ['pnumber' => $pnumber, 'policy_type' => 4, 'platform' => '7-2', 'flag' => 0],
        ];

    }

    //模糊搜索
    public function dimSearch(){

        $this->ajaxReturn(ProductPolicyModel::getDimSearchData(I('post.pname')));

    }

    //搜索
    public function searchPnameShowPolicy(){
        //p($this->getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))));die;

        $this->assign(['data' =>$this->getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))),'pname' => $_GET['pname']]);
        //p($this->getPolicyListData(ProductPolicyModel::searchPnameShowPolicy(I('get.pname'))));die;

        $this->display('index');
    }

    //修改兑换平台策略
    public function updateExchangePlatformPolicy(){

        ProductPolicyModel::updateExchangePlatformPolicy(I('post.policyId'), I('post.flag'));//修改

        //更新 policyflag 写入日志
        ProductPolicyModel::policyLogRecord($this->logRecordParm(I('post.policyId'), 'policy_id', '修改兑换平台是否沿用前策略', 'update'));

        $this->ajaxReturn(1);

    }

    //日志参数
    private function logRecordParm($object_id, $object_type, $action, $sql_type){

        return [
            'operator' => BaseModel::username(),
            'object_id' => $object_id,
            'object_type' => $object_type,
            'action' => $action,
            'sql_type' => $sql_type,
            'created_at' => date('Y-m-d H:i:s', time()),
        ];
    }


}