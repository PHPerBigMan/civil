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

class Awe extends Controller
{
    public function run()
    {
        $options = [
            'debug'     => true,
            'app_id'    => 'wx821b50c826507628',
            'secret'    => 'fc9deefe99318aa866bd9fafa86a7e77',
            'token'     => 'baimifan',
            // ...
            // ..
            'log'     => [
                'level'      => 'debug',
                'file'       => LOG_PATH . 'easywechat.log',
            ],
//            'oauth' => [
//                'scopes'   => ['snsapi_userinfo'],
//                'callback' => 'http://civil.gyl.sunday.so/index/callback/oauth',
//            ],
        ];

        $app = new Application($options);
        $response = $app->server->serve();
// 将响应输出
        $response->send();
    }

//    public function aa(){
//
//        $options = [
//            'debug'     => true,
//            'app_id'    => 'wx821b50c826507628',
//            'secret'    => 'fc9deefe99318aa866bd9fafa86a7e77',
//            'token'     => 'baimifan',
//            // ...
//            // ..
//            'log'     => [
//                'level'      => 'debug',
//                'file'       => LOG_PATH . 'easywechat.log',
//            ],
//        ];
//        $app = new Application($options);
//        $menu = $app->menu;
//
//        $buttons = [
//            [
//                        "type" => "view",
//                        "name" => "企业平台",
//                        "url"  => "http://civil.gyl.sunday.so/index/Oauth/run"
//    ]
//
//        ];
//        $menu->add($buttons);
//        echo session('user_id');
//    }
}
