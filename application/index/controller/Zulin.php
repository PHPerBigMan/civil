<?php
namespace app\index\controller;

use app\common\model\Category;
use app\common\model\Notice;
use app\common\model\UserInformation;
use think\Controller;
use think\Db;
use Tinify\Tinify;

class Zulin extends Controller
{
    /**
     * @return object
     * 分类信息
     */

    public function category(){
        $c_data = Category::where(['c_name'=>"机械租赁"])->select();
        if(empty($c_data)){
            $cata[0]['category_id'] ='';
            $cata[0]['level'] = '';
            $cata[0]['pid'] = '';
            $cata[0]['c_name'] = '';
            $sata[0]['category_id'] = '';
            $sata[0]['level'] = '';
            $sata[0]['pid'] = '';
            $sata[0]['c_name'] = '';
            $tata[0]['category_id'] = '';
            $tata[0]['level'] = '';
            $tata[0]['pid'] ='';
            $tata[0]['c_name'] = '';
        }else{
            foreach($c_data as $k=>$v){
                $c_id = $v->category_id;
                $cata[$k]['category_id'] = $v->category_id;
                $cata[$k]['level'] = $v->level;
                $cata[$k]['pid'] = $v->pid;
                $cata[$k]['c_name'] = $v->c_name;
            }

            $s_data = Category::where(['pid'=>$c_id,'level'=>2])->select();
            if(empty($s_data)){
                $sata[0]['category_id'] = '';
                $sata[0]['level'] = '';
                $sata[0]['pid'] = '';
                $sata[0]['c_name'] = '';
                $tata[0]['category_id'] = '';
                $tata[0]['level'] = '';
                $tata[0]['pid'] ='';
                $tata[0]['c_name'] = '';
            }else{
                foreach($s_data as $k=>$v){
                    $sata[$k]['category_id'] = $v->category_id;
                    $sata[$k]['level'] = $v->level;
                    $sata[$k]['pid'] = $v->pid;
                    $sata[$k]['c_name'] = $v->c_name;
                    $id[]   = $v->category_id;
                }
                $id = implode(',',$id);
                $t_data = Category::where("pid in ($id)")->where(['level'=>3])->select();
                if(empty($t_data)){
                    $tata[0]['category_id'] = '';
                    $tata[0]['level'] = '';
                    $tata[0]['pid'] ='';
                    $tata[0]['c_name'] = '';
                }else{
                    foreach($t_data as $k=>$v){
                        $tata[$k]['category_id'] = $v->category_id;
                        $tata[$k]['level'] = $v->level;
                        $tata[$k]['pid'] = $v->pid;
                        $tata[$k]['c_name'] = $v->c_name;
                    }
                }
            }
        }

        $data = [
            'a'=>$cata,
            'b'=>$sata,
            'c'=>$tata,
        ];
        return json_encode($data);
    }

    /**
     * @return object
     * 租赁页面公告数据
     */

    public function notice(){
        $category_id = input('category_id');
        $n_data = Notice::where(['category_id'=>$category_id])->order("create_time desc")->select();
        if(!empty($n_data)){
            foreach($n_data as $k=>$v){
                $user_id[] = $v->user_id;
                $data[$k]['notice_content'] = $v->notice_content;
                $data[$k]['create_time']=$v->create_time;
                $data[$k]['notice_url']=$v->notice_url;
                $data[$k]['notice_video']=$v->notice_video;
            }
            $user_id = implode(',',$user_id);
            $u_data = UserInformation::where("user_id in ($user_id)")->select();
            foreach($u_data as $k=>$v){
                $data[$k]['company_name'] = $v->company_name;
            }
        }
        return json_encode($data);
    }

    /**
     * @return string
     * 机械租赁的企业列表
     */

    public function company(){
        $Tid    = input('tid');
        $fId    = input('fid');
        $Sid    = input('sid');
        $page   = input('page');
        $area   = input('area');

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

        //如果没有第二级分类，显示所有
        if(empty($page)){
            if(empty($Sid)){
                $category_id = Db::name('Category')->where("c_name","机械租赁")->value('category_id');
                $s_category_id = Db::name('Category')->field('category_id')->where("pid",$category_id)->select();
                if(empty($s_category_id)){
                    $company_data                      = array();
                }else{
                    foreach($s_category_id as $k=>$v){
                        $s_category_id_use[] = $v['category_id'];
                    }
                    $s_category_id_use = implode(',',$s_category_id_use);
                    $t_category_id = Db::name('Category')->field("category_id")->where("pid in ($s_category_id_use)")->select();
                    if(empty($t_category_id)){
                        $company_data                      = array();

                    }else{
                        foreach($t_category_id as $k=>$v){
                            $t_category_id_use[] = $v['category_id'];
                        }
                        $t_category_id_use = implode(",",$t_category_id_use);

                        $company = Db::name("UserCategory")->field('user_id')->where("category_id in ($t_category_id_use)")->select();
                        if(empty($company)){
                            $company_data                     = array();

                        }else{
                            foreach($company as $v){
                                $company_id[] = $v['user_id'];
                            }
                            $company_id = implode(',',$company_id);

                            $company_data = Db::name("UserInformation")
                                ->field('id,company_url,company_video,company_name,company_address,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                ->where("user_id in ($company_id)")->select();
                            foreach($company_data as $k=>$v){
                                $data[$k]['company_url']             = $v['company_url'];
                                $data[$k]['company_name']            = $v['company_name'];
                                $data[$k]['company_address']         = $v['company_address'];
                                $data[$k]['tel']                     = $v['tel'];
                                $data[$k]['qq']                      = $v['qq'];
                                $data[$k]['introduce']               = $v['introduce'];
                                $data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                                $data[$k]['user_id']                 = $v['user_id'];
                                $c_id[$k]                            = $v['business'];
                                $c_name[$k]["business"]              = Db::name("Category")->field("c_name")->where("category_id","in","$c_id[$k]")->select();
                                foreach($c_name[$k]["business"] as $k1=>$v1){
                                    $c_name_use[$k][$k1]= empty($v1['c_name'])?"":$v1['c_name'];
                                }
                                $data[$k]["business"] = empty($c_name_use[$k])? "" : implode(",",$c_name_use[$k]);
                            }
                        }
                    }
                }
            }else{
                //如果没有三级分类  显示对应的二级分类
                if(empty($Tid)){
                    $t_category_id = Db::name('Category')->field('category_id')->where("pid",$Sid)->select();
                    if(empty($t_category_id)){
                        $company_data[0]['id']                      = '';
                        $company_data[0]['company_url']             = '';
                        $company_data[0]['company_video']           = '';
                        $company_data[0]['company_name']            = '';
                        $company_data[0]['company_address']         = '';
                        $company_data[0]['company_authentication']  = '';
                        $company_data[0]['tel']                     = '';
                        $company_data[0]['qq']                      = '';
                        $company_data[0]['business']                = '';
                        $company_data[0]['introduce']               = '';
                        $company_data[0]['is_authentication']       = '';
                    }else{
                        foreach($t_category_id as $k=>$v){
                            $t_category_id_use[] = $v['category_id'];
                        }
                        $t_category_id_use = implode(",",$t_category_id_use);

                        $company = Db::name("UserCategory")->field('user_id')->where("category_id in ($t_category_id_use)")->select();
                        if(empty($company)){
                            $company_data[0]['id']                      = '';
                            $company_data[0]['company_url']             = '';
                            $company_data[0]['company_video']           = '';
                            $company_data[0]['company_name']            = '';
                            $company_data[0]['company_address']         = '';
                            $company_data[0]['company_authentication']  = '';
                            $company_data[0]['tel']                     = '';
                            $company_data[0]['qq']                      = '';
                            $company_data[0]['business']                = '';
                            $company_data[0]['introduce']               = '';
                            $company_data[0]['is_authentication']       = '';
                        }else{
                            foreach($company as $v){
                                $company_id[] = $v['user_id'];
                            }
                            $company_id = implode(',',$company_id);

                            $company_data = Db::name("UserInformation")
                                ->field('id,company_url,company_video,head_pic,company_name,company_address,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                ->where("user_id in ($company_id)")->select();

                            foreach($company_data as $k=>$v){
                                $head_pic = $v['head_pic'];
                                $data[$k]['company_url']          = empty(Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url')) ? "" :Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url');

//                                $data[$k]['company_url']             = $v['company_url'];
                                $data[$k]['company_id']                 = $v['user_id'];
                                $data[$k]['company_name']            = $v['company_name'];
                                $data[$k]['company_address']         = $v['company_address'];
                                $data[$k]['tel']                     = $v['tel'];
                                $data[$k]['qq']                      = $v['qq'];
                                $data[$k]['introduce']               = $v['introduce'];
                                $data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                                $data[$k]['user_id']                 = $v['user_id'];
                                $c_id[$k]                            = $v['business'];
                                $c_name[$k]["business"]              = Db::name("Category")->field("c_name")->where("category_id","in","$c_id[$k]")->select();
                                foreach($c_name[$k]["business"] as $k1=>$v1){
                                    $c_name_use[$k][$k1]= empty($v1['c_name'])?"":$v1['c_name'];
                                }
                                $data[$k]["business"] = empty($c_name_use[$k])? "" : implode(",",$c_name_use[$k]);
                            }
                        }

                    }
                }else{
                    $company = Db::name("UserCategory")->field('user_id')->where("category_id",$Tid)->select();
                    if(empty($company)){
                        $company_data[0]['id']                      = '';
                        $company_data[0]['company_url']             = '';
                        $company_data[0]['company_video']           = '';
                        $company_data[0]['company_name']            = '';
                        $company_data[0]['company_address']         = '';
                        $company_data[0]['company_authentication']  = '';
                        $company_data[0]['tel']                     = '';
                        $company_data[0]['qq']                      = '';
                        $company_data[0]['business']                = '';
                        $company_data[0]['introduce']               = '';
                        $company_data[0]['is_authentication']       = '';
                    }else{
                        foreach($company as $v){
                            $company_id[] = $v['user_id'];
                        }
                        $company_id = implode(',',$company_id);

                        $company_data = Db::name("UserInformation")
                            ->field('id,company_url,company_video,company_name,head_pic,company_address,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                            ->where("user_id in ($company_id)")->select();
                        foreach($company_data as $k=>$v){
                            $head_pic = $v['head_pic'];
                            $data[$k]['company_url']          = empty(Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url')) ? "" :Db::name("head_pic")->where(['id'=>$head_pic])->value('pic_url');

//                                $data[$k]['company_url']             = $v['company_url'];
                            $data[$k]['company_name']            = $v['company_name'];
                            $data[$k]['company_address']         = $v['company_address'];
                            $data[$k]['tel']                     = $v['tel'];
                            $data[$k]['company_id']                 = $v['user_id'];
                            $data[$k]['qq']                      = $v['qq'];
                            $data[$k]['introduce']               = $v['introduce'];
                            $data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                            $data[$k]['user_id']                 = $v['user_id'];
                            $c_id[$k]                            = $v['business'];
                            $c_name[$k]["business"]              = Db::name("Category")->field("c_name")->where("category_id","in","$c_id[$k]")->select();
                            foreach($c_name[$k]["business"] as $k1=>$v1){
                                $c_name_use[$k][$k1]= empty($v1['c_name'])?"":$v1['c_name'];
                            }
                            $data[$k]["business"] = empty($c_name_use[$k])? "" : implode(",",$c_name_use[$k]);
                        }
                    }
                }
            }
            $j = [
                'data'=>$data
            ];
        }else{
            if(empty($Tid)){
                if(empty($Sid)){
                    //根据一级分类查找所有的企业信息
                    $s_id = Db::name('Category')->field('category_id')->where('pid',$fId)->select();
                    if(empty($s_id)){
                        $search_id = $fId;
                    }else {

                        foreach ($s_id as $k => $v) {
                            $t_Id[$k] = Db::name('Category')->field('category_id')->where('pid', $v['category_id'])->select();
                        }

                        foreach ($s_id as $k => $v) {
                            $s_use_id[$k] = $v['category_id'];
                        }
                        foreach ($t_Id as $k => $v) {
                            if (empty($v)) {
                                unset($v);
                            } else {
                                foreach ($v as $k1 => $v1) {
                                    $search_id_use[] = $v1['category_id'];
                                }
                            }
                        }

                        if (empty($search_id_use)) {
                            $search_id = $s_use_id;
                        } else {
                            $search_id = array_merge($s_use_id, $search_id_use);
                        }
                    }

                    foreach ($search_id as $k => $v) {
                        $company_id[] = Db::name('UserCategory')->field('user_id')->where("category_id", $v)->select();
                    }

                    if (empty($company_id)) {
                        $data = array();
                        $n_count = 0;
                    } else {
                        $n_count = "";
                        foreach ($company_id as $k => $v) {
                            if (empty($v)) {
                                unset($v);
                            } else {
                                foreach($v as $v1){
                                    $company_data[] = Db::name('UserInformation')
                                        ->where("user_id", $v1['user_id'])
                                        ->find();
                                }
                            }
                        }

                        $company_data = getArrayUniqueByKeys($company_data);

                        if(empty($company_data)){
                            $data = array();
                        }else{
                            foreach ($company_data as $k => $v) {
                                if (!empty($v)) {
                                    $head_pic = $v['head_pic'];
                                    $z_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                                    $z_data[$k]['company_name'] = $v['company_name'];
                                    $z_data[$k]['company_address'] = $v['company_address'];
                                    $z_data[$k]['avl'] = $v['avl'];
                                    $z_data[$k]['evl'] = $v['evl'];
                                    $z_data[$k]['tel'] = $v['tel'];
                                    $z_data[$k]['qq'] = $v['qq'];
                                    $z_data[$k]['introduce'] = $v['introduce'];
                                    $z_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                                    $z_data[$k]['company_id'] = $v['user_id'];
                                    $c_id[$k] = $v['business'];
                                    $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                    foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                        $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                    }
                                    $z_data[$k]["business"] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                }
                            }
//                            var_dump($a);die;
                            $z_data = $this->getDistance($avl,$evl,$z_data);
                            $c_data = $z_data;
                            $count = count($c_data);

                            $n_num = ceil($count/10);
                            if($page<=1 && $count<=10){
                                for($i=0;$i<$count;$i++){
                                    $data[$i] = $c_data[$i];
                                }
                            }else if($page>1 &&  $count>10 ){
                                if($n_count%10 == 0){
                                    for($i=($page-1)*10;$i<($n_count-1);$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }else if($count%10 != 0 && $page!=$n_num){
                                    for($i=($page-1)*10;$i<(($page-1)*10)+10;$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }else if($count%10 != 0 && $page == $n_num){
                                    for($i=($page-1)*10;$i<$count;$i++){
                                        $data[$i] = $c_data[$i];
                                    }
                                }
                            }else if($page<=1 && $count>10){
                                for($i=0;$i<10;$i++){
                                    $data[$i] = $c_data[$i];
                                }
                            }
                        }

                    }
                }else{
                    $S_data = Db::name('Category')->field("category_id")->where("pid",$Sid)->select();
                    $new = [];
                    if(empty($S_data)){
                        $company_id = Db::name('UserCategory')->field('user_id')->where("category_id",$Sid)->select();
                    }else{
                        //所有分类下的公司id
                        $s_company_id = Db::name('UserCategory')->field('user_id')
                            ->where("category_id",$Sid)
                            ->select();

                        foreach($S_data as $k=>$v){
                            $t_company_id[$k] = Db::name('UserCategory')->field('user_id')
                                ->where("category_id",$v['category_id'])
                                ->select();;
                        }

                        foreach($t_company_id as $k=>$v){
                            if(empty($v[0])){
                                unset($t_company_id[$k]);
                            }else{
                                $check_t_company_id[] = $t_company_id[$k];
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
                        $data = array();
                        $count      = 0;
                    }else{

                        foreach($company_id as $k=>$v){
                            $t_category_id_use[] = $v['user_id'];
                        }
                        $t_category_id_use = implode(",",$t_category_id_use);

                        $company = Db::name("UserInformation")->where("user_id in ($t_category_id_use)")->select();

                        if(empty($company)){
                            $data = array();
                            $count = 0;
                        }else{
                            $count = count($company);

                            //页码
                            $n_num = ceil($count/10);
                            if($page<=1){
                                if($count < 10){
                                    $n_count = $count;
                                    $company_data = Db::name("UserInformation")
//                                        ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                        ->where("user_id in ($t_category_id_use)")
                                        ->limit(0,$n_count)->select();


                                }else{
                                    $company_data = Db::name("UserInformation")
//                                        ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                        ->where("user_id in ($t_category_id_use)")
                                        ->limit(0,10)->select();
                                }
                            }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
                                $company_data = Db::name("UserInformation")
//                                    ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                    ->where("user_id in ($t_category_id_use)")
                                    ->limit(($page-1)*10,10)->select();
                            }else if($page==$n_num){
                                $company_data = Db::name("UserInformation")
//                                    ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                    ->where("user_id in ($t_category_id_use)")
                                    ->limit($count-($n_num-1)*10)->select();
                            }


                            if(empty($company_data)){
                                $data = array();
                                $count = 0;
                            }else{
                                $company_data = getArrayUniqueByKeys($company_data);
                                foreach($company_data as $k=>$v) {
                                    $head_pic = $v['head_pic'];
                                    $z_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                                    $z_data[$k]['company_name'] = $v['company_name'];
                                    $z_data[$k]['company_address'] = $v['company_address'];
                                    $z_data[$k]['avl'] = $v['avl'];
                                    $z_data[$k]['evl'] = $v['evl'];
                                    $z_data[$k]['tel'] = $v['tel'];
                                    $z_data[$k]['qq'] = $v['qq'];
                                    $z_data[$k]['introduce'] = $v['introduce'];
                                    $z_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 :Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                                    $z_data[$k]['company_id'] = $v['user_id'];
                                    $c_id[$k] = $v['business'];
                                    $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                    foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                        $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                    }
                                    $z_data[$k]["business"] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                                }
                            }
                        }


                        $z_data = $this->getDistance($avl,$evl,$z_data);
                        $data = $z_data;
                    }

                }
                $j = [
                    'data'=>$data,
                    'count'=>$count
                ];
            }else{

                //查找三级分类的数据
                $t_category_id = Db::name('UserCategory')->field('user_id')->where("category_id",$Tid)->select();

                if(empty($t_category_id)){
                    //查询三级分类下的数据
                    $t_category_id = Db::name('UserCategory')->field('user_id')->where("category_id",$Tid)->select();
                }else{
                    $t_category_id  = $t_category_id;
                }

                if(empty($t_category_id)){
                    $data = array();
                    $count      = 0;
                }else{

                    foreach($t_category_id as $k=>$v){
                        $t_category_id_use[] = $v['user_id'];
                    }
                    $t_category_id_use = implode(",",$t_category_id_use);

                    $company = Db::name("UserInformation")->where("user_id in ($t_category_id_use)")->select();
                    if(empty($company)){
                        $data = array();
                        $count = 0;
                    }else{
//                            $count     = Db::name("UserCategory")->where(['category_id'=>$t_category_id_use])->count();
                        $count = count($company);

                        //页码
                        $n_num = ceil($count/5);
                        if($page<=1){
                            if($count < 5){
                                $n_count = $count;
                                $company_data = Db::name("UserInformation")
//                                    ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                    ->where("user_id in ($t_category_id_use)")
                                    ->limit(0,$n_count)->select();


                            }else{
                                $company_data = Db::name("UserInformation")
//                                    ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                    ->where("user_id in ($t_category_id_use)")

                                    ->limit(0,5)->select();
                            }
                        }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
                            $company_data = Db::name("UserInformation")
//                                ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                ->where("user_id in ($t_category_id_use)")
                                ->limit(($page-1)*5,5)->select();
                        }else if($page==$n_num){
                            $company_data = Db::name("UserInformation")
//                                ->field('id,company_url,company_video,company_name,company_address,head_pic,company_authentication,tel,qq,introduce,is_authentication,user_id,business')
                                ->where("user_id in ($t_category_id_use)")
                                ->limit($count-($n_num-1)*5)->select();
                        }

                        if(empty($company_data)){
                            $data = array();
                            $count = 0;
                        }else{
                            foreach($company_data as $k=>$v) {
                                $head_pic = $v['head_pic'];
                                $z_data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');

                                $z_data[$k]['company_name'] = $v['company_name'];
                                $z_data[$k]['company_address'] = $v['company_address'];
                                $z_data[$k]['tel'] = $v['tel'];
                                $z_data[$k]['avl'] = $v['avl'];
                                $z_data[$k]['evl'] = $v['evl'];
                                $z_data[$k]['qq'] = $v['qq'];
                                $z_data[$k]['introduce'] = $v['introduce'];
                                $z_data[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level') == 4 ? 0 : Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                                $z_data[$k]['company_id'] = $v['user_id'];
                                $c_id[$k] = $v['business'];
                                $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                                foreach ($c_name[$k]["business"] as $k1 => $v1) {
                                    $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                                }
                                $z_data[$k]["business"] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                            }
                        }
                    }

                    $z_data = $this->getDistance($avl,$evl,$z_data);
                    $data = $z_data;
                }
                $j = [
                    'data'=>$data,
                    'count'=>$count
                ];
            }
        }
       return json_encode($j);
    }




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
     * 查询  根据分类查询（三级分类）
     */
    public function search(){
        $key = input('keyword');
        $c_data = Db::name('Category')->field('category_id')->where('c_name',"like","%$key%")
                                      ->where("level",2)
                                      ->select();
        $t_data = Db::name('Category')->field('category_id')->where("pid",$c_data[0]['category_id'])->select();
        if(empty($t_data)){

        }else{
            foreach($t_data as $k=>$v){
                $data[$k] = Db::name('UserCategory')->where("category_id",$v['category_id'])->value('user_id');
                $company_data[$k] = Db::name('UserInformation')->where("user_id",$data[$k])->select();
            }
            if(!empty($company_data)){
                foreach($company_data as $k=>$v){
                    if(!empty($v)){
                        $com_data[$k]['company_name']               = $v[0]['company_name'];
                        $com_data[$k]['company_address']            = $v[0]['company_address'];
                        $com_data[$k]['tel']                        = $v[0]['tel'];
                        $com_data[$k]['qq']                         = $v[0]['qq'];
                        $com_data[$k]['introduce']                  = $v[0]['introduce'];
                        $com_data[$k]['company_url']                = $v[0]['company_url'];
                        $com_data[$k]['is_authentication']          = $v[0]['is_authentication'];
                        $com_data[$k]['user_id']                    = $v[0]['user_id'];
                        $c_id[$k]                                   = $v[0]['business'];
                        $c_name[$k]["business"]                     = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                        foreach ($c_name[$k]["business"] as $k1 => $v1) {
                            $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                        }
                        $com_data[$k]["business"]                   = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                    }else{
                        $com_data[$k]['company_name']               = '';
                        $com_data[$k]['company_address']            ='';
                        $com_data[$k]['tel']                        = '';
                        $com_data[$k]['qq']                         = '';
                        $com_data[$k]['introduce']                  = '';
                        $com_data[$k]['company_url']                = '';
                        $com_data[$k]['is_authentication']          = '';
                        $com_data[$k]['user_id']                    = '';
                        $com_data[$k]["business"]                   = '';
                    }
                }
            }
        }
        $j = [
            'data'=>$com_data
        ];
        return json_encode($j);
    }






    public function index(){
        return view('zulin/index');
    }

    function send_post($url, $post_data,$method='POST') {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => $method, //or GET
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }


    function api_notice_increment($url, $data){

    }

    public function demo(){
        MakeImg();
    }

}
