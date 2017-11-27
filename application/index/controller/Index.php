<?php
namespace app\index\controller;

use app\common\model\Banner;
use app\common\model\Category;
use app\common\model\Logo;
use app\common\model\News;
use app\common\model\UserInformation;
use app\common\model\Visit;
use app\common\model\WebNotice;
use think\Controller;
use think\Db;
header('Content-type: application/json');
header('contentType: application/x-www-form-urlencoded; charset=utf-8');
header('Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie');
class Index extends Controller
{
    /**
     * @return object
     * 首页banner,logo,分类数据,根据地区筛选的优质企业
     */

    public function index()
    {
        $pc = input('pc');
        if(empty($pc)){
            //banner  logo
            $pic_data = Banner::select();
            foreach($pic_data as $k=>$v){
                $pic[$k]['banner'] = $v->cell_banner;
            }

            $logo = Logo::select();
            //分类
            $c = new Category();
            $data = $c->where("is_show",1)->select();

            $new = $c->make_tree1($data);
            $new = array_slice($new,0,3);
            //入驻商家总数
            $com_count = Db::name('UserInformation')->count('*');

        }else{
            $pic_data = Banner::select();
            foreach($pic_data as $k=>$v){
                $pic[$k]['banner'] = $v->pc_banner;
            }

            $logo = Logo::select();
            //分类
            $c = new Category();
            $data = $c->order("is_show desc")->select();
//        var_dump($data);die;
            $new = $c->make_tree1($data);
            //入驻商家总数
            $com_count = Db::name('UserInformation')->count('*');

        }
        //统计访问量
        $j = [
            'pic'       =>$pic,
            'logo'      =>$logo,
            'data'      =>$new,
            'count'     =>$com_count
        ];
        return json_encode($j);
    }
    /**
     * @return string
     * pc_使用分类数据
     */

    public function pc_cat(){
        $pic_data = Banner::select();
        foreach($pic_data as $k=>$v){
            $pic[$k]['banner'] = $v->pc_banner;
        }

        $logo = Logo::select();
        //分类
        $c = new Category();
        $data = $c->order("is_show desc")->select();
        $new = $c->make_tree1($data);
        foreach($new as $k=>$v){
            $c_data[$k]['category_id'] = $v->category_id;
            $c_data[$k]['level'] = $v->level;
            $c_data[$k]['pid'] = $v->pid;
            $c_data[$k]['pc_pic'] = $v->pc_pic;
            $c_data[$k]['c_name'] = $v->c_name;
            $c_data[$k]['is_show'] = $v->is_show;
            $c_id[$k] = $v->category_id;
//            $c_data[$k]['teach'] = empty(Db::name("technology")->field('id,title,content')->where(['category_id'=>$c_id[$k]])->limit(10)->select()) ? array() : Db::name("technology")->field('id,title,content')->where(['category_id'=>$c_id[$k]])->limit(10)->select() ;
            $c_data[$k]['child'] = $v->child;
        }

//        echo "<pre>";
//        var_dump($c_data);die;

        //入驻商家总数
        $com_count = Db::name('UserInformation')->count('*');
        $j = [
            'pic'       =>$pic,
            'logo'      =>$logo,
            'data'      =>$c_data,
            'count'     =>$com_count
        ];
        return json_encode($j);
    }

    /**
     * @return object  企业信息
     */

   public function company(){
       //优质企业    地区格式还需要进行调整
       $area = input('area');
       $more = input('more');
       $page = input('page');
       if(empty($more)){
           $company = UserInformation::where(['is_top'=>1])->limit(35)
               ->select();
           if(empty($company)){
               $Dcompany = array();
           }else{
               foreach($company as $k=>$v){
                   if(mb_substr($area , 0,6) == mb_substr($v->company_address , 0 , 6)){
                       $A_Dcompany[$k]['id']                             = $v->user_id;
                       $A_Dcompany[$k]['company_url']                    = $v->company_url;
                       $A_Dcompany[$k]['company_video']                  = $v->company_video;
                       $A_Dcompany[$k]['company_name']                   = $v->company_name;
                       $A_Dcompany[$k]['company_address']                = $v->company_address;
                       $A_Dcompany[$k]['company_authentication']         = $v->company_authentication;
                       $A_Dcompany[$k]['tel']                            = $v->tel;
                       $A_Dcompany[$k]['qq']                             = $v->qq;
                       $c_name[$k]["business"]                     = Db::name("Category")->field("c_name")->where("category_id", "in", "$v->business")->select();
                       foreach ($c_name[$k]["business"] as $k1 => $v1) {
                           $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                       }
                       $A_Dcompany[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       $A_Dcompany[$k]['is_authentication']              = Db::name('User')->where(['id'=>$v->user_id])->value('level');
                   }else{
                       $B_Dcompany[$k]['id']                             = $v->user_id;
                       $B_Dcompany[$k]['company_url']                    = $v->company_url;
                       $B_Dcompany[$k]['company_video']                  = $v->company_video;
                       $B_Dcompany[$k]['company_name']                   = $v->company_name;
                       $B_Dcompany[$k]['company_address']                = $v->company_address;
                       $B_Dcompany[$k]['company_authentication']         = $v->company_authentication;
                       $B_Dcompany[$k]['tel']                            = $v->tel;
                       $B_Dcompany[$k]['qq']                             = $v->qq;
                       $c_name[$k]["business"]                     = Db::name("Category")->field("c_name")->where("category_id", "in", "$v->business")->select();
                       foreach ($c_name[$k]["business"] as $k1 => $v1) {
                           $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                       }
                       $B_Dcompany[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       $B_Dcompany[$k]['is_authentication']              = Db::name('User')->where(['id'=>$v->user_id])->value('level');
                   }
               }
               if(empty($A_Dcompany)){
                   $Dcompany = $B_Dcompany;
               }else if(empty($B_Dcompany)){
                   $Dcompany = $A_Dcompany;
               }else{
                   $Dcompany = array_merge($A_Dcompany,$B_Dcompany);
               }
//               var_dump($Dcompany);die;
           }
           $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$_SERVER["REMOTE_ADDR"];
           $total_data = json_decode(file_get_contents($url));
           $visit = new Visit();
           $visit->user_id         = empty(session('user_id')) ? 0 : session('user_id');
           $visit->ip              = $_SERVER["REMOTE_ADDR"];
           if(empty($total_data)){
               $visit->province        = "未知地区";
           }else{
               $visit->province        = $total_data->province;
           }
           $visit->visit_time      = time();
           $visit->save();
           $j = [
               'company'=>$Dcompany
           ];
       }else{
           $count = Db::name("UserInformation")->where(['is_top'=>1])->count();
           //页码
           $n_num = ceil($count/5);
           if($page<=1){
               if($count < 5){
                   $n_count = $count;
                   $company_data = Db::name("UserInformation")->where(['is_top'=>1])
                       ->limit($n_count)->select();
               }else{
                   $company_data = Db::name("UserInformation")->where(['is_top'=>1])->limit(5)->select();
               }
           }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
               $company_data = Db::name("UserInformation")->where(['is_top'=>1])
                   ->limit(($page-1)*5,5)->select();
           }else if($page==$n_num){
               $company_data = Db::name("UserInformation")->where(['is_top'=>1])
                   ->limit($count-($n_num-1)*5)->select();
           }
//           var_dump($company_data);die;
           if(empty($company_data)){
               $data = array();
               $count = 0;
           }else{
               foreach($company_data as $k=>$v) {
                   if (mb_substr($area,0,6) == mb_substr($v['company_address'], 0, 6)) {
                       $A_Dcompany[$k]['id'] = $v['user_id'];
                       $A_Dcompany[$k]['company_name'] = $v['company_name'];
                       $A_Dcompany[$k]['company_address'] = $v['company_address'];
                       $A_Dcompany[$k]['tel'] = $v['tel'];
                       $A_Dcompany[$k]['qq'] = $v['qq'];
                       $business = $v['business'];
                       $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$business")->select();
                       foreach ($c_name[$k]["business"] as $k1 => $v1) {
                           $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                       }
                       $A_Dcompany[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       $A_Dcompany[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                       $head_pic = $v['head_pic'];
                       $A_Dcompany[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                   }else{
                       $B_Dcompany[$k]['id'] = $v['user_id'];
                       $B_Dcompany[$k]['company_name'] = $v['company_name'];
                       $B_Dcompany[$k]['company_address'] = $v['company_address'];
                       $B_Dcompany[$k]['tel'] = $v['tel'];
                       $B_Dcompany[$k]['qq'] = $v['qq'];
                       $business = $v['business'];
                       $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$business")->select();
                       foreach ($c_name[$k]["business"] as $k1 => $v1) {
                           $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                       }
                       $B_Dcompany[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       $B_Dcompany[$k]['is_authentication'] = Db::name('User')->where(['id'=>$v['user_id']])->value('level');
                       $head_pic = $v['head_pic'];
                       $B_Dcompany[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');

                   }
               }

               if(empty($A_Dcompany)){
                   $Dcompany = $B_Dcompany;
               }else if(empty($B_Dcompany)){
                   $Dcompany = $A_Dcompany;
               }else{
                   $Dcompany = array_merge($A_Dcompany,$B_Dcompany);
               }
           }
           $j = [
               'company'=>$Dcompany,
               'count'=>$count
           ];
       }
       return json_encode($j);
   }

    /**
     * @return object
     * 平台公告
     */
   public function notice(){
       $pc = input('pc');
       if(empty($pc)){
           $new_data = "";
           $notice = WebNotice::all();
           if(empty($notice)){
               $data = array();
           }else{
               foreach($notice as $k=>$v){
                   $new_data         = $new_data."   ".$v->content;
               }
           }
           return $new_data;
       }else{

           $page        = input('page');
           $n_count     = Db::name("web_notice")->count();
           //页码
           if((input('index') == 1)){

               $n_num = ceil($n_count/7);
               if($n_count < 7){
                   $n_data = Db::name("web_notice")->order("create_time desc")->select();
               }else{
                   $n_data = Db::name("web_notice")->limit(7)->order("create_time desc")->select();
               }
               if(empty($n_data)){
                   $data = array();
               }else{
                   foreach($n_data as $k=>$v){
                       $data[$k]['title']       = $v['title'];
                       $data[$k]['time']        = date("Y-m-d",$v['create_time']);
                       $data[$k]['id']          = $v['id'];
                   }
               }
           }else{
               $n_num = ceil($n_count/5);
               if($page<=1){
                   if($n_count < 5){
                       $n_count = $n_count;
                       $n_data = Db::name("web_notice")->limit(1,$n_count)->order('create_time desc')->select();
                       if(empty($n_data)){
                           $n_data = array();
                       }
                   }else{
                       $n_data = Db::name("web_notice")->limit(1,5)->order('create_time desc')->select();
                   }
               }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
                   $n_data = Db::name("web_notice")->limit(($page-1)*5,5)->order('create_time desc')->select();
               }else if($page==$n_num){
                   $n_data = Db::name("web_notice")->limit($n_count-($n_num-1)*5)->order('create_time desc')->select();
               }
               if(empty($n_data)){
                   $data = array();
               }else{
                   foreach($n_data as $k=>$v){
                       $data[$k]['title']       = $v['title'];
                       $data[$k]['time'] = date("Y-m-d",$v['create_time']);
                       $data[$k]['id']          = $v['id'];
                   }
               }
           }
           $j = [
               'count'=>$n_count,
               'data'=>$data
           ];
           return json_encode($j);
       }

   }


    /**
     * @return object
     * 搜索后的数据
     */
   public function search(){
       $keyword = input('key');
       $page    = input('page');
       $s       = input('skey');
       $area    = input('area');
       if(empty($page)){

           if(empty($keyword) && $s==1){
               $company = Db::name('UserInformation')->where(['is_top'=>1])->select();
               foreach($company as $k=>$v){
                   $data[$k]['id']                          = $v['user_id'];
                   $data[$k]['company_name']            = $v['company_name'];
                   $data[$k]['company_address']         = $v['company_address'];
                   $data[$k]['tel'] = $v['tel'];
                   $data[$k]['qq'] = $v['qq'];
                   $business = $v['business'];
                   $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$business")->select();
                   foreach ($c_name[$k]["business"] as $k1 => $v1) {
                       $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                   }
                   $data[$k]['business'] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                   $data[$k]['is_authentication'] = $v['is_authentication'];
                   $head_pic = $v['head_pic'];
                   $data[$k]['company_url'] = empty(Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url')) ? "" : Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');

               }
//               var_dump($data);die;
           }else if(!empty($keyword) && $s==0){
               $company = Db::name('UserInformation')
                   ->where("company_name","like","%$keyword%")
                   ->whereOr("company_name",$keyword)
                   ->select();

               if(empty($company)){
                   $data                      =array();
               }else{
                   foreach($company as $k=>$v){
                       if(mb_substr($area,0,6) == mb_substr($v['company_address'],0,6)){
                           $a_data[$k]['id']                      = $v['user_id'];
                           $a_data[$k]['company_name']            = $v['company_name'];
                           $a_data[$k]['company_address']         = $v['company_address'];
                           $a_data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v->user_id])->value('level');
                           $a_data[$k]['tel']                     = $v['tel'];
                           $a_data[$k]['qq']                      = $v['qq'];
                           $c_id[$k]                            = $v['business'];
                           $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                           foreach ($c_name[$k]["business"] as $k1 => $v1) {
                               $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                           }
                           $a_data[$k]['business']                = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       }else{
                           $b_data[$k]['id']                      = $v['user_id'];
                           $b_data[$k]['company_name']            = $v['company_name'];
                           $b_data[$k]['company_address']         = $v['company_address'];
                           $b_data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v->user_id])->value('level');
                           $b_data[$k]['tel']                     = $v['tel'];
                           $b_data[$k]['qq']                      = $v['qq'];
                           $c_id[$k]                            = $v['business'];
                           $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                           foreach ($c_name[$k]["business"] as $k1 => $v1) {
                               $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                           }
                           $b_data[$k]['business']                = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       }
                   }
                   if(empty($a_data)){
                       $data = $b_data;
                   }else if(empty($b_data)){
                       $data = $a_data;
                   }else{
                       $data = array_merge($a_data,$b_data);
                   }
               }
           }else if(empty($keyword)){
               $data                      =array();
           }
           $j = [
               'data'=>$data,
           ];
       }else{
           if(empty($keyword) && $s==1){
               $company = Db::name('UserInformation')->select();

               foreach($company as $k=>$v){
                   $data[$k]['id']                      = $v['user_id'];
                   $data[$k]['company_url']             = $v['head_pic'];
                   $data[$k]['company_video']           = $v['company_video'];
                   $data[$k]['company_name']            = $v['company_name'] ;
                   $data[$k]['company_address']         = $v['company_address'];
                   $data[$k]['is_authentication']       = $v['is_authentication'];
                   $data[$k]['tel']                     = $v['tel'];
                   $data[$k]['qq']                      = $v['qq'];
                   $c_id[$k]                            = $v->business;
                   $head[$k]                            = $v['head_pic'];
                   $data[$k]['company_url']                = Db::name("head_pic")->where(['id'=>$head])->value('pic_url');
                   $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                   foreach ($c_name[$k]["business"] as $k1 => $v1) {
                       $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                   }
                   $data[$k]['business']                = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
               }
           }else if(!empty($keyword) && $s==0){
               $h_count = Db::name('UserInformation')->where("company_name","like","%$keyword%")->count();
               $h_num = ceil($h_count/10);
               if($page<=1){
                   if($h_count < 10){
                       $h_count = $h_count;
                       $company = UserInformation::where("company_name","like","%$keyword%")->limit(0,$h_count)->order("create_time desc")->select();
                       if(empty($company)){
                           $company = array();
                       }
                   }else{
                       $company = UserInformation::where("company_name","like","%$keyword%")->limit(0,10)->order("create_time desc")->select();
                   }
               }else if($page>1 && $page <= ($h_num-1) && $h_num>1){
                   $company = UserInformation::where("company_name","like","%$keyword%")->limit(($page-1)*10,10)->order("create_time desc")->select();
               }else if($page==$h_num){
                   $company = UserInformation::where("company_name","like","%$keyword%")->limit($h_count-($h_num-1)*10)->order("create_time desc")->select();
               }
//               var_dump($company);die;
               if(empty($company)){
                   $data                      =array();
               }else{
                   foreach($company as $k=>$v){
                       if(mb_substr($area,0,6) == mb_substr($v->company_address, 0, 6)){
                           $a_data[$k]['id']                      = $v->user_id;
                           $head_pic[$k]                        = $v->head_pic;
                           $a_data[$k]['company_video']           = $v->company_video;
                           $a_data[$k]['company_name']            = $v->company_name;
                           $a_data[$k]['company_address']         = $v->company_address;
                           $a_data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v->user_id])->value('level');
                           $a_data[$k]['tel']                     = $v->tel;
                           $a_data[$k]['qq']                      = $v->qq;
                           $c_id[$k]                            = $v->business;
                           $a_data[$k]['company_url']                = Db::name('head_pic')->where(['id'=>$head_pic[$k]])->value('pic_url');
                           $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                           foreach ($c_name[$k]["business"] as $k1 => $v1) {
                               $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                           }
                           $a_data[$k]['business']                = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       }else{
                           $b_data[$k]['id']                      = $v->user_id;
                           $head_pic[$k]                        = $v->head_pic;
                           $b_data[$k]['company_video']           = $v->company_video;
                           $b_data[$k]['company_name']            = $v->company_name;
                           $b_data[$k]['company_address']         = $v->company_address;
                           $b_data[$k]['is_authentication']       = Db::name('User')->where(['id'=>$v->user_id])->value('level');
                           $b_data[$k]['tel']                     = $v->tel;
                           $b_data[$k]['qq']                      = $v->qq;
                           $c_id[$k]                            = $v->business;
                           $b_data[$k]['company_url']                = Db::name('head_pic')->where(['id'=>$head_pic[$k]])->value('pic_url');
                           $c_name[$k]["business"]             = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
                           foreach ($c_name[$k]["business"] as $k1 => $v1) {
                               $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
                           }
                           $b_data[$k]['business']                = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);
                       }

                   }
//                   var_dump($b_data);die;
                   if(empty($a_data)){
                       $data = $b_data;
                   }else if(empty($b_data)){
                       $data = $a_data;
                   }else{
                       $data = array_merge($a_data,$b_data);
                   }

               }
           }else if(empty($keyword)){
               $data                      =array();
           }
           $j = [
               'data'=>$data,
               'count' =>$h_count
           ];
       }
       return json_encode($j);
   }

   public function news(){
    $data = Db::name('News')->limit(12)->order('time desc')->select();
    foreach($data as $k=>$v){
        $new[$k]['title']               = $v['title'];
        $new[$k]['img']                 = $v['img'];
        $new[$k]['content']             = $v['content'];
        $new[$k]['time']                = date("Y-m-d",$v['time']);
        $new[$k]['id']                  = $v['id'];
    }
    return json_encode($new);
   }

    /**
     * @return string
     */
   
   public function read(){
       $id = input('id');
       $count = Db::name('news')->count('id');
       Db::name("news")->where([
           'id'=>$id
       ])->setInc('pv',rand(3,6));
//       var_dump($count);die;
       if($id == 1){
           $next_id = $id+1;
           $n_data = Db::name('news')->where(['id'=>$next_id])->find();
           $next_title = $n_data['title'];

//           $next_id = $n_data['id'];

           $pre_id = "";
           $pre_title="无";
       }else if($id>1 && $id!=$count){
           $next_id = $id+1;
           $n_data = Db::name('news')->where(['id'=>$next_id])->find();
           $next_title = $n_data['title'];
//           $nid = $n_data['id'];

           $pre_id = $id-1;
           $p_data = Db::name('news')->where(['id'=>$pre_id])->find();
           $pre_title=$p_data['title'];
       }else if($id == $count){
           $next_id = "";
           $next_title = "无";

           $pre_id = $id-1;
           $p_data = Db::name('news')->where(['id'=>$pre_id])->find();
           $pre_title = $p_data['title'];
       }

       $data                    = Db::name('news')->where(['id'=>$id])->find();
       $title                   = $data['title'];
       $content                 = $data['content'];
       $time                    = date("Y-m-d",$data['time']);
       $img                     = $data['img'];
       $id                      = $data['id'];
       $from                    = $data['from'];
       $j = [
           'next_id'=>$next_id,
           'next_title'=>$next_title,
           'pre_id'=>$pre_id,
           'pre_title'=>$pre_title,
           'title'=>$title,
           'content'=>$content,
           'time'=>$time,
           'img'=>$img,
           'id'=>$id,
           'from'=>$from
       ];
       return json_encode($j);
   }
    /**
     * @return string
     * 选择地区
     */

   public function area(){
       $data = Db::name('region')->where('region_grade',1)->select();

       foreach ($data as $k => $v) {
           $res_1 = Db::name('region')
               ->where('region_parent', $v['region_code'])
               ->select();
//           $data[$k][$v['region_fullname']] = $res_1;

           foreach ($res_1 as$k2 => $v2) {
               $res_2 = Db::name('region')
                   ->where('region_parent', $v2['region_code'])
                   ->select();
               $data[$k][$v['region_fullname']]  [$k2][$v2['region_fullname']] = $res_2;

           }
           unset($data[$k]['region_name']);
           unset($data[$k]['region_fullname']);
           unset($data[$k]['region_grade']);
           unset($data[$k]['region_code']);
           unset($data[$k]['region_parent']);
       }


       return json_encode($data);
   }

    /**
     * @return mixed
     * ip获取用户当前位置信息
     */

   public function ApiArea(){
       $address = [];
       $ip = $_SERVER["REMOTE_ADDR"];
       //通过ip获取 经纬度
       $url = "https://api.map.baidu.com/location/ip?ak=YCGs4ltGvN2Ist4trfn6ilHz7pUUh6l7&coor=bd09ll&ip=".$ip;
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
       $data = curl_exec($ch);
       curl_close($ch);
       //处理ip
       $ip = json_decode($data);
//       dump($ip->content->point);die;
       // ip的定位不能到区所以使用逆地理编码定位
       $getStreet = "http://restapi.amap.com/v3/geocode/regeo?output=json&location=".$ip->content->point->x.",".$ip->content->point->y."&key=b8deb8cb2756b5bb677ddd8cba4e79c2&radius=1000&extensions=all";

       $getStreetData = json_decode(file_get_contents($getStreet),true);

//       dump($getStreet);die;
       if($getStreetData['status'] == 1){
           $address['content']['address_detail']['province'] = $getStreetData['regeocode']['addressComponent']['province'];
           $address['content']['address_detail']['city'] = $getStreetData['regeocode']['addressComponent']['city'];
           $address['content']['address_detail']['district'] = $getStreetData['regeocode']['addressComponent']['district'];
       }else{
           $address['content']['address_detail']['province'] = "浙江省";
           $address['content']['address_detail']['city'] = "杭州市";
           $address['content']['address_detail']['district'] = "";
       }

       return json_encode($address);
   }

    /**
     * 备用公告
     */

   public function Inotice(){
       $data = Db::name('notice')->field("notice_content,create_time,category_id,id")->where('is_top',1)->limit(7)->select();
       foreach($data as $k=>$v){
           $c_data[$k]['notice_content']    = $v['notice_content'];
           $c_data[$k]['create_time']       = date("Y-m-d",$v['create_time']);
           $c_data[$k]['category_id']       = Db::name('Category')->where("category_id",$v['category_id'])->value('c_name');
           $c_data[$k]['id']                = $v['id'];
       }
       $j = [
           'data'=>$c_data
       ];
       return json_encode($j);
   }

    /**
     * @return mixed
     * 新闻详情页 --- 资讯接口
     */

   public function zixun(){
       $page        = input('page');
       $n_count     = Db::name("News")->count();
       //页码
       $n_num = ceil($n_count/5);
       if($page<=1){
           if($n_count < 5){
               $n_count = $n_count;
               $n_data = Db::name("News")->limit(0,$n_count)->order('time desc')->select();
               if(empty($n_data)){
                   $n_data['title'] = array();
               }
           }else{
               $n_data = Db::name("News")->limit(0,5)->order('time desc')->select();
           }
       }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
           $n_data = Db::name("News")->limit(($page-1)*5,5)->order('time desc')->select();
       }else if($page==$n_num){
           $n_data = Db::name("News")->limit($n_count-($n_num-1)*5)->order('time desc')->select();
       }
       if(empty($n_data)){
           $data = array();
       }else{

           foreach($n_data as $k=>$v){
               $data[$k]['title']       = $v['title'];
               $data[$k]['time']        = date("Y-m-d",$v['time']);
               $data[$k]['from']        = $v['from'];
               $data[$k]['id']          = $v['id'];
           }
       }
       $j = [
           'count'=>$n_count,
           'data'=>$data
       ];
       return json_encode($j);
   }

    /**
     * @return string
     * pc端的新闻 详情
     */

   public function web_read_new(){
       $id = input('id');
       //增加pv
       Db::name("news")->where([
           'id'=>$id
       ])->setInc("pv",rand(3,6));
       //当前id下的新闻
       $new = Db::name("news")->where([
           'id'=>$id
       ])->select();
       if(empty($new)){
           $data = NULL;
       }else{
           $data = $new[0];
           $data['time'] = date("Y-m-d",$data['time']);
       }
       //上一条新闻

       $prev_new = Db::name("news")->field("id,title,time")->where("id < $id")->limit(0,1)->order('id desc')->select();

       if(empty($prev_new)){
           $prev_data = NULL;
       }else{
           $prev_data = $prev_new[0];
       }
       //下一条新闻
       $next_new = Db::name("news")->field("id,title,time")->where("id",">",$id)->limit(1)->select();
       if(empty($next_new)){
           $next_data = NULL;
       }else{
           $next_data = $next_new[0];
       }
       $j = [
           'data'=>$data,
           'prev_data'=>$prev_data,
           'next_data'=>$next_data
       ];
       return json_encode($j);
   }

    /**
     * @return string
     * 咨询详情
     */

   public function web_read_notice(){
       //网站公告详情
       $id = input('id');
       //增加pv
       Db::name("web_notice")->where([
           'id'=>$id
       ])->setInc("pv",rand(3,6));
       //当前id下的新闻
       $notice = Db::name("web_notice")->where([
           'id'=>$id
       ])->select();
       if(empty($notice)){
           $data = NULL;
       }else{
           $data = $notice[0];
           $data['time'] = date("Y-m-d",$data['create_time']);
           $data['from'] = "官方";
       }
       //上一条新闻
       $prev_notice = Db::name("web_notice")->field("id,title,create_time")->where("id < $id")->limit(0,1)->order('id desc')->select();
       if(empty($prev_notice)){
           $prev_data = NULL;
       }else{
           $prev_data = $prev_notice[0];
       }
       //下一条新闻
       $next_notice = Db::name("web_notice")->field("id,title,create_time")->where("id",">",$id)->limit(1)->select();
       if(empty($next_notice)){
           $next_data = NULL;
       }else{
           $next_data = $next_notice[0];
       }


       $j = [
           'data'=>$data,
           'prev_data'=>$prev_data,
           'next_data'=>$next_data,
       ];
       return json_encode($j);
   }

    /**
     * @return \think\response\Json
     * 服务隐私等数据详细
     */

   public function web_read_more(){

       $id   = input('id');
       $type = input('type');
       $data = Db::name('WebService')->where(['id'=>$id])->find();


       //上一条
       $prev_notice = Db::name("WebService")->where("id < $id")->limit(0,1)->order('id desc')->find();
       if(empty($prev_notice)){
           $prev_data = NULL;
       }else{
           $prev_data['id'] = $prev_notice['id'];
           $prev_data['title'] = $prev_notice['title'];
       }

       //下一条
       $next_notice = Db::name("WebService")->where("id > $id")->limit(1)->find();
       if(empty($next_notice)){
           $next_data = NULL;
       }else{
           $next_data['id'] = $next_notice['id'];
           $next_data['title'] = $next_notice['title'];
       }

       if($data != NULL){
           foreach($data as $k=>$v){
               $info['title']       = $data['title'] == NULL ? NUll : $data['title'];
               $info['content']     = $data['web_fwxy'] == NULL ? $data['web_tk'] == NULL ? $data['web_bz'] == NULL ? $data['web_ptjs'] == NULL ? NULL :$data['web_ptjs']:$data['web_bz'] : $data['web_tk'] : $data['web_fwxy'];
               $info['time']        = date('Y-m-d H:i:s',$data['create_time']);
               $info['from']        = "官方";

           }
       }else{
           $info = array();
       }


       $j = [
           'data'=>$info,
           'prev_data'=>$prev_data,
           'next_data'=>$next_data,
           'code'=>200,
           'msg'=>'获取数据成功'
       ];

       return json($j);
   }

    /**
     * @return \think\response\Json
     * 底部数据id
     */

   public function foot(){
       $data['pt'] = Db::name('WebService')->where("web_ptjs","not NULL")->value('id');
       $data['bz'] = Db::name('WebService')->where("web_bz","not NULL")->value('id');
       $data['fw'] = Db::name('WebService')->where("web_fwxy","not NULL")->value('id');
       $data['tk'] = Db::name('WebService')->where("web_tk","not NULL")->value('id');

       $j = [
           'data'=>$data
       ];
       return json($j);
   }

    /**
     * @return string
     * 服务隐私详细数据左侧
     */

   public function web(){
       $page        = input('page');
       $type        = input('type');
       $n_count     = Db::name("WebService")->count();
       //页码
       $n_num = ceil($n_count/5);
       if($page<=1){
           if($n_count < 5){
               $n_count = $n_count;
               $n_data = Db::name("WebService")->limit(0,$n_count)->select();
               if(empty($n_data)){
                   $n_data['title'] = array();
               }
           }else{
               $n_data = Db::name("WebService")->limit(0,5)->select();
           }
       }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
           $n_data = Db::name("WebService")->limit(($page-1)*5,5)->select();
       }else if($page==$n_num){
           $n_data = Db::name("WebService")->limit($n_count-($n_num-1)*5)->select();
       }
       if(empty($n_data)){
           $data = array();
       }else{

           foreach($n_data as $k=>$v){
               $data[$k]['title']       = $v['title'];
               $data[$k]['time']        = date("Y-m-d",$v['create_time']);
               $data[$k]['content']          = $v['web_fwxy'] == NULL ? $v['web_tk'] == NULL ? $v['web_bz'] == NULL ? $v['web_ptjs'] == NULL ? NULL :$v['web_ptjs']:$v['web_bz'] : $v['web_tk'] : $v['web_fwxy'];
               $data[$k]['id']          = $v['id'];
           }
       }
       $j = [
           'count'=>$n_count,
           'data'=>$data
       ];
       return json_encode($j);
   }
    /**
     * @return string
     * 登录之后返回user_id
     */
   public function user_id(){
//       file_put_contents('/uploads/log.txt',1);
       $j = [
           'user_id'=>empty(session('user_id')) ? (int)0 : session('user_id')
       ];

       return json_encode($j);
   }


    public function web_zixun(){
        $page        = input('page');
        $n_count     = Db::name("web_notice")->count();
        //页码
        if((input('index') == 1)){

            $n_num = ceil($n_count/7);
            if($page<=1){
                if($n_count < 7){
                    $n_count = $n_count;
                    $n_data = Db::name("News")->limit(0,$n_count)->select();
                    if(empty($n_data)){
                        $n_data['title'] = "";
                        $n_data['time'] = "";
                        $n_data['from'] = "";
                    }
                }else{
                    $n_data = Db::name("News")->limit(0,5)->select();
                }
            }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
                $n_data = Db::name("News")->limit(($page-1)*5,5)->select();
            }else if($page==$n_num){
                $n_data = Db::name("News")->limit($n_count-($n_num-1)*5)->select();
            }
            foreach($n_data as $k=>$v){
                $data[$k]['title']       = $v['title'];
                $data[$k]['time']        = date("Y-m-d",$v['time']);
                $data[$k]['from']        = $v['from'];
            }
        }else{
            $n_num = ceil($n_count/5);
            if($page<=1){
                if($n_count < 5){
                    $n_count = $n_count;
                    $n_data = Db::name("web_notice")->limit(0,$n_count)->select();
                    if(empty($n_data)){
                        $n_data['title'] = "";
                        $n_data['time'] = "";
                        $n_data['from'] = "";
                    }
                }else{
                    $n_data = Db::name("News")->limit(0,5)->select();
                }
            }else if($page>1 && $page <= ($n_num-1) && $n_num>1){
                $n_data = Db::name("News")->limit(($page-1)*5,5)->select();
            }else if($page==$n_num){
                $n_data = Db::name("News")->limit($n_count-($n_num-1)*5)->select();
            }
            foreach($n_data as $k=>$v){
                $data[$k]['title']       = $v['title'];
                $data[$k]['time']        = date("Y-m-d",$v['time']);
                $data[$k]['from']        = $v['from'];
            }
        }
        $j = [
            'count'=>$n_count,
            'data'=>$data
        ];
        return json_encode($j);
    }



}
