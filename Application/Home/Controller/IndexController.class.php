<?php
namespace Home\Controller;

use Think\Controller;

/**
 * 用于用户的登录验证控制器类
 * 
 * @author jan
 *
 */
class IndexController extends Controller
{

    /**
     * 后台首页
     */
    public function index(){

        //if($_SERVER["SERVER_NAME"] == 'cks.phicomm.com')

        header('location:https://cks.phicomm.com/index.php/Admin');

        //else

           // header('location:https://cks.phicomm.com/index.php/Admin');
    }

}