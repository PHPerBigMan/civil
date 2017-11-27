<?php

namespace app\admin\controller;

use app\common\model\User;
use app\common\model\UserInformation;
use think\Db;
use app\admin\controller\Base;

class Customer extends Base
{

    protected $model;
    protected $user_model;

    public function _initialize(){
        $this->model = New User();
        $this->user_model = New UserInformation();
    }



    public function index()
    {

        $data = [
            'title' => '合同列表(未签订)',
        ];

       return view('Customer/index',$data);
    }

    public function indexed()
    {

        $data = [
            'title' => '合同列表(已签订)',
        ];

        return view('Customer/indexed',$data);
    }

    /**
     * @return \think\response\Json
     * author hongwenyang
     * method description : 未签订的合同
     */

    public function hetong_ajax(){
        $cus_data = Db::table('customer c')->where('status','<>','300')->order('create_time desc')->select();
        $api_key                = "vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178";
        $secret_key             = "89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9";

        foreach($cus_data as $k=>$v){
            $cus_data[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            $cus_data[$k]['update_time'] = date('Y-m-d',$v['update_time']);

            $key = Db::name("Write")->where(['user_id'=>$v['user_id'],'contract_id'=>$v['id']])->field("key,short_token,long_token")->select();
            if(!empty($key)){
                $output1['long_token'] = $key[0]['long_token'];
                $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$output1['long_token'];
                //获取short_token
                $output2 = json_decode(file_get_contents($url2),true);
                $key = Db::name("Write")->where(['contract_id'=>$v['id']])->field("key,short_token,long_token")->select();
                $yqxie = "https://yiqixie.com/d/home/".$key[0]['key']."?rt=embedded&API-SESSION-TOKEN=".$output2['short_token'];
                $readTime = Db::name('back_read_ht')->where('contact_id',$v['id'])->order('create_time','desc')->value('create_time');
                if(($readTime + 86400*1.5) < time() || empty($readTime)){
                    $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$output1['long_token'];
                    //获取short_token
                    $output2 = json_decode(file_get_contents($url2),true);
                    //更新 write数据库中的 short_token
                    Db::name('write')->where('contract_id',$v['id'])->update([
                        'short_token'=>$output2['short_token']
                    ]);
                    $this->readAdd($v['id']);
                }else{
                    $output2['short_token'] = $key[0]['short_token'];
                }

                $cus_data[$k]['caozuo']     = "
                 <a class=\"btn btn-info\" href='$yqxie' target='_blank' onclick='read(".$v['id'].")'>查看合同</a>
                ";
            }else{
                $yqxie = "";
                $cus_data[$k]['caozuo']     = "
                    <a class=\"btn btn-info\" href='$yqxie' >查看合同</a>  
                ";
            }
            $cus_data[$k]['user_name']   = empty($this->user_model->where(['user_id'=>$v['user_id']])->value('company_name')) ? "暂未填写" :$this->user_model->where(['user_id'=>$v['user_id']])->value('company_name');
            $cus_data[$k]['customer_name']   = empty($this->user_model->where(['user_id'=>$v['customer_id']])->value('company_name')) ? "暂未填写" : $this->user_model->where(['user_id'=>$v['customer_id']])->value('company_name');
//            dump($cus_data);die;
            switch ($v['status']){
                case 0:
                    $cus_data[$k]['status'] = "等待上传合同";
                    break;
                case 101:
                    $cus_data[$k]['status'] = "等待双方确认合作";
                    break;
                case 103:
                    $cus_data[$k]['status'] = "等待双方确认合作";
                    break;
                case 102:
                    $cus_data[$k]['status'] = "等待双方确认合作";
                    break;
                case 104:
                    $cus_data[$k]['status'] = "已被拒绝";
                    break;
                case 200:
                    $cus_data[$k]['status'] = "正在签订合同中";
                    break;
                case 210:
                    $cus_data[$k]['status'] = "正在签订合同中";
                    break;
                case 221:
                    $cus_data[$k]['status'] = "正在签订合同中";
                    break;
                case 222:
                    $cus_data[$k]['status'] = "正在签订合同中";
                    break;
                case 105:
                    $cus_data[$k]['status'] = "合作已取消";
                    break;
                case 500:
                    $cus_data[$k]['status'] = "合作已取消";
            }
        }

        return json(['data'=>$cus_data]);
    }

    public function hetong_succcess_ajax(){
        $cus_data = Db::table('customer c')->where(['status'=>300])->order('create_time desc')->select();
        $api_key                = "vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178";
        $secret_key             = "89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9";
        foreach($cus_data as $k=>$v){
            $cus_data[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            $cus_data[$k]['update_time'] = date('Y-m-d',$v['update_time']);
            $cus_data[$k]['user_name']   = $this->user_model->where(['user_id'=>$v['user_id']])->value('company_name');
            $cus_data[$k]['customer_name']   = $this->user_model->where(['user_id'=>$v['customer_id']])->value('company_name');

            $key = Db::name("Write")->where(['user_id'=>$v['user_id'],'contract_id'=>$v['id']])->field("key,short_token,long_token")->select();

            $output1['long_token'] = $key[0]['long_token'];
            // 判断如果合同1天半内查阅过则不用再调取一起写接口
            $readTime = Db::name('back_read_ht')->where('contact_id',$v['id'])->value('create_time');
            if(($readTime + 86400*1.5) < time() || empty($readTime)){

                $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$output1['long_token'];
                //获取short_token
                $output2 = json_decode(file_get_contents($url2),true);
                //更新 write数据库中的 short_token
                Db::name('write')->where('contract_id',$v['id'])->update([
                    'short_token'=>$output2['short_token']
                ]);
                $this->readAdd($v['id']);
            }else{
                $output2['short_token'] = $key[0]['short_token'];
            }
            $yqxie = "https://yiqixie.com/d/home/".$key[0]['key']."?rt=embedded&API-SESSION-TOKEN=".$output2['short_token'];
            $cus_data[$k]['caozuo']     = "
                    <a class=\"btn btn-info\" href='$yqxie' target='_blank' onclick='read(".$v['id'].")'>查看合同</a>  
                ";
//            $cus_data[$k]['caozuo']   = "<a class='btn btn-info' href='".$url4."' target='_blank'>查看合同</a>";
            switch ($v['status']){
                case 300:
                    $cus_data[$k]['status'] = "合同签订成功";
                    break;
            }
        }

        return json(['data'=>$cus_data]);
    }

    public function readAdd($id){
//        $id = input('id');
        $isHave = Db::name('back_read_ht')->where('contact_id',$id)->find();
//        dump($id);die;
        if(empty($isHave)){
            Db::name('back_read_ht')->insert([
                'contact_id'=>$id,
                'create_time'=>time()
            ]);
        }else{
            Db::name('back_read_ht')->where('contact_id',$id)->update([
                'create_time'=>time()
            ]);
        }
    }
}
