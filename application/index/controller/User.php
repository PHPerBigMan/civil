<?php
namespace app\index\controller;

use app\common\model\Category;
use app\common\model\Collection;
use app\common\model\Evaluate;
use app\common\model\EvaluateUser;
use app\common\model\Notice;
use app\common\model\Technology;
use app\common\model\UserCategory;
use app\common\model\UserInformation;
use think\Controller;
use think\Db;

class User extends Controller
{
    /**
     * 分类信息、公司信息
     * @return object
     */

    public function index(){
        //一级分类
        $fId = input('fid');
        //二级分类
        $sId = input('sid');
        //三级分类
        $tId = input('tid');
        $area = input('area');
        $page = input('page');

        if(!empty($area)){
            $url = 'http://restapi.amap.com/v3/geocode/geo?address='.$area.'&output=json&key=68e535a816c751afc9ae25a975cf8459';

            $string = file_get_contents($url);

            $string = json_decode($string);
            if($string->status == 1){
                $jw = explode(',',$string->geocodes[0]->location);

                $avl= $jw[0];
                $evl= $jw[1];
            }
        }

        if(empty($page)){
            if(empty($fId) && empty($sId) && empty($tId)){
                $data = array();
            }else{
                if(empty($tId)){
                    //得到对应的三级分类
                    $t_id = Db::name('Category')->field('category_id')->where(['pid'=>$sId])->select();
                    //根据二级分类查找公司信息

                    if(empty($t_id)){
                        $company_id = Db::name('UserCategory')->field('user_id')->where("category_id",$sId)->select();
                    }else{
                        //所有分类下的公司id
                        $s_company_id = Db::name('UserCategory')->field('user_id')
                            ->where("category_id",$sId)
                            ->select();

                        foreach($t_id as $k=>$v){
                            $t_company_id[$k] = Db::name('UserCategory')->field('user_id')
                                ->where("category_id",$v['category_id'])
                                ->select();
                        }
                        $new = [];
                        $check_t_company_id = "";
                        if(empty($t_company_id)){
                            $check_t_company_id = "";
                        }else{
                            foreach($t_company_id as $k=>$v){
                               if(empty($v[0])){
                                   unset($t_company_id[$k]);
                               }else{
                                   $check_t_company_id[] = $t_company_id[$k];
                               }
                            }
                        }

                        if(!empty($check_t_company_id)){
                            foreach($check_t_company_id as $k=>$v){
                                foreach($v as $k1=>$v1){
                                    $new[] = $v1;
                                }
                            }
                        }

                        if(empty($s_company_id)){
                            $company_id = $new;
                        }else if(empty($new)){
                            $company_id = $s_company_id;
                        }else{
                            $company_id = array_merge($s_company_id,$new);
                        }
                    }


                    if(empty($company_id)){
                        $data            = array();
                        $n_count         = 0;
                    }else{
                        $n_count = "";
                        foreach($company_id as $k=>$v){
                            if(empty($v)){
                                unset($v);
                            }else{
                                $n_count += Db::name('UserInformation')->where(['user_id'=>$v['user_id']])->count();

                                $company_data[$k] = Db::name('UserInformation')
//                                    ->field('company_name,is_authentication,company_address,tel,qq,business,user_id,head_pic,introduce')
                                    ->where("user_id",$v['user_id'])
                                    ->select();
                            }

                        }
                        foreach($company_data as $k=>$v){
                            if(empty($v)){
                                unset($v);
                            }else{
                                $company_data_new[] = $v;
                            }
                        }
                        if(empty($company_data_new)){
                            $data = array();
                        }else{

                            foreach($company_data_new as $k=>$v){
                                if(!empty($v)) {
                                    if (mb_substr($area, 0, 9) == mb_substr($v[0]['company_address'], 0, 9)) {
                                        $c_data[$k]['company_name'] = $v[0]['company_name'];
                                        $c_data[$k]['company_address'] = $v[0]['company_address'];
                                        $c_data[$k]['tel'] = $v[0]['tel'];
                                        $c_data[$k]['qq'] = $v[0]['qq'];
                                        $c_data[$k]['company_id'] = $v[0]['user_id'];
                                        $head_pic = $v[0]['head_pic'];
                                        $c_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                                        $c_id[$k] = $v[0]['business'];
                                        $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                        foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                            $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                        }
                                        $c_data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                        $c_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v[0]['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v[0]['user_id']])->value('level');
                                        $c_data[$k]['introduce'] = $v[0]['introduce'];
                                        $c_data[$k]['id'] = $v[0]['user_id'];
                                    }else{
                                        $a_data[$k]['company_name'] = $v[0]['company_name'];
                                        $a_data[$k]['company_address'] = $v[0]['company_address'];
                                        $a_data[$k]['tel'] = $v[0]['tel'];
                                        $a_data[$k]['qq'] = $v[0]['qq'];
                                        $a_data[$k]['avl'] = $v[0]['avl'];
                                        $a_data[$k]['evl'] = $v[0]['evl'];
                                        $a_data[$k]['company_id'] = $v[0]['user_id'];
                                        $head_pic = $v[0]['head_pic'];
                                        $a_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                                        $c_id[$k] = $v[0]['business'];
                                        $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                        foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                            $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                        }
                                        $a_data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                        $a_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v[0]['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v[0]['user_id']])->value('level');
                                        $a_data[$k]['introduce'] = $v[0]['introduce'];
                                        $a_data[$k]['id'] = $v[0]['user_id'];
                                    }
                                }
                            }

                            if(empty($c_data)){
                                $a_data = $this->getDistance($avl,$evl,$a_data);
                                $data = $a_data;
                            }else if(empty($a_data)){
                                $data = $c_data;
                            }else{
                                $a_data = $this->getDistance($avl,$evl,$a_data);
                                $data = array_merge($c_data,$a_data);
                            }
                        }
                    }
//                    echo "<pre>";
//                    var_dump($data);die;
                }else{
                    $ucData = Db::name('UserCategory')->field('user_id')->where(['category_id'=>$tId])->select();


                    if(empty($ucData)){
                        $c_Id = '';
                    }else{
                        foreach($ucData as $k=>$v){
                            $com_data[$k] = Db::name('UserInformation')->where(['user_id'=>$v['user_id']])->find();
                        }

                    }

                    if(empty($com_data)){
                        $data = array();
                    }else{
                        foreach($com_data as $k=>$v) {
                            if (mb_substr($area, 0, 6) == mb_substr($v['company_address'], 0, 6)) {
                                $a_data[$k]['id'] = $v['user_id'];
                                $head_pic = $v['head_pic'];
                                $a_data[$k]['company_url'] = Db::name('head_pic')->where(['id' => $head_pic])->value('pic_url');
                                $a_data[$k]['company_video'] = $v['company_video'];
                                $a_data[$k]['company_name'] = $v['company_name'];
                                $a_data[$k]['company_address'] = $v['company_address'];
                                $a_data[$k]['is_authentication'] = $v['is_authentication'];
                                $a_data[$k]['tel'] = $v['tel'];
                                $a_data[$k]['qq'] = $v['qq'];
                                $business[$k] = $v['business'];
                                $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$business[$k]")->select();
                                foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                    $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                }
                                $a_data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                $a_data[$k]['introduce'] = $v['introduce'];
                                $a_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                            }else{
                                $c_data[$k]['id'] = $v['user_id'];
                                $head_pic = $v['head_pic'];
                                $c_data[$k]['company_url'] = Db::name('head_pic')->where(['id' => $head_pic])->value('pic_url');
                                $c_data[$k]['company_video'] = $v['company_video'];
                                $c_data[$k]['company_name'] = $v['company_name'];
                                $c_data[$k]['company_address'] = $v['company_address'];
                                $c_data[$k]['is_authentication'] = $v['is_authentication'];
                                $c_data[$k]['tel'] = $v['tel'];
                                $c_data[$k]['qq'] = $v['qq'];
                                $c_data[$k]['avl'] = $v['avl'];
                                $c_data[$k]['evl'] = $v['evl'];
                                $business[$k] = $v['business'];
                                $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$business[$k]")->select();
                                foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                    $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                }
                                $c_data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                $c_data[$k]['introduce'] = $v['introduce'];
                                $c_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                            }
                        }
                        if(empty($a_data)){
                            $c_data = $this->getDistance($avl,$evl,$c_data);
                            $data = $c_data;
                        }else if(empty($c_data)){
                            $data = $a_data;
                        }else{
                            $c_data = $this->getDistance($avl,$evl,$c_data);
                            $data = array_merge($a_data,$c_data);
                        }
                    }
                }
            }
            $j = [
                'data'=>$data,
            ];
        }else{
            if(empty($tId)){
                if(empty($sId)){

                    //根据一级分类查找所有的企业信息
                    $s_id = Db::name('Category')->field('category_id')->where('pid',$fId)->select();
                    if(empty($s_id)){
                        $search_id = $fId;
                    }else{

                        foreach($s_id as $k=>$v){
                            $t_Id[$k] = Db::name('Category')->field('category_id')->where('pid',$v['category_id'])->select();
                        }

                        foreach($s_id as $k=>$v){
                            $s_use_id[$k] = $v['category_id'];
                        }
                        foreach($t_Id as $k=>$v){
                            if(empty($v)){
                                unset($v);
                            }else{
                                foreach ($v as $k1=>$v1){
                                    $search_id_use[] = $v1['category_id'];
                                }
                            }
                        }
                    }

                    if(empty($search_id_use)){
                        $search_id = $s_use_id;
                    }else{
                        $search_id = array_merge($s_use_id,$search_id_use);
                    }

                    foreach($search_id as $k=>$v){
                        $company_id[$k] = Db::name('UserCategory')->field('user_id')->where("category_id",$v)->select();
                    }
                    if(empty($company_id)){
                        $data            = array();
                        $n_count         = 0;
                    }else {
                        $n_count = "";
                        foreach ($company_id as $k => $v) {
                            if (empty($v)) {
                                unset($v);
                            } else {
//                                $n_count += Db::name('UserInformation')->where(['user_id'=>$v['user_id']])->count();
                                foreach($v as $v1){
                                    $company_data[] = Db::name('UserInformation')
//                                        ->field('company_name,is_authentication,company_address,tel,qq,business,user_id,head_pic')
                                        ->where("user_id", $v1['user_id'])
                                        ->find();
                                }
                            }
                        }
                        if (empty($company_data)) {
                            $data = array();
                        } else {
                            foreach ($company_data as $k => $v) {
                                if (!empty($v)) {
                                    $a_data[$k]['company_name'] = $v['company_name'];
                                    $a_data[$k]['company_address'] = $v['company_address'];
                                    $a_data[$k]['tel'] = $v['tel'];
                                    $a_data[$k]['qq'] = $v['qq'];
                                    $a_data[$k]['company_id'] = $v['user_id'];
                                    $a_data[$k]['avl'] = $v['avl'];
                                    $a_data[$k]['evl'] = $v['evl'];
                                    $head_pic = $v['head_pic'];
                                    $a_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                                    $c_id[$k] = $v['business'];
                                    $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                    foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                        $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                    }
                                    $a_data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                    $a_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 : Db::name('User')->where(['id'=>$v['user_id']])->value('level');

                                }
                            }
//                            dump($a_data);die;
                            if (!empty($a_data)) {
                                $c_data = $this->getDistance($avl,$evl,$a_data);
                            }
//                            sort($c_data);
                            $n_count = count($c_data);
                            $n_num = ceil($n_count/10);
                            if($page<=1 && $n_count<=10){
                                for($i=0;$i<$n_count;$i++){
                                    $data[$i] = $c_data[$i];
                                }
                            }else if($page>1 &&  $n_count>10 ){
                                if($n_count%10 == 0){
                                    for($i=($page-1)*10;$i<($n_count-1);$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }else if($n_count%10 != 0 && $page!=$n_num){
                                    for($i=($page-1)*10;$i<(($page-1)*10)+10;$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }else if($n_count%10 != 0 && $page == $n_num){
                                    for($i=($page-1)*10;$i<$n_count;$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }
                            }else if($page<=1 && $n_count>10){
                                for($i=0;$i<10;$i++){
                                    $data[$i] = $c_data[$i];
                                }
                            }
                        }
                    }
//                    var_dump($data);die;
                }else{

                    //根据二级分类查询企业
                    $S_data = Db::name('Category')->field("category_id")->where("pid",$sId)->select();

                    if(empty($S_data)){
                        $company_id = Db::name('UserCategory')->field('user_id')->where("category_id",$sId)->select();
                        if(empty($company_id)){
                            $data            = array();
                            $n_count         = 0;
                        }else {
                            $n_count = "";
                            foreach ($company_id as $k => $v) {
                                if (empty($v)) {
                                    unset($v);
                                } else {
                                    $n_count += Db::name('UserInformation')->where(['user_id' => $v['user_id']])->count();

                                    $company_data[$k] = Db::name('UserInformation')
//                                        ->field('company_name,is_authentication,company_address,tel,qq,business,user_id,head_pic')
                                        ->where("user_id", $v['user_id'])
                                        ->find();
                                }
                            }
                            foreach ($company_data as $k => $v) {
                                if (empty($v)) {
                                    unset($v);
                                } else {
                                    $company_data_new[$k] = $v;
                                }
                            }
                            if (empty($company_data_new)) {
                                $data = array();
                            } else {
                                $company_data_new = getArrayUniqueByKeys($company_data_new);

                                foreach ($company_data_new as $k => $v) {
                                    $b_data[$k]['company_name'] = $v['company_name'];
                                    $b_data[$k]['company_address'] = $v['company_address'];
                                    $b_data[$k]['tel'] = $v['tel'];
                                    $b_data[$k]['qq'] = $v['qq'];
                                    $b_data[$k]['company_id'] = $v['user_id'];
                                    $b_data[$k]['avl'] = $v['avl'];
                                    $b_data[$k]['evl'] = $v['evl'];
                                    $head_pic = $v['head_pic'];
                                    $b_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                                    $c_id[$k] = $v['business'];
                                    $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                    foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                        $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                    }
                                    $b_data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                    $b_data[$k]['is_authentication'] = Db::name('User')->where(['id' => $v['user_id']])->value('level') == 4 ? 0 : Db::name('User')->where(['id' => $v['user_id']])->value('level');

                                }
                                $b_data = $this->getDistance($avl, $evl, $b_data);
                                $c_data = $b_data;
                                $n_count = count($c_data);
                                if (count($c_data) != 0) {
                                    $n_num = ceil($n_count / 10);
                                    if ($page <= 1 && $n_count <= 10) {

                                        for ($i = 0; $i < $n_count; $i++) {
                                            $data[$i] = $c_data[$i];
                                        }
                                    } else if ($page > 1 && $n_count > 10) {
                                        if ($n_count % 10 == 0) {
                                            for ($i = ($page - 1) * 10; $i < ($n_count - 1); $i++) {
                                                $data[$i] = $c_data[$i];
                                            }
                                        } else if ($n_count % 10 != 0 && $page != $n_num) {
                                            for ($i = ($page - 1) * 10; $i < (($page - 1) * 10) + 10; $i++) {
                                                $data[$i] = $c_data[$i];
                                            }
                                        } else if ($n_count % 10 != 0 && $page == $n_num) {
                                            for ($i = ($page - 1) * 10; $i < $n_count; $i++) {
                                                $data[$i] = $c_data[$i];
                                            }
                                        }
                                    } else if ($page <= 1 && $n_count > 10) {
                                        for ($i = 0; $i < 10; $i++) {
                                            $data[$i] = $c_data[$i];
                                        }
                                    }
                                } else {
                                    $data = array();
                                }
                            }
                        }
                    }else{
                        //所有分类下的公司id
                        $s_company_id = Db::name('UserCategory')->field('user_id')
                            ->where("category_id",$sId)
                            ->select();

                        foreach($S_data as $k=>$v){
                            $t_company_id[$k] = Db::name('UserCategory')->field('user_id')
                                ->where("category_id",$v['category_id'])
                                ->find();
                        }
                        foreach($t_company_id as $k=>$v){
                            if($v[0] = ""){
                                unset($t_company_id[$k]);
                                $check_t_company_id[$k] = "";
                            }else{
                                $check_t_company_id[$k] = $t_company_id[$k];
                            }
                        }


                        if(empty($s_company_id)){
                            $company_id = $check_t_company_id;
                        }else if(empty($check_t_company_id)){
                            $company_id = $s_company_id;
                        }else{
                            $company_id = array_merge($s_company_id,$check_t_company_id);
                        }


                        $n_count = "";
                        if(empty($company_id)){
                            $data            = array();
                        }else{
                            foreach($company_id as $k=>$v){
                                if(!empty($v)){
                                    $n_count += Db::name('UserInformation')->where(['user_id'=>$v['user_id']])->count();
                                    $company_data = Db::name('UserInformation')
//                                        ->field('company_name,is_authentication,company_address,tel,qq,business,user_id,head_pic')
                                        ->where("user_id",$v['user_id'])
                                        ->select();
                                }
                            }

                            if(empty($company_data)){
                                $data = array();
                            }else{
                                $company_data = getArrayUniqueByKeys($company_data);
                                foreach($company_data as $k=>$v){
                                    if(!empty($v)){
                                        $b_data[$k]['company_name']         = $v['company_name'];
                                        $b_data[$k]['company_address']      = $v['company_address'];
                                        $b_data[$k]['tel']                  = $v['tel'];
                                        $b_data[$k]['qq']                   = $v['qq'];
                                        $b_data[$k]['company_id']           = $v['user_id'];
                                        $head_pic                           = $v['head_pic'];
                                        $b_data[$k]['avl']                  = $v['avl'];
                                        $b_data[$k]['evl']                  = $v['evl'];
                                        $b_data[$k]['company_url']          = empty(Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url')) ? "" :Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url');
                                        $c_id[$k]                           = $v['business'];
                                        $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                        foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                            $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                        }
                                        $b_data[$k]['business']             = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                        $b_data[$k]['is_authentication']        = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');

                                    }
                                }
                                $b_data = $this->getDistance($avl,$evl,$b_data);
                                $c_data = $b_data;

                                $n_num = ceil($n_count/10);
                                if($page<=1 && $n_count<=10){
                                    for($i=0;$i<$n_count;$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }else if($page>1 &&  $n_count>10 ){
                                    if($n_count%10 == 0){
                                        for($i=($page-1)*10;$i<($n_count-1);$i++){
                                            $data[$i] = $c_data[$i];
                                        }
                                    }else if($n_count%10 != 0 && $page!=$n_num){
                                        for($i=($page-1)*10;$i<(($page-1)*10)+10;$i++){
                                            $data[$i] = $c_data[$i];
                                        }
                                    }else if($n_count%10 != 0 && $page == $n_num){
                                        for($i=($page-1)*10;$i<$n_count;$i++){
                                            $data[$i] = $c_data[$i];
                                        }
                                    }
                                }else if($page<=1 && $n_count>10){
                                    for($i=0;$i<10;$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }

                            }


                        }
                    }
                }
            }else{

                $company_id = Db::name('UserCategory')->field('user_id')->where('category_id',$tId)->select();
                if(empty($company_id)){
                    $n_count = 0;
                    $data = array();
                }else{
                    $n_count = "";
                    foreach($company_id as $k=>$v){
                        $n_count += Db::name('UserInformation')->where(['user_id'=>$v['user_id']])->count();
                        $company_data[$k] = Db::name('UserInformation')
                            ->where("user_id",$v['user_id'])
                            ->select();
                    }
                    foreach($company_data as $k=>$v){
                        if(!empty($v)){
                            $c_data[$k]['company_name']         = $v[0]['company_name'];
                            $c_data[$k]['company_address']      = $v[0]['company_address'];
                            $c_data[$k]['tel']                  = $v[0]['tel'];
                            $c_data[$k]['qq']                   = $v[0]['qq'];
                            $c_data[$k]['company_id']                   = $v[0]['user_id'];
                            $c_data[$k]['avl']                  = $v[0]['avl'];
                            $c_data[$k]['evl']                  = $v[0]['evl'];
                            $head_pic = $v[0]['head_pic'];
                            $c_data[$k]['company_url']          = empty(Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url')) ? "" :Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url');
                            $c_id[$k]                           = $v[0]['business'];
                            $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                            foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                            }
                            $c_data[$k]['business']             = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                            $c_data[$k]['is_authentication']    = Db::name('User')->where(['id'=>$v[0]['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v[0]['user_id']])->value('level');
                        }
                    }

                    $n_num = ceil($n_count/10);
                    if($page<=1 && $n_count<=10){
                        for($i=0;$i<$n_count;$i++){
                            $data[$i] = $c_data[$i];
                        }
                    }else if($page>1 &&  $n_count>10 ){
                        if($n_count%10 == 0){
                            for($i=($page-1)*10;$i<($n_count-1);$i++){
                                $data[$i] = $c_data[$i];
                            }
                        }else if($n_count%10 != 0 && $page!=$n_num){
                            for($i=($page-1)*10;$i<(($page-1)*10)+10;$i++){
                                $data[$i] = $c_data[$i];
                            }
                        }else if($n_count%10 != 0 && $page == $n_num){
                            for($i=($page-1)*10;$i<$n_count;$i++){
                                $data[$i] = $c_data[$i];
                            }
                        }
                    }else if($page<=1 && $n_count>10){
                        for($i=0;$i<10;$i++){
                            $data[$i] = $c_data[$i];
                        }
                    }
                }
//                var_dump($data);die;
            }
            $j = [
                'data'=>$data,
                'count'=>$n_count
            ];
        }

        return json_encode($j);
    }

    /**
     * @param $lat1 纬度
     * @param $lng1 经度
     * @param $city
     * @return mixed
     * author hongwenyang
     * method description :
     */


    function getDistance($lon1,$lat1,$city)
    {
        $radius = 6378.137;
        $rad = floatval(M_PI / 180.0);

        //纬度
        $lat1 = floatval($lat1) * $rad;
        //经度
        $lon1 = floatval($lon1) * $rad;
        foreach($city as $k=>$v){
            $lat2 = floatval($v['evl']) * $rad;
            $lon2 = floatval($v['avl']) * $rad;
            $theta = $lon2 - $lon1;
            $dist = acos(sin($lat1) * sin($lat2) +cos($lat1) * cos($lat2) * cos($theta));
            if ($dist < 0 ) {
                $dist += M_PI;
            }
            $dist = $dist * $radius;
            $city[$k]['distance'] = $dist;
        }
        $len = count($city);
        sort($city);
        for($i=1;$i<$len;$i++)
        {
            for($j=$len-1;$j>=$i;$j--)
            {
                if($city[$j]['distance']<$city[$j-1]['distance'])
                {//如果是从大到小的话，只要在这里的判断改成if($b[$j]>$b[$j-1])就可以了
                    $tmp=$city[$j];
                    $city[$j]=$city[$j-1];
                    $city[$j-1]=$tmp;
                }
            }
        }

        return $city;
    }

    /**
     * 获取每一个企业详细信息页
     * 企业介绍这块使用富文本
     * 移动端使用
     */
    public function read()
    {

        // 企业id
        $id             = input('company_id');
        //公司基本信息
        $ddata           = UserInformation::where(['user_id'=>$id])->select();
        if(empty($ddata)){
            $company_url ='';
            $head_pic = [];
            $company_video = '';
            $company_name = '';
            $tel = '';
            $c_id = '';
            $category = '';
        }else{
            foreach($ddata as $k=>$v){
                $head_pic1 = $v->head_pic;
                $head_pic          = empty(Db::name("head_pic")->where(['id'=>$head_pic1])->value('pic_url')) ? "" :Db::name("head_pic")->where(['id'=>$head_pic1])->value('pic_url');
                $company_url    = empty($v->company_url) ? '' :explode(',',$v->company_url);
//                $head_pic       = empty($v->head_pic) ? "": $v->head_pic;
                $company_video  = $v->company_video;
                $introduce  = $v->introduce;
                $company_name   = $v->company_name;
                $tel            = $v->tel;
                $c_id           = $v->business;
            }

            if(empty($c_id)){
                $category = '';
            }else{
                $c_data = Category::where("category_id in ($c_id)")->select();
                foreach ($c_data as $k=>$v){
                    $cdata[] = $v->c_name;
                }
                $cdata = implode(',',$cdata);
                $category = $cdata;
            }

        }
        $j = [
            'introduce'=>$introduce,
            'head_pic'=>$head_pic,
            'company_video'=>$company_video,
            'company_name'=>$company_name,
            'tel'=>$tel,
            'business'=>$category,
        ];
         return json_encode($j);
    }

    /**
     * 评价
     * @return string
     */

    public function evaluate(){
        $id             = input('company_id');
        $evaluate       = Db::name('Evaluate')->where(['valuator_id'=>$id])->select();
//        echo "<pre>";
//        var_dump(date("Y-m-d",$evaluate[0]['create_time']));die;
        if(empty($evaluate)){
            $evaluate_info[0]['company_name'] = '';
            $evaluate_info[0]['content'] = '';
            $evaluate_info[0]['time'] = '';
            $evaluate_info[0]['valuator_id'] = '';
        }else{
            foreach($evaluate as $k=>$v){
                $evaluate_info[$k]['valuator_id'] = $v['valuator_id'];
                $evaluate_info[$k]['content'] = $v['evaluate_content'];
                $evaluate_info[$k]['time'] = date("Y-m-d",$v['create_time']);
                $evaluator_id[$k]           = $v['evaluator_id'];
                $cell[$k]                       = Db::name('User')->where(['id'=>$evaluator_id[$k]])->value('login_cell');
                $new_cell[$k]                   = $str = substr_replace($cell[$k],'****',3,4);
                $evaluate_info[$k]['company_name'] = empty(Db::name('UserInformation')->where("user_id",$evaluator_id[$k])->value('company_name'))
                ?   $new_cell[$k] : Db::name('UserInformation')->where("user_id",$evaluator_id[$k])->value('company_name');
            }

        }

        $j = [
            'data'=>$evaluate_info
        ];
        return json_encode($j);
    }

    /**
     * 对应子分类的工艺介绍
     */

    public function technology(){
        $category_id = input('fid');
        $page = input('page');

        if(empty($category_id)){
            $data = array();
            $count = 0;
        }else{
            $son_id = Db::name('Category')->field('category_id,c_name')->where([
                'category_id'=>$category_id
            ])->select();

            $count = Db::name("technology")->where(['category_id'=>$category_id])->count();
            if(empty($son_id)){
                $data = array();
            }else{
                foreach($son_id as $k=>$v){
                    $c_id = $v['category_id'];
                    $data['category_id'] = $v['category_id'];
                    $tech[$k] = empty(Db::name('technology')->field('id,title,content')->where(['category_id'=>$c_id])->limit($page-1,$page)->select()) ? "暂无数据" :Db::name('technology')->field('id,title,content')->where(['category_id'=>$c_id])->limit($page-1,$page)->select();;

                    if($tech[$k] == "暂无数据"){
                        $data['tech']['content'] = '暂无工艺介绍';
                    }else{
                        $data['tech'] = $tech[$k][0];
                    }
                }
            }
        }

        $j = [
            'count'=>$count,
            'data'=>$data
        ];
        return json_encode($j);
    }

    /**
     * 根据一级分类得到的公告信息   最新公告
     * @return object
     */

    public function notice(){
        $time = time();
        $category_id        = input('fid');
        $page               = input('page');
        $hpage              = input('hpage');
        //最新数据
        if(empty($page) && empty($hpage)){
            //最新公告
            $n_data = Db::name('notice')->where(['category_id'=>$category_id])->order('create_time desc')->select();
            if(empty($n_data)){
                $data = array();
            }else{
                foreach($n_data as $k=>$v){
                    $data[$k]['user_name']              = empty($v['user_name']) ? "" : $v['user_name'];
                    $data[$k]['notice_content']         = empty($v['notice_content']) ? "" :$v['notice_content'];
                    $data[$k]['notice_video']           = empty($v['notice_video']) ? "" :$v['notice_video'];
                    $notice_url[$k]                     = empty($v['notice_url'])?'':explode(',',$v['notice_url']);
                    if(empty($notice_url)){
                        $data[$k]['notice_url'] = array();
                    }else{
                        foreach($notice_url as $k1=>$v1){
                            if(!empty($v1)){
                                foreach($v1 as $k2=>$v2){
                                    $data[$k1]['notice_url'][$k2] = Db::name("notice_pic")->where(['id'=>$v2])->value('notice_url');
                                }
                            }
                        }
                    }
                    $data[$k]['id'] = empty($v['id']) ? "" :$v['id'];
                    $data[$k]['create_time'] = date("Y-m-d",$v['create_time']);
                }
            }

            //最热公告
            $h_data_c = Db::name('notice')->where(['category_id'=>$category_id,'is_top'=>1])
                                            ->where("stop_time > $time")->select();
            if(empty($h_data_c)){
                $h_data = array();
            }else{
                foreach($h_data_c as $k=>$v){
                    $h_data[$k]['user_name']            = empty($v['user_name']) ? "" : $v['user_name'];
                    $h_data[$k]['notice_video']         = empty($v['notice_content']) ? "" :$v['notice_content'];
                    $h_data[$k]['notice_video']         = empty($v['notice_video']) ? "" :$v['notice_video'];
                    $notice_url_demo[$k]                     = empty($v['notice_url'])?'': explode(',',$v['notice_url']);
                    if(empty($notice_url_demo)){
                        $h_data[$k]['notice_url'] = array();
                    }else{
                        foreach($notice_url_demo as $k1=>$v1){
                            if(!empty($v1)){
                                foreach($v1 as $k2=>$v2){
                                    $h_data[$k1]['notice_url'][$k2] = Db::name("notice_pic")->where(['id'=>$v2])->value('notice_url');
                                }
                            }
                        }
                    }
                    $h_data[$k]['id']           = empty($v['id']) ? "" :$v['id'];
                    $h_data[$k]['create_time']  = date("Y-m-d",$v['create_time']);
                }
            }
            $j = [
                'data'=>$data,
                'hdata'=>$h_data
            ];
        }else{
            if(empty($page)){
                //最热公告
                $h_count = Db::name('notice')->where(['category_id'=>$category_id,'is_top'=>1])->where("stop_time > $time")->count();

                $h_num = ceil($h_count/10);
                if($hpage<=1){
                    if($h_count < 10){
                        $h_count = $h_count;
                        $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->limit(0,$h_count)->order("create_time desc")->select();
                        if(empty($h_data)){
                            $hdata[0]['notice_content']         = '';
                            $hdata[0]['create_time']            = '';
                            $hdata[0]['notice_url']             = array();
                            $hdata[0]['notice_video']           = '';
                            $hdata[0]['company_name']           = '';
                        }
                    }else{
                        $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->limit(0,10)->order("create_time desc")->select();
                    }
                }else if($hpage>1 && $hpage <= ($h_num-1) && $h_num>1){
                    $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->limit(($hpage-1)*10,10)->order("create_time desc")->select();
                }else if($hpage==$h_num){
                    $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->limit($h_count-($h_num-1)*10)->order("create_time desc")->select();
                }
                foreach($h_data as $k=>$v){
                    $user_Id[$k] = $v->user_id;
                    $hdata[$k]['notice_content'] = $v->notice_content;
                    $hdata[$k]['create_time']=$v->create_time;
                    $notice_url_demo[$k] =empty($v->notice_url)?'':explode(',',$v->notice_url);
                    if(empty($notice_url_demo)){
                        $hdata[$k]['notice_url'] = array();
                    }else{
                        foreach($notice_url_demo as $k1=>$v1){
                            foreach($v1  as $k2=>$v2){
                                $hdata[$k1]['notice_url'][$k2] = Db::name("notice_pic")->where(['id'=>$v2])->value('notice_url');
                            }
                        }
                    }
                    $hdata[$k]['notice_video']=$v->notice_video;
                    $hdata[$k]['company_name'] = Db::name('UserInformation')->where("user_id",$v->user_id)->value('company_name');
                }
                $j = [
                    'hdata'=>$hdata,
                    'hcount'=>$h_count
                ];
            }else{
                //总数
                $n_count = Db::name('notice')->where(['category_id'=>$category_id])->count();
                //页码
                $n_num = ceil($n_count/10);
                if($page<=1){
                    if($n_count < 10){
                        $n_count = $n_count;
                        $n_data = Notice::where(['category_id'=>$category_id])->limit(0,$n_count)->order("create_time desc")->select();
                        if(empty($n_data)){
                            $data = array();
                        }
                    }else{
                        $n_data = Notice::where(['category_id'=>$category_id])->limit(0,10)->order("create_time desc")->select();
                    }
                }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
                    $n_data = Notice::where(['category_id'=>$category_id])->limit(($page-1)*10,10)->order("create_time desc")->select();
                }else if($page==$n_num){
                    $n_data = Notice::where(['category_id'=>$category_id])->limit($n_count-($n_num-1)*10)->order("create_time desc")->select();
                }
//                var_dump($n_data);die;
                if(!empty($n_data)){
                    foreach($n_data as $k=>$v){
                        $user_id[$k]                        = $v->user_id;
                        $data[$k]['notice_content']         = $v->notice_content;
                        $data[$k]['create_time']            = $v->create_time;
                        $notice_url[$k] =empty($v->notice_url)?'':explode(',',$v->notice_url);
                        if(empty($notice_url)){
                            $data[$k]['notice_url'] = array();
                        }else{
                            foreach($notice_url as $k1=>$v1){
                                if(empty($v1[0])){
                                    unset($notice_url[$k1]);
                                }else{
                                    foreach($v1  as $k2=>$v2){
                                        $data[$k1]['notice_url'][$k2] = Db::name("notice_pic")->where(['id'=>$v2])->value('notice_url');
                                    }
                                }
                            }
                        }
                        $data[$k]['notice_video']   =$v->notice_video;
                        $data[$k]['company_name']   = empty(Db::name('UserInformation')->where("user_id",$v->user_id)->value('company_name')) ? "" :Db::name('UserInformation')->where("user_id",$v->user_id)->value('company_name');
                    }
                }
                //最热公告
                $h_count = Db::name('notice')->where(['category_id'=>$category_id,'is_top'=>1])->where("stop_time > $time")->count();

                $h_num = ceil($h_count/10);
                $time = time();
                if($hpage<=1){
                    if($h_count < 10){
                        $h_count = $h_count;
                        $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->where("stop_time > $time")->limit(0,$h_count)->order("create_time desc")->select();
                        if(empty($h_data)){
                            $hdata[0]['notice_content']         = '';
                            $hdata[0]['create_time']            = '';
                            $hdata[0]['notice_url']             = array();
                            $hdata[0]['notice_video']           = '';
                            $hdata[0]['company_name']           = '';
                        }
                    }else{
                        $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->where("stop_time > $time")->limit(0,10)->order("create_time desc")->select();
                    }
                }else if($hpage>1 && $hpage <= ($h_num-1) && $h_num>1){
                    $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->where("stop_time > $time")->limit(($hpage-1)*10,10)->order("create_time desc")->select();
                }else if($hpage==$h_num){
                    $h_data = Notice::where(['category_id'=>$category_id,'is_top'=>1])->where("stop_time > $time")->limit($h_count-($h_num-1)*10)->order("create_time desc")->select();
                }

                foreach($h_data as $k=>$v){
                    $user_Id[$k] = $v->user_id;
                    $hdata[$k]['notice_content'] = $v->notice_content;
                    $hdata[$k]['create_time']=$v->create_time;
                    $notice_url_demo[$k] = empty($v->notice_url)?'':explode(',',$v->notice_url);
                    if(empty($notice_url_demo)){
                        $hdata[$k]['notice_url'] = array();
                    }else{
                        foreach($notice_url_demo as $k1=>$v1){
                            if(empty($v1[0])){
                                unset($notice_url[$k1]);
                            }else{

                                foreach($v1  as $k2=>$v2){
                                    $hdata[$k1]['notice_url'][$k2] = Db::name("notice_pic")->where(['id'=>$v2])->value('notice_url');
//                                    $pic[$k1][$k2] = Db::name("notice_pic")->where(['id'=>$v2])->value('notice_url');
                                }
                            }
                        }
                    }
                    $hdata[$k]['notice_video']=$v->notice_video;
                    $hdata[$k]['company_name']   = empty(Db::name('UserInformation')->where("user_id",$v->user_id)->value('company_name')) ? "" :Db::name('UserInformation')->where("user_id",$v->user_id)->value('company_name');
                }

//                echo "<pre>";
//                var_dump($hdata);die;
                $j = [
                    'hdata'=>$hdata,
                    'data'=>$data,
                    'count'=>$n_count,
                ];
            }
        }

        return json_encode($j);
    }


    public function category(){
        $c = new Category();
        $data = $c->select();
        $new = $c->make_tree1($data);
        $j = [
            'data'=>$new
        ];
        return json_encode($j);
    }

    /**
     * 收藏
     * @return string
     */

    public function collection(){
        $user_id            = input('user_id');
        $company_id         = input('id');
        $check              = input('check');
        $c = new Collection();
        if(empty($check)){
            $is = $c->where([
                'collection_user_id'=>$user_id,
            ])->where(['collectioned_user_id'=>$company_id])->select();
            if(empty($is)){
                $c->collection_user_id = $user_id;
                $c->collectioned_user_id  = $company_id;
                $c->create_time  = time();
                $s = $c->save();
                if($s){
                    $status = 200;
                }else{
                    $status = 100;
                }
            }else{

                $status = 300;
                $c->where([
                    'collection_user_id'=>$user_id,
                ])->where(['collectioned_user_id'=>$company_id])->delete();
            }

            $j= [
                'status'=>$status
            ];
        }else{
            $is = $c->where([
                'collection_user_id'=>$user_id,
            ])->where(['collectioned_user_id'=>$company_id])->select();
            if(empty($is)){
                $code = 0;
                $msg = "未收藏";
            }else{
                $code = 1;
                $msg = "已收藏";
            }
            $j = [
                'code'=>$code,
                'msg'=>$msg
            ];
        }
        return json_encode($j);
    }

    /**
     * 发送评价
     * @return string
     */

    public function s_evaluate(){

        //查询 互评的次数
        $e_time = Db::name('UserEvaluate')->where(['evaluate_id'=>input('user_id'),'evaluated_id'=>input('company_id')])->value('evaluate_time');
        if($e_time>=1){
            $e = new Evaluate();
            $e->evaluate_content =  input('content');
            $e->evaluator_id  = input('user_id');
            $e->valuator_id   = input('company_id');
            $e->create_time = time();
            $s = $e->save();
            if($s){
                $status = 200;
                $msg = "评级成功";
                //减少一次互评的机会
                Db::name('UserEvaluate')->where(['evaluate_id'=>input('user_id'),'evaluated_id'=>input('company_id')])->setDec('evaluate_time');
                Db::name('user')->where(['id'=>input('user_id')])->setInc('evaluated_time');
            }
        }else if($e_time<1){
            $status = 100;
            $msg = "评价次数不足";
        }



        $j = [
            'status'=>$status,
            'msg'=>$msg
        ];
        return json_encode($j);
    }

    /**
     * pc端使用
     * @return string
     */

    public function web_read()
    {

        // 企业id
        $id             = input('company_id');
        //公司基本信息
        $ddata           = UserInformation::where(['user_id'=>$id])->select();
        if(empty($ddata)){
            $company_url ='';
            $head_pic = '';
            $company_video = '';
            $company_name = '';
            $tel = '';
            $c_id = '';
            $category = '';
            $user_name = '';
            $name = '';
            $category = "";
            $company_address = '';
            $introduce = "";
            $qq = "";
        }else{
            foreach($ddata as $k=>$v){
                $company_url                =empty($v->company_url) ? '' :explode(',',$v->company_url);
                $head_pic1                  = $v->head_pic;
                $head_pic                   = empty(Db::name("head_pic")->where(['id'=>$head_pic1])->value('pic_url')) ? "" :Db::name("head_pic")->where(['id'=>$head_pic1])->value('pic_url');

                $company_video              = $v->company_video;
                $company_name               = $v->company_name;
                $tel                        = $v->tel;
                $qq                         = $v->qq;
                $name                       = $v->name;
                $c_id                       = $v->business;
                $company_address            = $v->company_address;
                $user_name                  = Db::name("User")->where(["id"=>$v->user_id])->value("login_cell");
                $introduce                  = $v->introduce;
                $is_authentication          = Db::name('User')->where(['id'=>$v->user_id])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v->user_id])->value('level');
            }
            $cdata = [];
            if(empty($c_id)){
                $category = "";
            }else{
                $c_data = Category::where("category_id in ($c_id)")->select();
                foreach ($c_data as $k=>$v){
                    $cdata[] = $v->c_name;
                }
                $cdata = implode(',',$cdata);
                $category = $cdata;
            }

        }

        $j = [
            'introduce'             =>$introduce,
            'head_pic'              =>$head_pic,
            'company_video'         =>$company_video,
            'company_name'          =>$company_name,
            'tel'                   =>$tel,
            'category'              =>$category,
            'name'                  =>$name,
            'user_name'             =>$user_name,
            'company_address'       =>$company_address,
            'qq'                    =>$qq,
            'is_authentication'     =>$is_authentication
        ];
        return json_encode($j);
    }

    /**
     * 根据二级分类  查到对应的三级分类
     */

    public function get_three(){
        $sId = input('sid');
        $s_data = Db::name('Category')->field('category_id,c_name')->where(['pid'=>$sId])->select();
        if(empty($s_data)){
            $data = array();
        }else{
            foreach($s_data as $k=>$v){
                $data[$k]['category_id'] = $v['category_id'];
                $data[$k]['c_name'] = $v['c_name'];
            }
        }

        $j = [
            'data'=>$data
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 验证企业是否已被收藏
     */

    public function check_col(){
        $user_id = input('user_id');
        $company_id = input('company_id');
        $c = Db::name('collection')->where([
            'collection_user_id'=>$user_id,
            'collectioned_user_id'=>$company_id
        ])->find();
        if(empty($c)){
            $code   = 100;
            $msg    = "未收藏";
        }else{
            $code   = 200;
            $msg    = "已收藏";
        }
        $j = [
            'code'=>$code,
            'msg'=>$msg
        ];
        return json_encode($j);
    }
}
