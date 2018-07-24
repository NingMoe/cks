<?php

namespace Admin\Controller;

/**
 * 平台营业控制器类
 * @author observerstar
 */
class PlatformController extends BaseController
{
    /**
     * 平台列表
     */
    public function index()
    {
        $platforms = M('platform')->select();
        $this->assign('platforms', json_encode($platforms));
        $this->display();
    }

    public function edit(){
        $id = I('get.platform');
        $platform = M('platform')->where(['id' => $id])->find();
        $this->ajaxReturn($platform);
    }

    public function update()
    {
        $platform = json_decode(file_get_contents('php://input'), true);
        M('platform')->where(['id' => $platform['id']])->save($platform);
        $this->success('保存成功！');
    }

}