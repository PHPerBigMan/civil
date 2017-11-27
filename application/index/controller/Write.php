<?php


namespace app\index\controller;

use app\common\model\UserEvaluate;
use app\common\model\YaoQing;
use app\common\model\YiQiXie;
use GuzzleHttp\Client;
use think\Controller;
use think\Db;

class Write extends Controller
{
    protected $yqx = "";
    public function __construct()
    {
        $this->yqx = new YiQiXie();
    }

    /**
     * 一起写文档
     */

    public function index(){
    }

    /**
     * 导出合同
     */
    public function get(){
        //用户id
        $user_id            = input('user_id');
        $company_id         = input('company_id');
        $id                 = input('contact_id');
        $allId              = [];
        //如果达成合作 次数超过2次，且初级认证会员则升为高级认证会员
        $data = Db::name('customer')->where([
            'user_id'=>$user_id,
            'status'=>300
        ])->select();

        foreach ($data as $v){
            $allId[] = $v['customer_id'];
        }

        array_unique($allId);
        $level = Db::name('User')->where('id',$user_id)->value('level');

        if((count($allId)>=2) && $level==1){
            //升级为高级认证用户
            Db::name('User')->where('id',$user_id)->update([
                'notice_time'=>48,
                'level'=>2
            ]);
        }
        $key = Db::name("Write")->where(['user_id'=>$user_id,'customer_id'=>$company_id,'contract_id'=>$id])->field("key,short_token")->find();
        $url = "https://yiqixie.com/d/export/".$key['key']."?format=docx&API-SESSION-TOKEN=".$key['short_token'];

        return $url;
    }


    /**
     * 一起写上传文档
     */


    public function set(){

        //用户id
        $id                     = input('user_id');
        //合同id
        $contract_id_use        = empty(input('contract_id')) ? input('contact_id') :input('contract_id');
        $contract_id_use_edit   = input('contact_id');
        //客户id
        $customer_id            = input('company_id');
        //判断是否是直接上传模板合同
        $muban                  = empty(input('muban')) ? 0 : 1;
        //判断用户是否  将合同数据保存
        $write = empty(Db::name('Write')->where(['contract_id'=>$contract_id_use])->field("write_id")->find()) ?
            Db::name('Write')->where(['contract_id'=>$contract_id_use_edit])->field("write_id")->find() :
            Db::name('Write')->where(['contract_id'=>$contract_id_use])->field("write_id")->find();
        $api_key                = "vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178";
        $secret_key             = "89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9";
        if(empty($write)){
            //判断用户是否注册过一起写账号
            $write_id = Db::name('write')->where(['user_id'=>$id])->find();

            $write = new \app\common\model\Write();

            if(empty($write_id)){

                $user_data = Db::name('User')->where(['id'=>$id])->find();
                //display_name使用公司名称

                $display_name           = empty(Db::name('UserInformation')->where(['user_id'=>$user_data['id']])->value("company_name"))
                    ? ($user_data['login_cell'])
                    : (Db::name('UserInformation')->where(['user_id'=>$user_data['id']])->value("company_name"));

                $local_id               = $id * rand(1000,9999);
                $email                  = $id * rand(1000,9999);
                $profile_url            = $user_data['head_img'];

                //获取user_id
                $url = "https://yiqixie.com/e/api/user/create?api_key=".$api_key."&secret_key=".$secret_key."&local_id=".$local_id."&email=".$email."&display_name=".$display_name."&profile_url=".$profile_url;


                $output = json_decode(file_get_contents($url),true);

//                var_dump($output);die;
                $write->user_id             = $id;
                $write->customer_id         = $customer_id;
                $write->write_id            = $output['user_id'];
                $write->home_id             = $output['home_id'];
                //获取long_token
                $url1 = "https://yiqixie.com/e/api/getrefreshtoken?api_key=".$api_key."&secret_key=".$secret_key."&user_id=".$output['user_id'];

                $output1 = json_decode(file_get_contents($url1),true);

                $write->long_token = $output1['long_token'];


                //将long_token保存至用户表中
                Db::name('user')->where(['id'=>$id])->update([
                    'long_token'=>$output1['long_token']
                ]);
            }else{
                $output1['long_token'] = $write_id['long_token'];
                $output['home_id'] = $write_id['home_id'];
                $output['user_id'] = $write_id['write_id'];
                $write->user_id             = $id;
                $write->customer_id         = $customer_id;
                $write->long_token = $write_id['long_token'];
            }

            //查询用户的等级
            $level = Db::name('user')->where(['id'=>$id])->field('level,write_time')->find();

            if($level['level'] == 0 && $level['write_time'] > 10){
                $url4 = "";
                $msg  = "上传失败，请完成认证";
                $code = 400;
            }else if($level['level'] == 1 && $level['write_time'] > 30){
                $url4 = "";
                $msg  = "上传失败，请完成高级认证后继续使用";
                $code = 400;
            }else if($level['level'] == 2 && $level['write_time'] > 50){
                $url4 = "";
                $msg  = "上传失败，您上传合同的次数已经用完";
                $code = 400;
            }else{
                $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$output1['long_token'];

                //获取short_token
                $output2 = json_decode(file_get_contents($url2),true);

                $write->short_token = $output2['short_token'];
                if($muban == 1){
                    $contract_name = Db::name('hetong')->where([
                        'id'=>input('htId')
                    ])->value('contract_name');
                }else{
                    $file = request()->file('contract');
                    if(!empty($file)) {
                        $contract_name = $_FILES['contract']['name'];
                        $contract_name = explode('.',$contract_name);
                        $name = $contract_name[0];
                        $info = $file->move('uploads/',$name.' '.$name.'.'.$contract_name[1]);
                        if ($info) {
                            $word = '/uploads/'.$info->getSaveName();
                            //合同名称

                        }
                    }
                }


                $url3 = "https://yiqixie.com/d/import/upload/".$output['home_id']."?format=doc&API-SESSION-TOKEN=".$output2['short_token'];
                if(!$url3){
                    $url3 = "https://yiqixie.com/d/import/upload/".$output['home_id']."?format=docx&API-SESSION-TOKEN=".$output2['short_token'];
                }

                //该部分使用Guzzle上传文档至接口
                $client = new Client();

                if($muban == 1){
                    //直接上传模板
                    $Mword = Db::name('hetong')->where([
                        'id'=>input('htId')
                    ])->value('url');
                    $wordUrl = URL.$Mword;
                }else{
                    $wordUrl = URL.$word;
                }
                $response = $client->request('POST', $url3, [
                    'multipart' => [
                        [
                            'name'     => 'Filedata',
                            'contents' => fopen($wordUrl,'r')
                        ],
                    ]
                ]);
                //获取接口返回值
                $body = $response->getBody();
                $remainingBytes = json_decode($body->getContents(),true);

                $write->key = $remainingBytes['sessionStatus']['additionalData']['docId'];

                //修改合同的 访问权限
                $changeStatus = "https://yiqixie.com/e/api/setmetadata/".$remainingBytes['sessionStatus']['additionalData']['docId']."?external=1&API-SESSION-TOKEN=".$output2['short_token'];

                file_get_contents($changeStatus);
                //上传合同后修改数据库数据
                Db::name("customer")
                    ->where(['user_id'=>$id,'customer_id'=>$customer_id,'status'=>0])
                    ->update([
                        'contract_name' =>$contract_name,
                        'status'        => 101,
                        'c_status'=>1,
                    ]);
                //这部分还需要修改
                Db::name("customer")
                    ->where(['customer_id'=>$id,'user_id'=>$customer_id,'status'=>0])
                    ->update([
                        'contract_name' =>$contract_name,
                        'status'        => 102,
                        'c_status'=>0,
                    ]);

                //保存发起人 信息
                $contract_id     = Db::name("customer")->where(['user_id'=>$id,'customer_id'=>$customer_id,'id'=>$contract_id_use])->value("customer_id");

                $write->customer_id = $contract_id;
                $write->contract_id = $contract_id_use;
                $write->save();

                //客户的id  自动帮助客户添加信息
                $c_id = Db::name("customer")->where(['user_id'=>$id,'id'=>$contract_id_use])->value("customer_id");

                //获取 客户合同信息的 id值  要修改
                $check_custom_info = Db::name('Customer')->where(['user_id'=>$customer_id,'customer_id'=>$id])->where("status","<>",'300')->order('create_time desc')->value('id');


                if(($check_custom_info == ($contract_id_use+1)) || ($check_custom_info == ($contract_id_use-1))){
                    $customer_contract_id = $check_custom_info;
                }
                //$c_id 客户用户id

                $write->customer($c_id,$remainingBytes['sessionStatus']['additionalData']['docId'],$output['user_id'],$output2['short_token'],$contract_name,$id,$customer_contract_id,input('cr_time'),$output1['long_token']);

                $url4 = "https://yiqixie.com/d/home/".$remainingBytes['sessionStatus']['additionalData']['docId']."?rt=embedded&API-SESSION-TOKEN=".$output2['short_token'];

                //取出 发起人创建时间的数据
                $user_cr_time = Db::name('Customer')->where(['id'=>$contract_id_use])->value('create_time');

                //取出 客户创建时间的数据
                $cus_cr_time = Db::name('Customer')->where(['id'=>$customer_contract_id])->value('create_time');
                Db::name("Customer")->where([
                    'id'=>$contract_id_use,
                ])->order('create_time desc')->update([
                    'update_time'=>$user_cr_time+1
                ]);

                Db::name("Customer")->where([
                    'id'=>$customer_contract_id,
                ])->order('create_time desc')->update([
                    'update_time'=>$cus_cr_time+1
                ]);

                //增加互相评价的次数 需要添加两次，双方两次  这部分需要修改 调整
                $eva_time = Db::name('UserEvaluate')->where(['evaluate_id'=>$id,'evaluated_id'=>$customer_id])->find();
                if(empty($eva_time)){
                    $eva = new UserEvaluate();
                    $eva->evaluate_id = $id;
                    $eva->evaluated_id = $customer_id;
                    $eva->evaluate_time = 1;
                    $eva->create_time = time();
                    $e = $eva->save();
                    if($e){
                        $eva->evaluate_id = $customer_id;
                        $eva->evaluated_id = $id;
                        $eva->evaluate_time = 1;
                        $eva->create_time = time();
                        $eva->save();
                    }
                }else{
                    //增加一次互评的机会
                    Db::name('UserEvaluate')->where(['evaluate_id'=>$id,'evaluated_id'=>$customer_id])->setInc('evaluate_time');
                }

                //签约统计增加
                Db::name('User')->where(['id'=>$id])->setInc('customer_time');
                Db::name('User')->where(['id'=>$customer_id])->setInc('customer_time');
                //增加调用一起写次数的记录
                Db::name('User')->where(['id'=>$id])->setInc('write_time');
                $msg = "上传成功";
                $code = 200;
            }
        }else{
            $new_contract = empty(input('contact_id')) ? input('contract_id') : input('contact_id');
            $key = Db::name("Write")->where(['contract_id'=>$new_contract])->field("key,short_token,long_token")->select();
            if($key[0]['long_token'] == "NULL"){
                $key[0]['long_token'] = Db::name('user')->where(['id'=>$id])->value('long_token');
            }

//            $long_token = Db::name("Write")->where(['user_id'=>$id,''])->value('long_token');
//            $long_token = Db::name("Write")->where(['user_id'=>$id,''])->value('long_token');
            $output1['long_token'] = $key[0]['long_token'];

            $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$output1['long_token'];

            //获取short_token
            $output2 = json_decode(file_get_contents($url2),true);


            $url4 = "https://yiqixie.com/d/home/".$key[0]['key']."?rt=embedded&API-SESSION-TOKEN=".$output2['short_token'];
//            echo "1";die;
            Db::name("Customer")->where([
                'user_id'=>$id,
                'customer_id'=>$customer_id,
            ])->whereIn('status',[210,200,101])->order('create_time desc')->update([
                'status'=> 210,
                'c_status'=>0,
                'update_time'=>time()
            ]);

            Db::name("Customer")->where([
                'user_id'=>$customer_id,
                'customer_id'=>$id,
            ])->whereIn('status',[210,200,101])->order('create_time desc')->update([
                'status'=> 210,
                'c_status'=>0,
                'update_time'=>time()
            ]);
            Db::name('User')->where(['id'=>$id])->setInc('write_time');
            $code = 200;
            $msg = "获取数据成功";
        }
        $num = Db::name('User')->where(['id'=>$id])->find();
        switch ($num['level']){
            case 0:
                $time = 10;
                break;
            case 1:
                $time = 30;
                break;
            case 2:
                $time = 50;
                break;
            default:
                $time = 10;
                break;
        }
        //剩余还能使用次数
        //已经使用次数
        $yqxUsed = (int)$num['write_time'];
        if($yqxUsed >= $time){
            $code = 400;
            $msg = "用完次数";
            $url4 = "";
        }
        $j = [
            'url'=>$url4,
            'status'=>$code,
            'msg'=>$msg
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * author hongwenyang
     * method description : 移动端返回合同一起写地址
     */

    public function hetong(){
        $api_key                = "vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178";
        $secret_key             = "89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9";
        $contact_id = input('contact_id');

        $long_token = Db::name("user")->where(['id'=>Db::table('write')->where(['contract_id'=>$contact_id])->value('user_id')])->value('long_token');

        $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$long_token;

        //获取short_token
        $output2 = json_decode(file_get_contents($url2),true);

        $key = Db::name("Write")->where(['contract_id'=>$contact_id])->field("key")->select();

        if(empty($key)){
           $url4 = "/index.html#/contract-index";
        }else{
            $url4 = "https://yiqixie.com/d/home/".$key[0]['key']."?rt=embedded&API-SESSION-TOKEN=".$output2['short_token'];
        }
        $j = [
            'url'=>$url4
        ];
        return json_encode($j);
    }

    /**
     * @return string 企业邀请新的合同操作者
     */
    public function new_use(){

        $display_name   = input('mobile');
        $contact_id     = input('contact_id');
        $user_id        = input('user_id');
        //查找添加用户是否注册 网站会员
        $use = Db::name('User')->where(['login_cell'=>$display_name])->find();
        $long_token = Db::name('User')->where(['id'=>$user_id])->value('long_token');
        if(empty($use)){
            $j = [
                'code'=>100,
                'msg'=>"该用户未注册会员，请先注册"
            ];

            return json($j);
        }else{
            //查找对应的合同 一起写的数据 key
            $key = Db::name('write')->where('contract_id',$contact_id)->field('key,short_token')->find();
            $write = new \app\common\model\Write();
            $write->user_id             = $use['id'];
            $write->customer_id         = "";
            $write->long_token         = $long_token;
            $write->short_token = $key['short_token'];
            $write->key         = $key['key'];
            $url4 = "https://yiqixie.com/d/home/".$key['key']."?rt=embedded&API-SESSION-TOKEN=".$key['short_token'];
            $write->contract_id = $contact_id;
            $write->save();
            $con_time = Db::name('Customer')->where(['id'=>$contact_id])->value('create_time');
            //保存数据到邀请 数据表
            //查找邀请者 id
            $used_id = Db::name('User')->where(['login_cell'=>$display_name])->value('id');
            Db::name('yaoqing')->insert([
                'user_id'=>$user_id,
                'used_id'=>$used_id,
                'contract_id'=>$contact_id,
                'create_time'=>$con_time,
            ]);
            $j = [
                'code'=>200,
                'msg'=>'邀请成功',
                'url'=>$url4
            ];
            return json_encode($j);
        }
    }


    public function YiQiXie(){
        $GetUserInfo = input();
        // 判断用户是否已经注册一起写
        $IsHaveYiQiXie = \app\common\model\User::where('id',$GetUserInfo['user_id'])->find();

        if(!empty($IsHaveYiQiXie->long_token)){
            //已存在一起写账号
        }else{
            //执行新增一起写用户
            $AddNewData = $this->yqx->AddNewUser($IsHaveYiQiXie,$GetUserInfo);
        }
    }

    public function delUser(){
        $url = "https://yiqixie.com/e/api/user/delete?api_key=vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178&secret_key=89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9&user_id=4211992320090695072";

        dump(file_get_contents($url));
    }
}
