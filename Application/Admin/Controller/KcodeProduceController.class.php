<?php
namespace Admin\Controller;
use Admin\Model\CksManageModel;
use Admin\Model\KcodeProduceModel;
use Admin\Model\SystemKeysModel;
use Admin\Model\BaseModel;

/**
 *K码生成
 * @author jan
 *
 */
class KcodeProduceController extends BaseController
{

    public  $arr = [58, 59, 60, 61, 62, 63, 64, 73, 79, 91, 92, 93, 94, 95, 96, 108, 111];//去除ascii特殊字符
    public  $type = ['ph' => 8, 'BD' => 8, 'am' => 10 ]; //ph、am 明码暗码 bd绑定码
    public  $randStart = 50;//随机数开始
    public  $randEnd = 122;//随机数结束
    public  $expTitle = '导入数据'; //导出标题
    public  $sqlDir = './sql/';
    public  $expCellName =  [
                ['clearcd', '明码'],
                ['secretcd', '暗码'],
                ['hcode', '绑定码'],
                ['im_pnumber', '料号'],
                ['im_model', '产品名称'],
                ['im_staff', '导入人员'],
                ['im_time', '导入时间'],
                ['status', '状态'],
                ['pmoney', '金额'],
                ['close_time', '截止日期']
        ];//excel导出

    //列表
    public function  index(){
        //需要导入的页面显示字段
        $this->assign('kcodeImportFields', SystemKeysModel:: getSystemKeys('kcodeImportFields','key2 asc'));
        //p(SystemKeysModel:: getSystemKeys('kcodeImportFields','key2 asc'));die;

        //导入渠道策略选择
        //$this->assign('channelCheckBox', SystemKeysModel:: getSystemKeys('channelCheckBox','key2 asc'));

        //需要导入的比例页面显示
        //$this->assign('importRestrictions', SystemKeysModel:: getSystemKeys('importRestrictions','key2 asc'));

        //导入列表
        $this->assign('data', KcodeProduceModel:: getProductImportListData($_GET['page']));

        //时间
        //p(KcodeProduceModel:: getProductImportTime());
        $this->assign('imTime', KcodeProduceModel:: getProductImportTime());


        $this->display();
    }

    //模糊搜索
    public function dimSearch(){

        $this->ajaxReturn(CksManageModel::getDimSearchData(I('post.tag'), I('post.describe')));

    }

    //通过料号获取产品名称
    public function getPnameByPnumber(){

        $this->ajaxReturn(BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[0],
            'fields' => 'pname',
            'where' => ['pnumber' => I('post.pnumber')]
        ],false));

    }

    //导入LOAD DATA local INFILE
    public function imProductInitialData(){

        $pdata = json_decode($_POST['data'],true);
        $this->verifyPnumberByPname(trim($pdata['pnumber']), trim($pdata['pname']));//验证料号和名称是否对应
        $postData = $this->checkPost($pdata);//待添加数据

        set_time_limit(1000);
        ini_set ('memory_limit', '-1');

        if(!is_dir($this->sqlDir)){ //存储路径文件不存在就创建
            mkdir($this->sqlDir);
            chmod($this->sqlDir,0777);
        }

        $sqlFile = $this->sqlDir.time().'.sql';

        //$insertSqlFile = $_SERVER['DOCUMENT_ROOT'].ltrim($sqlFile,'.');

        $fhandler=fopen($sqlFile,'wb');

        if($fhandler) {
            $i = 0;
            while ($i < $pdata['number']){
                $sql = '';

                $i++;
                $postData['clearcd'] = 'ph'.$this->createKcode('ph');

                $postData['secretcd'] =$this->createKcode('am');

                if(trim($pdata['pname']) == 'N1' || trim($pdata['pname']) == 'N1M' || trim($pdata['pname']) == 'K3-D1')
                    $postData['hcode'] ='BD'.$this->createKcode('BD');

                $sql .= "{$postData['im_model']}\t{$postData['im_pnumber']}\t{$postData['im_time']}\t{$postData['im_staff']}\t{$postData['pmoney']}\t{$postData['close_time']}\t{$postData['status']}\t{$postData['readdress']}\t{$postData['clearcd']}\t{$postData['secretcd']}\t{$postData['hcode']}";

                fwrite($fhandler, $sql . "\r\n");
                unset($sql);
            }

            try {

            $pdoExecSql = "LOAD DATA local INFILE '".$_SERVER['DOCUMENT_ROOT'].ltrim($sqlFile,'.')."' INTO TABLE `relation` (`im_model`, `im_pnumber`, `im_time`, `im_staff`, `pmoney`, `close_time`, `status`, `readdress`,`clearcd`, `secretcd`, `hcode`);";

            $res = KcodeProduceModel::pdoConnect()->exec($pdoExecSql);

            if($res)$this->ajaxReturn(['status' => 1, 'info' => '导入成功']);
            //else $this->ajaxReturn(['status' => 0, 'info' => '导入失败']);

            } catch (\Exception $e) {

                $this->ajaxReturn(['status' => 0, 'info' => $e->getMessage()]);

            }
        }

    }

    //数据整合
    public function checkPost($pdata){

        $data['im_model'] = trim($pdata['pname']);
        $data['im_pnumber'] = trim($pdata['pnumber']);
        $data['im_time'] = date('Y:m:d H:i:s', time());
        $data['im_staff'] = BaseModel::username();
        $data['pmoney'] = trim($pdata['money']);
        $data['close_time'] = 999;
        $data['status'] = 0;
        $data['readdress'] = '';
        //$data['channel_policy'] = rtrim($channelPolicy,',');

        return $data;
    }

    //验证 渠道 是否存在
    /*public function  verifyChannelName($channelName){

        if(BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[1],
            'where' => ['channel_name' => $channelName]
        ]))
            return $channelName;

        else

            $this->ajaxReturn(['status' => 0, 'info' => '渠道名不存在,请重新填写']);

    }*/

    //验证时间时间 是否合法
    public function  verifyTimeValidity($startTime, $endTime){
        if($endTime < $startTime)$this->ajaxReturn(['status' => 0, 'info' => '结束时间不能够小于开始时间']);
    }

    //验证  料号产品名是否对应
    public function  verifyPnumberByPname($pnumber, $pname){

        if(BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[1],
            'where' => ['pnumber' => $pnumber , 'pname' => $pname]
        ]))
            return 1;

        else

            $this->ajaxReturn(['status' => 0, 'info' => '料号与产品名没有对应']);


    }

    //导出
    public function exportProductInitialData(){
        set_time_limit(0);
        ini_set ('memory_limit', '-1');
        $pdata = json_decode($_POST['data'],true);

        $this->verifyTimeValidity($pdata['start_time'], $pdata['end_time']);//验证时间合法性
        $this->verifyPnumberByPname($pdata['pnumbers'], $pdata['pnames']);//验证料号和名称是否对应

        $exportExcelData =  BaseModel::getDbData([
            'table' => KcodeProduceModel::$table[2],
            'fields' => ['id', 'clearcd', 'secretcd', 'hcode', 'im_pnumber', 'im_model','im_staff','im_time', 'status', 'pmoney', 'close_time'],
            'where' => [
                'im_pnumber' => $pdata['pnumbers'],
                'im_model' => $pdata['pnames'],
                'im_time' => ['between', [startMinuteTime(strtotime($pdata['start_time'])), endMinuteTime(strtotime($pdata['end_time']))]]
            ]
        ]);

        //echo M()->getLastSql();die;

        //echo KcodeProduceModel::exportExcel($this->expTitle, $this->expCellName, $exportExcelData);die;
        if($exportExcelData)

            $this->ajaxReturn([
               'status' => 1,
               'url' => KcodeProduceModel::exportExcel($this->expTitle, $this->expCellName, $exportExcelData),
               'info' => '导出成功'
            ]);
        else

            $this->ajaxReturn(['status' => 0, 'info' => '数据不存在' ]);


    }

    //k码生成
    public function createKcode($type) {

        $str = '';

        while(1) {
            $rand = mt_rand($this->randStart,$this->randEnd);

            if(!in_array($rand, $this->arr))

                $str .= chr($rand);

            if(strlen($str) == $this->type[$type])

            break;

        }

        return $str;


    }


    /*
//导入
public function imProductInitialData(){
    set_time_limit(0);

    $pdata = json_decode($_POST['data'],true);
    //$this->verifyChannelName($pdata['channel_name']);//验证渠道名是否存在

    $this->verifyPnumberByPname(trim($pdata['pnumber']), trim($pdata['pname']));//验证料号和名称是否对应

    $postData = $this->checkPost($pdata, $_POST['channel_policy']);//待添加数据


    for($i=0; $i<$pdata['number']; $i++){

        //生成k码
        $postData['clearcd'] ='ph'.$this->createKcode('ph');
        $postData['secretcd'] =$this->createKcode('am');
        if($pdata['pname'] == 'N1' || $pdata['pname'] == 'N1M' || $pdata['pname'] == 'K3-D1')
            $postData['hcode'] ='BD'.$this->createKcode('BD');

        $res = BaseModel::addData([
            'table' => KcodeProduceModel::$table[2],
            'data' =>$postData
        ]);

        if(!$res)$this->ajaxReturn(['status' => 0, 'info' => '导入失败']);
        //if(!$res) continue;

    }

    if($res)$this->ajaxReturn(['status' => 1, 'info' => '导入成功']);

}

//导入
public function importProductInitialData(){
    set_time_limit(0);
    $pdata = json_decode($_POST['data'],true);
    //$this->verifyChannelName($pdata['channel_name']);//验证渠道名是否存在

    $this->verifyPnumberByPname(trim($pdata['pnumber']), trim($pdata['pname']));//验证料号和名称是否对应

    $postData = $this->checkPost($pdata);//待添加数据

    for($k=0; $k<$pdata['number']; $k++){

        //生成k码
        $insertData[$k]['im_model'] = $postData['im_model'];
        $insertData[$k]['im_pnumber'] = $postData['im_pnumber'];
        $insertData[$k]['im_time'] = $postData['im_time'];
        $insertData[$k]['im_staff'] = $postData['im_staff'];
        $insertData[$k]['pmoney'] = $postData['pmoney'];
        $insertData[$k]['close_time'] = $postData['close_time'];
        $insertData[$k]['status'] = $postData['status'];
        $insertData[$k]['readdress'] =$postData['readdress'];
        $insertData[$k]['channel_policy'] = $postData['channel_policy'];
        $insertData[$k]['clearcd'] = 'ph'.$this->createKcode('ph');
        //echo $insertData[$k]['clearcd'].'<br/>';
        $insertData[$k]['secretcd'] =$this->createKcode('am');
        //echo $insertData[$k]['secretcd'].'<br/>';
        if($pdata['pname'] == 'N1' || $postData['pname'] == 'N1M')
            $insertData[$k]['hcode'] ='BD'.$this->createKcode('BD');
    }
    //$pdata['number'] =100;

    $num  = (int)($pdata['number']/2000);//5000行间断执行
    $yu = $pdata['number']%2000;
    //echo $num.'<br>';
    //echo $yu.'<br>';die;
    if($num >=1){
        for ($i=0; $i < $num; $i++) {
            //每5000条数据组成一个insert语句,$codeModel是存放记录的一个数组
            $values = '';
            for ($j=$i*2000; $j < ($i+1)*2000; $j++) {
                //拼接values的值
                $values .= '("'
                    .$insertData[$j]['im_model'].'","'
                    .$insertData[$j]['im_pnumber'].'","'
                    .$insertData[$j]['im_time'].'","'
                    .$insertData[$j]['im_staff'].'","'
                    .$insertData[$j]['pmoney'].'","'
                    .$insertData[$j]['close_time'].'",'
                    .$insertData[$j]['status'].',"'
                    .$insertData[$j]['readdress'].'","'
                    .$insertData[$j]['clearcd'].'","'
                    .$insertData[$j]['secretcd'].'","'
                    .$insertData[$j]['hcode'].'","'
                    .$insertData[$j]['channel_policy'].'"'
                    .'),';
            }
            //$values = "insert into w_code (im_model,im_pnumber,im_time,im_staff,pmoney,close_time,status,readdress,clearcd,secretcd) values".substr($values,0,-1).';';
            //Yii::$app->db->createCommand($values)->execute();
            $sql = "insert into relation (im_model,im_pnumber,im_time,im_staff,pmoney,close_time,status,readdress,clearcd,secretcd,hcode,channel_policy) values ".substr($values, 0, -1).";";
            //echo $sql;die;
            $res = M()->execute($sql);
            unset($values);
            //echo M()->getLastSql();
            if(!$res)$this->ajaxReturn(['status' => 0, 'info' => '导入失败']);
        }
    }

    if($yu != 0){
        $values = '';
        for ($j=$num * 2000; $j < $num * 2000 + $yu; $j++) {
            //拼接values的值
            $values .= '("'
                .$insertData[$j]['im_model'].'","'
                .$insertData[$j]['im_pnumber'].'","'
                .$insertData[$j]['im_time'].'","'
                .$insertData[$j]['im_staff'].'","'
                .$insertData[$j]['pmoney'].'","'
                .$insertData[$j]['close_time'].'",'
                .$insertData[$j]['status'].',"'
                .$insertData[$j]['readdress'].'","'
                .$insertData[$j]['clearcd'].'","'
                .$insertData[$j]['secretcd'].'","'
                .$insertData[$j]['hcode'].'","'
                .$insertData[$j]['channel_policy'].'"'
                .'),';
        }
        //$values = "insert into w_code (im_model,im_pnumber,im_time,im_staff,pmoney,close_time,status,readdress,clearcd,secretcd) values".substr($values,0,-1).';';
        //Yii::$app->db->createCommand($values)->execute();
        $sql = "insert into relation (im_model,im_pnumber,im_time,im_staff,pmoney,close_time,status,readdress,clearcd,secretcd,hcode,channel_policy) values ".substr($values, 0, -1).";";
        $res = M()->execute($sql);
        unset($values);
        if(!$res)$this->ajaxReturn(['status' => 0, 'info' => '导入失败']);
    }


    if($res)$this->ajaxReturn(['status' => 1, 'info' => '导入成功']);

}*/


    public function test(){

        echo $this->createKcode('ph').'<br/>';
        echo $this->createKcode('BD').'<br/>';
        echo $this->createKcode('n1').'<br/>';
    }



}