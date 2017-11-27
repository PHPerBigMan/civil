<?php
/**
 * Created by 洪文扬
 * User: MR.HONG
 * Date: 2017/3/18
 * Time: 19:50
 */
namespace app\common\model;

use think\Model;

class Customer extends Model
{
    //添加客户
    public function add($company_id,$user_id){
        $cus = new Customer();
        $cus->customer_id = $user_id;
        $cus->user_id       = $company_id;
        $cus->contract_name = "";
        $cus->status        = 0;
        $cus->c_status      = 0;
        $cus->create_time   = time();
        $cus->update_time   = time();
        $cus->save();
    }
}
