<?php
/**
 * Created by 洪文扬
 * User: MR.HONG
 * Date: 2017/3/18
 * Time: 19:50
 */
namespace app\common\model;

use GuzzleHttp\Client;
use think\Db;
use think\Model;

class YiQiXie extends Model
{
    protected $api_key      = "vg9e83efo5l7s5gnq89b0lmkh77al039bcbc7178";
    protected $secret_key   = "89tr8k2v4or2376gigs0bpqhj52d32c26dkmj2f9";
    public function AddNewUser($GetData,$GetUserInfo){
        $local_id           = $GetData->login_cell;
        $email              = $GetData->login_cell;
        $profile_url        = $GetData->head_img;
        $display_name       = $GetData->login_cell;
        $yqxUrl_1           = "https://yiqixie.com/e/api/user/create?api_key=".$this->api_key."&secret_key=".$this->secret_key."&local_id=".$local_id."&email=".$email."&display_name=".$display_name."&profile_url=".$profile_url;

        //获取 home_id 和 user_id
        $getFileContet_1 = json_decode(file_get_contents($yqxUrl_1));


        //获取 long_token
        $yqxUrl_2 = "https://yiqixie.com/e/api/getrefreshtoken?api_key=".$this->api_key."&secret_key=".$this->secret_key."&user_id=".$getFileContet_1->user_id;

        $getFileContet_2 = json_decode(file_get_contents($yqxUrl_2));

        //获取 short_token
        $yqxUrl_3 = "https://yiqixie.com/e/api/refresh?api_key=".$this->api_key."&secret_key=".$this->secret_key."&long_token=".$getFileContet_2->long_token;

        $getFileContet_3 = json_decode(file_get_contents($yqxUrl_3));

        // 将 home_id user_id long_token  short_token 保存在 user表中
        User::where([
            'login_cell'=>$local_id
        ])->update([
            'home_id'=>$getFileContet_1->home_id,
            'user_id'=>$getFileContet_1->user_id,
            'long_token'=>$getFileContet_2->long_token,
            'short_token'=>$getFileContet_3->short_token
        ]);



        //保存完成之后调用 文档
        $this->SetWord($GetData,$getFileContet_1->home_id,$getFileContet_3->short_token,$GetUserInfo);
    }



    public function SetWord($GetData,$home_id,$short_token,$GetUserInfo){
        //判断是否是上传模板文件
        if(isset($GetUserInfo->muban)){
            //查询模板文件的文件地址
            $Muban =  Db::name('hetong')->where([
                'id'=>$GetUserInfo->htId
            ])->find();
            $contract_name = $Muban['contract_name'];
            $word = $Muban['url'];
        }else{
            $file = request()->file('contract');
            if(!empty($file)) {
                $info = $file->move('uploads/');
                if ($info) {
                    $word = '/uploads/'.$info->getSaveName();
                    //合同名称
                    $contract_name = $_FILES['contract']['name'];
                }
            }
        }

        $yqxUrl_4 = "https://yiqixie.com/d/import/upload/".$home_id."?format=doc&API-SESSION-TOKEN=".$short_token;

        //执行上传合同
        $client = new Client();
        $wordUrl = URL.$word;
        $response = $client->request('POST', $yqxUrl_4, [
            'multipart' => [
                [
                    'name'     => 'Filedata',
                    'contents' => fopen($wordUrl,'r')
                ],
            ]
        ]);

        $body = $response->getBody();
        $remainingBytes = json_decode($body->getContents(),true);
        //上传 成功后的 合同 key
        $key = $remainingBytes['sessionStatus']['additionalData']['docId'];

        // 返回文档地址
        $yqxUrl_5 = "https://yiqixie.com/d/home/".$key."?rt=embedded&API-SESSION-TOKEN=".$short_token;

        //将 $key 保存到 write 数据表
        Db::name('write')->insert([
            'user_id'=>$GetUserInfo['user_id'],
            'customer_id'=>$GetUserInfo['company_id'],
            'contract_id'=>$GetUserInfo['contact_id'],
            'key'=>$key,
            'url'=>$yqxUrl_5
        ]);

        Db::name('write')->insert([
            'user_id'=>$GetUserInfo['company_id'],
            'customer_id'=>$GetUserInfo['user_id'],
            'contract_id'=>Db::name('customer')->where([
                'user_id'=>$GetUserInfo['company_id'],
                'customer_id'=>$GetUserInfo['user_id'],
                'status'=>0
            ])->value('id'),
            'key'=>$key,
            'url'=>$yqxUrl_5
        ]);
        //修改上传合同发起者的列表状态
        Db::name('customer')->where([
            'user_id'=>$GetUserInfo['user_id'],
            'customer_id'=>$GetUserInfo['company_id'],
            'status'=>0
        ])->update([
            'contract_name'=>$contract_name,
            'status'        => 101,
            'c_status'=>1,
        ]);

        //修改对应客户的列表状态
        Db::name("customer")
            ->where(['customer_id'=>$GetUserInfo['user_id'],'user_id'=>$GetUserInfo['company_id'],'status'=>0])
            ->update([
                'contract_name' =>$contract_name,
                'status'        => 102,
                'c_status'=>0,
            ]);



        dump($yqxUrl_5);
    }
}
