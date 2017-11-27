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

class Notice extends Model
{
    /**
     * @param $notice_url  公告图片
     * @param $user_id     用户id
     * @return array       content_url_id  数据表对应id
     * 保存公告图片
     */

    public function save_img($notice_url,$user_id){
        $notice_url_save = new NoticePic();
        $notice_url_save->notice_url    = $notice_url;
        $notice_url_save->user_id       = $user_id;
        $s = $notice_url_save->save();

        $content_url_id = $notice_url_save->id;
//        echo $content_url_id;die;
        if($s){
            $j = [
                'status'        =>200,
                'content_url_id'=>$content_url_id
            ];
        }else{
            $j = [
                'status'=>100,
                'content_url_id'=>""
            ];
        }
//        var_dump($j);die;
        return $j;
    }

    /**
     * @param $company_authentication_name   认证图片路径
     * @param $user_id  用户id
     * @return array
     * 保存认证图片
     */

    public function auth_pic($company_authentication_name,$user_id){
        if(!empty($company_authentication_name)){
            $company_authentication_url                = new AuthenticationPic();
            $company_authentication_url->pic_url       = $company_authentication_name;
            $company_authentication_url->user_id       = $user_id;
            $s = $company_authentication_url->save();
            $company_authentication_url_id = $company_authentication_url->id;
            if($s){
                $j = [
                    'status'=>200,
                    'company_authentication_url_id'=>$company_authentication_url_id
                ];
            }else{
                $j = [
                    'status'=>100,
                    'company_authentication_url_id'=>""
                ];
            }
            return $j;
        }
    }


    public function head_pic($head_pic_name,$user_id)
    {

        if (!empty($head_pic_name)) {

            $head_url = new HeadPic();
            $head_url->pic_url = $head_pic_name;
            $head_url->user_id = $user_id;
            $s = $head_url->save();
            $head_url_id = $head_url->id;
            if ($s) {
                $j = [
                    'status' => 200,
                    'head_url_id' => $head_url_id
                ];
            } else {
                $j = [
                    'status' => 100,
                    'head_url_id' => ""
                ];
            }

            return $j;
        }
    }
}
