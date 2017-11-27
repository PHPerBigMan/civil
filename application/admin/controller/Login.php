<?php
namespace app\admin\controller;

use app\common\model\Admin;
use think\Controller;
use think\Request;

class Login extends Controller
{
    public function Index()
    {
       return view();
    }

    /**
     * 后台管理员登录
     */
    public function login()
    {
        $admin = Admin::get(['admin_name'=>input('admin_name'),'admin_pwd'=>sha1(input('admin_pwd'))]);
        if($admin){
//            echo 1;die;
            session('admin',$admin);
            $this->redirect('index/index');
        }else{
            $this->error('账号或密码错误');
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session(null);
        $this->redirect('index');
    }
}
