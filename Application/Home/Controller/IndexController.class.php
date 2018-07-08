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

        if($_SERVER["SERVER_NAME"] == 'phishop.local')

         header('location:http://phishop.local/index.php/Admin');
        //header('location:https://k.phicomm.com/cks/dist/');

        //else

           // header('location:https://cks.phicomm.com/index.php/Admin');
    }

}