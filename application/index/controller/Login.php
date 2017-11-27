<?php
namespace app\index\controller;

header('Content-type: text/json');
use app\common\model\User;
use app\common\model\Visit;
use phpDocumentor\Reflection\Types\Null_;
use think\Controller;
use think\Db;

class Login extends Controller
{
    /**
     * 登录
     */

    public function login(){
        $login = input('tel');
        $pwd = User::where(['login_cell'=>$login])->select();

        if(empty($pwd)){
            $j = [
                'status'=>101,
                'msg'=>'您还未注册'
            ];
        }else{
            foreach($pwd as $k=>$v){
                $pass   = $v->user_pwd;
                $id     = $v->id;
            }

            $user_pwd = sha1(input('password'));
            if($user_pwd == $pass){

                 session('user_id',$id);
                 session('mobile',$login);
                 $data['user_id'] = $id;
                $j=[
                    'data' =>$data,
                    'msg'=>'登录成功',
                    'status'=>200
                ];
                $visit = new Visit();
                $visit->user_id         = empty(session('user_id')) ? 0 : session('user_id');
                $visit->ip              = $_SERVER["REMOTE_ADDR"];
                $visit->visit_time      = time();
                $visit->save();
            }else if($user_pwd != $pass){
                $j=[
                    'msg'=>'密码错误',
                    'status'=>100
                ];
            }
        }
        return json_encode($j);
    }

    /**
     * 退出
     */

    public function login_out(){
//        unset($_SESSION['user_id']);
//        session('user_id','');
        session(null);
        if(empty(session('user_id'))){
            $status = 200;
        }else{
            $status = 100;
        }
        $j = [
            'status'=>$status
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 登录验证
     */

    public function check_login(){
        if(empty(session('user_id'))){
            $status = 100;
            $msg = "未登录";
            $user_id = '';
            $user_name = '';
            $company_name = '';
        }else{
            $status = 200;
            $msg = "已登录";
            $name =  Db::name('UserInformation')->where("user_id",session('user_id'))->value('company_name');
            $user_id = session('user_id');
            $user_name = session('mobile');
            $company_name = $name;
        }
        $j = [
            'status'=>$status,
            'msg'=>$msg,
            'user_id'=>$user_id,
            'user_name'=>$user_name,
            'company_name'=>$company_name
        ];
        return json_encode($j);
    }
    /**
     * 微信授权登陆 ---->重定向地址，需要进行UrlEncode
     * @return string
     */

    public function url(){
        return urlencode("http://civil.gyl.sunday.so/index.html#/register-Index");
    }
}
