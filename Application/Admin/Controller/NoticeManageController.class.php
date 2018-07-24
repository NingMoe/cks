<?php

namespace Admin\Controller;

use Admin\Model\BaseModel;
use Admin\Model\NoticeManageModel;

/**
 * 公告
 * 
 * @author jan
 *
 */
class NoticeManageController extends BaseController
{

    //公告列表
    public function index()
    {

        $condition =  [
            'table'=>NoticeManageModel::$table[0],
            'order' => 'sort',
            'page' => $page = !empty($_GET['page']) ? $_GET['page'] : 0,
        ];

        $_GET['title'] && $condition['where'] = [
            'title' => ['like', "%{$_GET['title']}%"]
        ];

        $this->assign('data', BaseModel::getListData($condition));//分页 和列表

        $this->assign('title', $_GET['title']);//分页 和文章列表

        $this->display();
    }


    //添加公告
    public function addNotice()
    {
        $_GET['id'] && $this->assign('noticeData', BaseModel::getDbData([

            'table' => NoticeManageModel::$table[0],

            'where' => ['id'=>$_GET['id']]

        ],false));

        $this->display();
    }


    //删除公告
    public function deleteArticle(){

        BaseModel::delData([
            'table' => NoticeManageModel::$table[0],
            'where' => ['id' => $_POST['id']]
        ]);

        $this->delRemind(true);

    }

    //数据整合
    public function checkPost($postData){

        $pdata = json_decode($postData['data'],true);


        $data = $pdata['action'] == 'add' ? ['tag' => 'add'] : ['tag' => 'edit', 'id' => $pdata['eid']];

        //整合种类数据
        $data['table'] = NoticeManageModel::$table[0];
        $data['data']['title'] = trim($pdata['noticetitle']);
        $data['data']['sort'] = trim($pdata['noticesort']);
        $data['data']['show'] = trim($pdata['show']);
        $data['data']['content'] = trim($pdata['noticecontent']);
        $data['data']['operator'] = BaseModel::username();

        return $data;

    }

    public function  kindEditorUpload(){

        $response = NoticeManageModel::uploads($_FILES);

        if($response){

            $url = json_decode($response, true)['url'];
            header('Content-type: text/html; charset=UTF-8');

            if($url) echo json_encode(['error' => 0, 'url' => $url]);
            else echo 'url is null';

        }else

            echo 'error';
    }

}