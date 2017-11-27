<?php
namespace app\admin\controller;


use app\common\model\Category;
use app\common\model\Province;
use think\Db;

class Add extends Base{
    public function index(){
        $province   = Db::table('province')->select();
        $city       = Db::table('city')->where('father',110000)->select();
        $area       = Db::table('area')->where('father',110100)->select();
        $cat        = Db::table('category')->select();;
        $catTree = new Category();
        $Cdata  = $catTree->make_tree1($cat);
//        die;
        foreach($Cdata as $k=>$v){
            if(!empty($v['child'])){
                foreach ($v['child'] as $k1=>$v1){
                    $Cdata[$k]['child'][$k1]['child'] = Db::table('category')->where('pid',$v1['category_id'])->select();
                }
            }else{
                $Cdata[$k]['child'][0]['c_name'] = "";
                $Cdata[$k]['child'][0]['category_id'] = "";
                $Cdata[$k]['child'][0]['child'][0]['c_name'] = "";
                $Cdata[$k]['child'][0]['child'][0]['category_id'] = "";
            }
        }
//        dump($Cdata);die;
        $data = [
            'title'=>"增加新用户",
            'province'=>$province,
            'city'=>$city,
            'area'=>$area,
            'cat'=>$Cdata,
        ];
        return view('Add/index',$data);
    }

    /**
     * @return array
     * author hongwenyang
     * method description : 处理图片
     */
    public function img(){
        $url = CompressImg(request()->file('file'));

        return ['data'=>$url];
    }


    /**
     * @return \think\response\Json
     * author hongwenyang
     * method description : 获取城市 地区列表
     */

    public function city(){
        $father = input('father');

        $data = Province::city($father);

        return json($data);
    }

    /**
     * @return \think\response\Json
     * author hongwenyang
     * method description : 获取地区列表
     */
    public function area(){
        $father = input('father');

        $data = Province::area($father);

        return json($data);
    }

    /**
     * @return \think\response\Json
     * author hongwenyang
     * method description : 获取二级、三级分类
     */

    public function cat(){
        $father = input('fid');

        $data = Province::cat($father);

        return json($data);
    }


    /**
     * @return \think\response\Json
     * author hongwenyang
     * method description : 获取三级分类
     */

    public function catSec(){
        $father = input('fid');

        $data = Province::catSec($father);

        return json($data);
    }


    /**
     * @return int
     * author hongwenyang
     * method description : 黑科技  用户要后台就可以增加新用户  这样看起来网站有很多公司入驻
     */
    public function add(){
        $data =  input('post.');

        //将这个数据添加到user 表
        $userId = Db::table('user')->insertGetId([
            'login_cell'=>$data['tel'],
            'notice_use_time'=>time() + (60*60*24*365),
            'register_time'=>time(),
            'type'=>1,
            'level'=>2
        ]);
        //处理数据

        $insert['company_address']      = $data['province'] . $data['city'] . $data['area'];
        $prvince = $data['provinceId'];
        $city = $data['cityId'];
        $area = $data['areaId'];
        $insert['company_address_val']  = "['".$prvince."','".$city."','".$area."']";
        $insert['name']                 = $data['name'];
        //保存头图
        $headPic = "";
        if(!empty($data['head_pic'])){
            $headPic                        = Db::table('head_pic')->insertGetId([
                'pic_url'=>$data['head_pic'],
                'user_id'=>$userId
            ]);
        }
        $insert['company_video']        = $data['company_video'];
        $insert['company_name']         = $data['company_name'];
        $insert['is_authentication']    = 0;
        $insert['is_top']               = 0;
        $insert['tel']                  = $data['tel'];
        $insert['qq']                   = $data['qq'];

        //业务范围

        $insert['introduce']            = $data['introduce'];
        $insert['user_id']              = $userId;
        // 获取经纬度

        if(!empty($insert['company_address'])){

            $url = 'http://restapi.amap.com/v3/geocode/geo?address='.$insert['company_address'].'&output=json&key=68e535a816c751afc9ae25a975cf8459';

            $string = file_get_contents($url);

            $string = json_decode($string);
            if($string->status == 1){
                $jw = explode(',',$string->geocodes[0]->location);

                $insert['avl'] = $jw[0];
                $insert['evl'] = $jw[1];
            }
        }
        $insert['head_pic']    = $headPic;
        $insert['create_time'] = time();
        $company_authentication = "";
        //处理认证图片
        if(!empty($data['company_authentication'])){
            $img = explode(",",$data['company_authentication']);
            foreach($img as $k=>$v){
                $id = Db::table('authentication_pic')->insertGetId([
                    'pic_url'=>$v,
                    'user_id'=>$userId
                ]);
                $company_authentication .= $id . ",";
            }
            $insert['company_authentication'] = rtrim($company_authentication,",");
        }
        $insert['business'] = $data['business'];
        //处理分类数据
        if(!empty($data['business'])){
            $busienss = explode(',',$data['business']);
            foreach ($busienss as $v){
                Db::table('user_category')->insert([
                    'user_id'=>$userId,
                    'category_id'=>$v
                ]);
            }
        }
        $s = Db::table('user_information')->insert($insert);
        if($s){
            $code = 200;
        }else{
            $code = 404;
        }
        return $code;
    }
}
