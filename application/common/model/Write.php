<?php
/**
 * Created by 洪文扬
 * User: MR.HONG
 * Date: 2017/3/18
 * Time: 19:50
 */
namespace app\common\model;

use think\Db;
use think\Model;

class Write extends Model
{
    /**
     * @param $id                           客户id
     * @param $key                          编写文档的key
     * @param $user_id                      文档创建者一起写的user_id
     * @param $short_token                  文档创建者一起写的short_token
     * @param $contract_name                合同名称
     * @param $customer_user_id             客户ID
     * @param $customer_contract_id         客户对应合同信息ID
     */

    public function customer($id,$key,$user_id,$short_token,$contract_name,$customer_user_id,$customer_contract_id,$create_time,$long_token){

        $write_id = Db::name('write')->where(['user_id'=>$customer_user_id])->find();
        $write = new \app\common\model\Write();
        $api_key                = "vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178";
        $secret_key             = "89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9";
        if(empty($write_id)){
            $user_data = Db::name('User')->where(['id'=>$id])->find();
//        var_dump($user_data);die;
            $c_id                   = $id.rand(1,9999).rand(1000,9999);

            $display_name           = $user_data['little_name'];
            $local_id               = $c_id;
            $email                  = $user_data['wechat_id'].rand(1,999);
            $profile_url            = $user_data['head_img'];

            //获取user_id
            $url = "https://yiqixie.com/e/api/user/create?api_key=".$api_key."&secret_key=".$secret_key."&local_id=".$local_id."&email=".$email."&display_name=".$display_name."&profile_url=".$profile_url;

            $output = json_decode(file_get_contents($url),true);
            $url1 = "https://yiqixie.com/e/api/getrefreshtoken?api_key=".$api_key."&secret_key=".$secret_key."&user_id=".$output['user_id'];

            $output1 = json_decode(file_get_contents($url1),true);


        }else{
            $output2['short_token'] = $write_id['short_token'];
            $output['home_id']      = $write_id['home_id'];
            $output['user_id']      = $write_id['write_id'];
            $output1['long_token']  = $write_id['long_token'];
        }
        $write->long_token = $long_token;
        $write->user_id         = $id;
        $write->write_id        = $output['user_id'];
        $write->customer_id     = $customer_user_id;
        $write->home_id         = $output['home_id'];
        $write->contract_id     = $customer_contract_id;
        //获取long_token

        Db::name('user')->where(['id'=>$id])->update([
            'long_token'=>$output1['long_token']
        ]);


        $url2 = "https://yiqixie.com/e/api/refresh?api_key=".$api_key."&secret_key=".$secret_key."&long_token=".$output1['long_token'];

        //获取short_token
        $output2 = json_decode(file_get_contents($url2),true);

        $url3 = "https://yiqixie.com/d/newapi?id=".$output['home_id']."&API-SESSION-TOKEN=".$output2['short_token'];

        $write->short_token = $output2['short_token'];
        $write->key = $key;

        $add_user_id = $user_id.",".$output['user_id'];
        //设置文档权限

        $url5 = "https://yiqixie.com/e/api/setmetadata/".$key."?add_collaborators=".$add_user_id."&API-SESSION-TOKEN=".$short_token;

        $status = file_get_contents($url5);

        Db::name("customer")->where([
            'customer_id'=>$customer_user_id,
            'user_id'=>$id,
            'create_time'=>$create_time
        ])->update([
            'contract_name' =>$contract_name,
            'status'        =>0
        ]);

        $contract_id     = Db::name("customer")->where([
            'customer_id'=>$customer_user_id,
            'user_id'=>$id,
        ])->value("customer_id");

        $write->customer_id = $contract_id;
        $write->save();
    }
}
