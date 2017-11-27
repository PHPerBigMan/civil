<?php
// +----------------------------------------------------------------------
//            -------------------------
//           /   / ----------------\  \
//          /   /             \  \
//         /   /              /  /
//        /   /    /-------------- /  /
//       /   /    /-------------------\  \
//      /   /                   \  \
//     /   /                     \  \
//    /   /                      /  /
//   /   /      /----------------------- /  /
//  /-----/      /---------------------------/
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://baimifan.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanglidong
// | Date: 2017/2/7
// | Time: 10:41
// +----------------------------------------------------------------------


namespace app\index\controller;



use EasyWeChat\Foundation\Application;
use think\Controller;

class Oauth extends Controller
{
    public function run()
    {
        $config = [
            'debug'     => true,
            'app_id'    => 'wx821b50c826507628',
            'secret'    => 'fc9deefe99318aa866bd9fafa86a7e77',
            'token'     => '',
            // ...
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => 'http://civil.gyl.sunday.so/index/callback/oauth',
            ],
            // ..
            'log'     => [
                'level'      => 'debug',
                'file'       => LOG_PATH . 'easywechat.log',
            ],
        ];

        //使用配置初始化一个项目实例
        $app = new Application($config);
        //从项目实例中得到一个oauth应用实例
        $oauth = $app->oauth;

        $oauth->redirect()->send();
        if(empty(session('user_id'))){
        }else{
            $targetUrl = url('/index', '', '.html',true).'#/home-index';
//
            header('location:'. $targetUrl); // 跳转到业务页面
        }
        //判断用户登录状态
//        if (empty(session('user_id'))) {
//            //未登录，引导用户到微信服务器授权
//        }else{
//            //已登录状态，重定向到首页
//            $targetUrl = url('/index', '', '.html',true).'#/home-index';
//
//            header('location:'. $targetUrl); // 跳转到业务页面
//        }
    }


}
