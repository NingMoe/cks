<?php

namespace Admin\Controller;
use Admin\Model\AccountModel;
use Admin\Model\BaseModel;

/**
 * 用于用户的登录验证控制器类
 * 
 * @author jan
 *
 */
class PlatformController extends BaseController
{
    /**
     * 平台列表
     */
    public function index()
    {
       $this->assign('platforms', M('platform')->select());
       $this->display();
    }

    public function edit(){
        $id = I('get.platformId');
        $this->assign('platform', M('platform')->where(['id' => $id]))->find();
        $this->display();
    }

    public function update()
    {
        $platform = I('get.platform');
        M('policy')->where(['id' => $platform['id']])->save($platform);
    }

}