<?php

namespace app\index\controller;

use app\common\model\User;
use EasyWeChat\Foundation\Application;
use think\Controller;
use think\Db;

class Callback extends Controller
{
    /**
     * 授权回调
     */
    public function oauth()
    {

        Db::transaction(function () {
            $config = [
                'debug'     => true,
                'app_id'    => 'wx821b50c826507628',
                'secret'    => 'fc9deefe99318aa866bd9fafa86a7e77',
                'token'     => '',
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
            $app = new Application($config);
//
            $oauth = $app->oauth;
//            // 获取 OAuth 授权结果用户信息
            $wechat = $oauth->user();

            $userWechat = Db::name("User")->where(['wechat_id'=>$wechat['original']['unionid']])->select();
            if (empty($userWechat)) {
                $user = new User();
                $user->wechat_id        = $wechat['original']['unionid'];
                $user->head_img         = $wechat['original']['headimgurl'];
                $user->little_name      = $wechat['original']['nickname'];
                $user->register_time    = time();
                $user->notice_use_time  = time();
                $user->evaluate_time    = 1;
                $user->save();
                session('wechat_new_user_id',$user->wechat_id);
//                $targetUrl = url('/index', '', '.html',true).'#/register-Index';
                $targetUrl = url('/index', '', '.html',true).'#/home-index';
            }else{
                $id = Db::name("User")->where(['wechat_id'=>$wechat['original']['unionid']])->field("login_cell,id")->find();
                if(!empty($id['login_cell'])) {
                    session('user_id',$id['id']);
//                    var_dump(session('user_id'));die;
                }
//                var_dump(strlen($id['login_cell']));
                $targetUrl = url('/index', '', '.html',true).'#/home-index';
            }

            header('location:'. $targetUrl); // 跳转到业务页面

        });
    }
}
