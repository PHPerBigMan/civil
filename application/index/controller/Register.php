<?php
namespace app\index\controller;
header('Content-type: text/json');
//header('Access-Control-Allow-Origin:*');
use app\common\model\Banner;
use app\common\model\Category;
use app\common\model\User;
use app\common\model\UserInformation;
use app\common\model\WebNotice;
use think\Controller;
use think\Cache;
use think\Db;
use think\Session;

class Register extends Controller
{
    /**
     * @return string
     * 注册发送验证码，这部分还需要修改
     */

   public function register(){
       $mobile = input('tel');
       $code = rand(100000,999999);
       if(empty(session('mobile'))){
           $post_data = array();
           $post_data['account'] = '003531';   //帐号
           $post_data['pswd'] = '1l7qMID4';  //密码
           $post_data['msg'] =urlencode("感谢您注册信云土木会员，验证码：".$code."。请及时输入验证码进行登录！"); //短信内容需要用urlencode编码下
           $post_data['mobile'] = $mobile; //手机号码， 多个用英文状态下的 , 隔开
           $post_data['product'] = ''; //产品ID
           $post_data['needstatus']=false; //是否需要状态报告，需要true，不需要false
           $post_data['extno']='';  //扩展码   可以不用填写
           $url='http://send.18sms.com/msg/HttpBatchSendSM';
           $o='';
           foreach ($post_data as $k=>$v)
           {
               $o.="$k=".urlencode($v).'&';
           }
           $post_data=substr($o,0,-1);
           $ch = curl_init();
           curl_setopt($ch, CURLOPT_POST, 1);
           curl_setopt($ch, CURLOPT_HEADER, 0);
           curl_setopt($ch, CURLOPT_URL,$url);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
           $result = curl_exec($ch);
           session('code',$code);
           session('mobile',$mobile);
       }else{
           $post_data = array();
           $post_data['account'] = '003531';   //帐号
           $post_data['pswd'] = '1l7qMID4';  //密码
           $post_data['msg'] =urlencode("感谢您注册信云土木会员，验证码：".$code."。请及时输入验证码进行登录！"); //短信内容需要用urlencode编码下
           $post_data['mobile'] = $mobile; //手机号码， 多个用英文状态下的 , 隔开
           $post_data['product'] = ''; //产品ID
           $post_data['needstatus']=false; //是否需要状态报告，需要true，不需要false
           $post_data['extno']='';  //扩展码   可以不用填写
           $url='http://send.18sms.com/msg/HttpBatchSendSM';
           $o='';
           foreach ($post_data as $k=>$v)
           {
               $o.="$k=".urlencode($v).'&';
           }
           $post_data=substr($o,0,-1);
           $ch = curl_init();
           curl_setopt($ch, CURLOPT_POST, 1);
           curl_setopt($ch, CURLOPT_HEADER, 0);
           curl_setopt($ch, CURLOPT_URL,$url);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
           $result = curl_exec($ch);
           session('code',$code);

       }
       $result = explode(',',$result);
       if($result[1] == 0){
           $j = [
               'data' => 200,
               'code' =>$code,
           ];
       }else{
           $j = [
               'data' => 100,
               'code' =>'',
           ];
       }
       return json_encode($j);
   }
    /**
     * @return int
     * 验证码比对
     */
   public function check(){
       $c_check = input('code');
       if(empty($c_check)){
           $res = 300;
           $msg = "验证码空";
       }else{
           if($c_check == session('code')){
               $res = 200;
           }else{
               $res = 100;
           }
       }
       return $res;
   }
    /**
     * @return string  设置密码还需要修改
     */

   public function set(){
//       session('wechat_new_user_id','');die;
//        ($_SESSION['wechat_new_user_id'] = '');die;
//       echo session('wechat_new_user_id');die;
       if(empty(session('wechat_new_user_id'))){
           $pwd_get = input('pwd');
           $mobile = input('mobile');
           $u = new User();
           $pwd = $u->where(['login_cell'=>$mobile])->value("user_pwd");
//       var_dump($pwd);die;
           if(!empty($pwd)){
               $user_id = Db::name('User')->where(['login_cell'=>$mobile])->value('id');
               if($pwd == sha1($pwd_get)){
                   $s_code = 200;
                   $msg = '修改成功';
                   session('user_id',$user_id);
               }else{
                   $ret= Db::name('User')->where(['login_cell'=>$mobile])->update(['user_pwd'=>sha1($pwd_get)]);
                   if($ret){
                       $s_code = 200;
                       $msg = '修改成功';
                       session('user_id',$user_id);
                   }else{
                       $s_code = 100;
                       $msg = '修改失败';
                   }
               }
               $j = [
                   'result'=>$s_code,
                   'msg'=>$msg
               ];
           }else{
               $u->login_cell   = $mobile;
               $u->user_pwd     = sha1($pwd_get);
               $u->wechat_id    = '';
               $u->level        = 0;
               $u->notice_time  = 6;
               $u->register_time = time();
               $u->notice_use_time = time();
               $u->evaluate_time = 1;
               $ret = $u->save();
               $user_id = Db::name('User')->where(['login_cell'=>$mobile])->value('id');
               if($ret){
                   $s_code = 200;
                   $msg='注册成功';
                   session('user_id',$user_id);
               }else{
                   $s_code = 100;
                   $msg = '注册失败';
               }
               $j = [
                   'result'=>$s_code,
                   'msg'=>$msg,
                   'user_id'=>$user_id
               ];
           }
       }else{
           $pwd_get     = input('pwd');
           $mobile      = input('mobile');
           //根据微信open_id 找手机号码
//           $wechat_mobile = Db::name("User")->where(['wechat_id'=>session("wechat_new_user_id")])->value("login_cell");
           //根据输入的手机号绑定 open_id
//           $hold_mobile = Db::name("User")->where(['login_cell'=>$mobile])->value("id");

           $u = new User();
           $pwd = $u->where(['login_cell'=>$mobile])->value("user_pwd");
           $wechat = Db::name("User")->where(['login_cell'=>$mobile])->find();
           $user_id = Db::name('User')->where(['login_cell'=>$mobile])->value('id');

           if(empty($wechat)){
               $u->wechat_id        = session('wechat_new_user_id');
               $u->head_img         = session('headimgurl');
               $u->little_name      = session('nickname');
               $u->login_cell       = $mobile;
               $u->user_pwd         = sha1($pwd_get);
               $u->register_time    = time();
               $u->notice_use_time  = time();
               $u->level            = 0;
               $u->notice_time      = 6;
               $u->evaluate_time    = 1;
               $s = $u->save();
               $user_id = $u->id;
               if($s){
                   $s_code = 200;
                   $msg = "绑定微信成功";
                   session('user_id',$user_id);
                   Session::delete('wechat_new_user_id');
                   Session::delete('headimgurl');
                   Session::delete('nickname');
               }
               $j = [
                   'result'=>$s_code,
                   'msg'=>$msg
               ];
           }else{
               if(!empty($pwd)){
                   $user_id = Db::name('User')->where(['login_cell'=>$mobile])->value('id');
                   if($pwd == sha1($pwd_get)){
                       $s_code = 200;
                       $msg = '修改成功';
                       session('user_id',$user_id);
                   }else{
                       $ret= Db::name('User')->where(['login_cell'=>$mobile])->update(['user_pwd'=>sha1($pwd_get)]);
                       if($ret){
                           $s_code = 200;
                           $msg = '修改成功';
                           session('user_id',$user_id);
                       }else{
                           $s_code = 100;
                           $msg = '修改失败';
                       }
                   }
                   $j = [
                       'result'=>$s_code,
                       'msg'=>$msg
                   ];
               }else{
                   $ret = Db::name('User')->where("wechat_id",session('wechat_new_user_id'))->update([
                       'login_cell'=>$mobile,
                       'user_pwd'=>sha1($pwd_get),
                       'register_time'=>time(),
                   ]);

                   if($ret){
                       $s_code = 200;
                       $msg='注册成功';
                       session('user_id',$user_id);
                   }else{
                       $s_code = 100;
                       $msg = '注册失败';
                   }
                   $j = [
                       'result'=>$s_code,
                       'msg'=>$msg
                   ];
               }
           }
//       var_dump($pwd);die;
       }
       return json_encode($j);
   }

   //废弃
    public  function wechat()
    {
        $redirect = urlencode("http://civil.gyl.sunday.so/index/register/access");
        $state = md5("admin12345");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxc909bc6835c99bfb&redirect_uri=".$redirect."&response_type=code&scope=snsapi_login&state=".$state."#wechat_redirect";
//        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
//            return json_encode($url);
//        }else{
//            header("Location:".$url);
//        }
        return $url;
    }
    //废弃
    public function access(){
        $code = input('code');
        $appid = "wxc909bc6835c99bfb";
        $secret = "eafbd46df0a569e080eda8fda4e292d0";
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $data = json_decode(file_get_contents($url),true);
//       return $data;
        $new_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$data['access_token']."&openid=".$data['openid'];
        $user_data = json_decode(file_get_contents($new_url),true);
        $user = new User();
        $check = Db::name("User")->field("wechat_id,login_cell,id")->where(['wechat_id'=>$user_data['openid']])->select();
        if(empty($check)){
            $user->wechat_id = $user_data['openid'];
            $user->evaluate_time = 1;
            $s = $user->save();
            if($s){
                session('wechat_new_user_id',$user_data['openid']);
                $this->redirect("http://civil.gyl.sunday.so/index.html#/register-Index");
            }else{
                $this->error("登录失败","index/register/wechat",2);
            }

        }else{
            if(empty($check[0]['login_cell'])){
                session('wechat_new_user_id',$user_data['openid']);
                $this->redirect("http://civil.gyl.sunday.so/index.html#/home-index");
            }else{
                session('user_id',$check[0]['id']);
                session('mobile',$check[0]['login_cell']);
                $this->redirect("http://civil.gyl.sunday.so/index.html#/home-index");
            }
        }
    }


    //pc微信授权
    public function web_wechat(){
        $redirect = urlencode("http://civil.gyl.sunday.so/index/register/web_access");
        return json_encode($redirect);
    }

    public function web_access(){

        $code = input('code');
        $appid = "wxc909bc6835c99bfb";
        $secret = "eafbd46df0a569e080eda8fda4e292d0";
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $data = json_decode(file_get_contents($url),true);
//       return $data;
        $new_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$data['access_token']."&openid=".$data['openid'];
        $user_data = json_decode(file_get_contents($new_url),true);
        $user = new User();
        $check = Db::name("User")->field("wechat_id,login_cell,id")->where(['wechat_id'=>$user_data['unionid']])->select();
//        var_dump($user_data);die;
        if(empty($check)){
//            $user->wechat_id        = $user_data['unionid'];
//            $user->head_img         =   $user_data['headimgurl'];
//            $user->little_name      = $user_data['nickname'];
//            $user->evaluate_time = 1;
//            $s = $user->save();
//            if($s){
//
//            }else{
//                $this->error("登录失败","index/register/wechat",2);
//            }
            session('wechat_new_user_id',$user_data['unionid']);
            session('headimgurl',$user_data['headimgurl']);
            session('nickname',$user_data['nickname']);
            $this->redirect("http://civil.gyl.sunday.so/pc/views/index.html?first=1");
        }else{
//            var_dump($check);die;
            if(empty($check[0]['login_cell'])){
                session('wechat_new_user_id',$user_data['unionid']);

                $this->redirect("http://civil.gyl.sunday.so/pc/views/index.html?first=1");
            }else{
                session('user_id',$check[0]['id']);
                session('mobile',$check[0]['login_cell']);
                $this->redirect("http://civil.gyl.sunday.so/pc/views/index.html");
            }
        }
    }
}
