<?php

namespace app\admin\controller;

use app\common\model\Hetong;
use think\Db;

class Settings extends Base
{

    public function logo ()
    {
        $logo = Db::table('logo')->value('logo');
        $data = [
            'title' => 'Logo管理',
            'logo' => $logo
        ];
        return view('Settings/logo',$data);
    }


    public function Update_logo ()
    {
        $file = request()->file('pic');
        if(!empty($file)) {
            $info = $file->move('uploads/');
            if ($info) {
                $pic = '/uploads/'.$info->getSaveName();
                Db::table('logo')->where('id',1)->update(['logo' => $pic]);
            }
        }
        return redirect('Settings/logo');
    }


    public function news ()
    {
        $news = Db::table('news')->order('time desc')->paginate(10);
        $data = [
            'news' => $news,
            'title' => '新闻管理'
        ];
        return view('Settings/news',$data);
    }


    public function news_del ()
    {
        Db::table('news')->where('id',input('id'))->delete();
        return redirect('Settings/news');
    }


    public function banners ()
    {
        $banners = Db::table('banner')->paginate(10);
        $data = [
            'banners' => $banners,
            'title' => '轮播图管理'
        ];
        return view('Settings/banner',$data);
    }


    public function banner_add ()
    {
        return view('Settings/banner_add',['title'=>'轮播图添加']);
    }


    public function banner_insert ()
    {
        $pic = "";
        $pc_pic = "";
        $file = request()->file('cell_pic');
        if(!empty($file)) {
            $name = 'uploads/'.time().rand(1,999999).'.png';
            $image = \think\Image::open(request()->file('cell_pic'));
            $image->thumb(750, 350)->save($name);
            $pic = '/'.$name;
        }
        $pc_file = request()->file('pc_pic');
        if(!empty($pc_file)) {
            $name = './uploads/'.time().rand(1,999999).'.png';
            $image = \think\Image::open(request()->file('pc_pic'));
            $image->thumb(750, 350)->save($name);
            $pc_pic ='/'.$name;
        }

        Db::table('banner')->insert(['cell_banner' => $pic,'pc_banner'=>$pc_pic]);
        return redirect('Settings/banners');
    }


    public function banner_edit ()
    {
        $banner = Db::table('banner')->where('id',input('id'))->find();
        $data = [
            'banner' => $banner,
            'title' => '轮播图修改'
        ];
        return view('Settings/banner_edit',$data);
    }


    public function banner_update ()
    {

        $data = [];
        $file = request()->file('pc_banner');
        if(!empty($file)) {
            $name = 'uploads/'.time().rand(1,999999).'.png';
            $image = \think\Image::open(request()->file('pc_banner'));
            $image->thumb(750, 350)->save($name);
            $data['pc_banner'] = '/'.$name;
        }

        $cell_file = request()->file('cell_banner');

        if(!empty($cell_file)) {
            $name = './uploads/'.time().rand(1,999999).'.png';
            $image = \think\Image::open(request()->file('cell_banner'));
            $image->thumb(750, 350)->save($name);
            $data['cell_banner'] ='/'.$name;
        };


        Db::table('banner')->where('id',input('id'))->update($data);
        return redirect('Settings/banners');
    }


    public function banner_del ()
    {
        Db::name("banner")->where("id",input('id'))->delete();
        return redirect('Settings/banners');
    }


    public function technologys ()
    {
        $technologys = Db::table('technology t')
            ->join('category c','t.category_id = c.category_id')
            ->field('t.*,c.c_name')
            ->paginate(10);
        $data = [
            'technologys' => $technologys,
            'title' => '工艺介绍'
        ];
        return view('Settings/technology',$data);
    }


    public function technology_add ()
    {
        $categorys = Db::table('category')->where('level',1)->select();
        $data = [
            'categorys' => $categorys,
            'title' => '工艺介绍新增'
        ];
        return view('Settings/technology_add',$data);
    }


    public function technology_insert ()
    {
        Db::table('technology')->insert([
            'content'       => input('content'),
            'category_id'   => input('category_id'),
            'title'         => input('title')
        ]);
        return redirect('Settings/technologys');
    }


    public function technology_edit ()
    {
        $categorys = Db::table('category')->where('level',1)->select();
        $technology = Db::table('technology')->where('id',input('id'))->find();
        $data = [
            'categorys' => $categorys,
            'technology' => $technology,
            'title' => '工艺介绍修改'
        ];
        return view('Settings/technology_edit',$data);
    }


    public function technology_update ()
    {
        Db::table('technology')
            ->where('id', input('id'))
            ->update([
                'content' => input('content'),
                'category_id' => input('category_id'),
                'title' => input('title')
            ]);
        return redirect('Settings/technologys');
    }


    public function technology_del ()
    {
        Db::table('technology')->where('id',input('id'))->delete();
        return redirect('Settings/technologys');
    }

    /**
     * @return \think\response\View
     * 数据统计
     */

    public function month_total(){

        //今日新增用户
        $today_zero = strtotime('today');
        $hold_day = $today_zero+86400;
        $visit = Db::name("Visit")->select();



        $arr_mem = array();
        foreach ($visit as $k => $v) {
            $datetime = substr(date("Y-m-d",$v['visit_time']),0,10);//得到年月日
            //得到每日新增用户数
            if(array_key_exists($datetime,$arr_mem)){
                $arr_mem[$datetime] +=1;
            }else{
                $arr_mem[$datetime] =1;
            }
        }

        //月点击量最高的用户
        $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
        $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));

        $month_visit_count = $this->math_total($beginThismonth,$endThismonth,2);


        return json(['data' => $month_visit_count]);
    }


    public function day_total(){
        //今日新增用户
        $today_zero = strtotime('today');
        $hold_day = $today_zero+86400;
        //日点击量最高的用户

        $today_visit_count = $this->math_total($today_zero,$hold_day,1);

        return json(['data'=>$today_visit_count]);
    }

    public function ip_visit(){
        $visit = $this->ip();

        foreach($visit as $k=>$v){
            $ip[$k]['name'] = $v;
        }
        return json(['data'=>$ip]);
    }

    /**
     * @return \think\response\Json
     * 发送公告的次数
     */

    public function notice_time(){
        //发送公告的次数
        $noticed_time = Db::name('User')->field('id,noticed_time')->where("noticed_time != 0")->order('noticed_time DESC')->select();
        foreach($noticed_time as $k=>$v){
            $data[$k]['name'] = empty(Db::name('UserInformation')->where(['user_id'=>$v['id']])->value('company_name')) ? Db::name('User')->where(['id'=>$v['id']])->value('login_cell')
            :Db::name('UserInformation')->where(['user_id'=>$v['id']])->value('company_name');

            $data[$k]['notice_time'] = $v['noticed_time'];
        }
        return json(['data'=>$data]);
    }

    public function evaluate_time(){
        $noticed_time = Db::name('User')->field('id,evaluated_time')->where("evaluated_time != 0")->order('evaluated_time DESC')->select();
        foreach($noticed_time as $k=>$v){
            $data[$k]['name'] = empty(Db::name('UserInformation')->where(['user_id'=>$v['id']])->value('company_name')) ? Db::name('User')->where(['id'=>$v['id']])->value('login_cell')
                :Db::name('UserInformation')->where(['user_id'=>$v['id']])->value('company_name');

            $data[$k]['evaluated_time'] = $v['evaluated_time'];
        }
        return json(['data'=>$data]);
    }


    public function index(){
        //注册用户总数
        $user_count = Db::name('User')->count();
        //今日新增用户
        $today_zero = strtotime('today');
        $hold_day = $today_zero+86400;
        $today_user = Db::name('User')->where("register_time",">","$today_zero")->where("register_time","<","$hold_day")->count();

        //总访问量
        $visit_count = Db::name("Visit")->count();

        //今日访问量
        $today_visit = Db::name('Visit')->where("visit_time",">","$today_zero")->where("visit_time","<","$hold_day")->count();
        $j = [
            'user_count'=>$user_count,
            'today_user'=>$today_user,
            'visit_count'=>$visit_count,
            'today_visit'=>$today_visit,
            'title'=>"网站统计",
        ];
        return view("Settings/index",$j);
    }
    /**
     * @param $begin 开始时间
     * @param $end   结束时间
     * @return mixed 排序结果
     * 对访问量进行统计
     */

    public function math_total($begin,$end,$type){
        if($type == 1){
            $sql = "select user_id,count(visit_time) from visit where visit_time> '$begin' and visit_time<'$end' GROUP BY user_id DESC ";
            $count = Db::query($sql);
            //冒泡排序将数组顺序重组
            $len=count($count);//6
            for($k=1;$k<$len;$k++)
            {
                for($j=0;$j<$len-$k;$j++){
                    if($count[$j]["count(visit_time)"]<$count[$j+1]["count(visit_time)"]){
                        $temp =$count[$j+1];
                        $count[$j+1] =$count[$j] ;
                        $count[$j] = $temp;
                    }
                }
            }

            foreach($count as $k=>$v){
                $check[] = empty(Db::name("UserInformation")->field('company_name,user_id')->where(['user_id'=>$v['user_id']])->find()) ? empty(Db::name('User')->field('login_cell,id')->where(['id'=>$v['user_id']])->find()) ? "" :Db::name('User')->field('login_cell,id')->where(['id'=>$v['user_id']])->find() : Db::name("UserInformation")->field('company_name,user_id')->where(['user_id'=>$v['user_id']])->find();
                foreach($check as $k1=>$v1){
                    if(empty($v1)){
                        unset($check[$k1]);
                    }
                }
            }
            if(empty($check)){
                $today_visit_count = array();
            }else{
                foreach($check as $k=>$v) {
                    $today_visit_count[$k]['foot'] = $k;
                    $today_visit_count[$k]['name'] = empty($v['company_name']) ? $v['login_cell'] : $v['company_name'];
                    $today_visit_count[$k]['user_id'] = empty($v['user_id']) ? $v['id'] : $v['user_id'];
                    $today_visit_count[$k]['count_visit'] = empty($count[$k]["count(visit_time)"]) ? 1 : $count[$k]["count(visit_time)"];
                }
            }

            sort($today_visit_count);
            $retJson = $today_visit_count;
        }else{
            $time = date('Y',time());
            $month = [
                $time.'-01',
                $time.'-02',
                $time.'-03',
                $time.'-04',
                $time.'-05',
                $time.'-06',
                $time.'-07',
                $time.'-08',
                $time.'-09',
                $time.'-10',
                $time.'-11',
                $time.'-12',
            ];
            foreach($month as $k=>$v){
                $sql = "SELECT COUNT(*) AS total FROM `visit` WHERE FROM_UNIXTIME(`visit_time`,'%Y-%m')='".$v."'";
                $count[$k] = Db::query($sql);
            }

            foreach ($count as $k=>$v){
                $retJson[$k] = $v[0];
                $retJson[$k]['month'] = $month[$k];
            }
        }

        return $retJson;
    }

    /**
     * @return mixed 排序结果
     * 对访问ip量进行统计
     */

    public function ip(){
        $sql = "select ip,count(ip),province from visit  GROUP BY province ASC ";

        $count = Db::query($sql);

        $len = count($count);
        for($k=1;$k<$len;$k++)
        {
            for($j=0;$j<$len-$k;$j++){
                if($count[$j]["count(ip)"]<$count[$j+1]["count(ip)"]){
                    $temp =$count[$j+1];
                    $count[$j+1] =$count[$j] ;
                    $count[$j] = $temp;
                }
            }
        }

        foreach($count as $k=>$v){
//            $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$v['ip'];
//            $total_data[$k] = json_decode(file_get_contents($url));
        }

        foreach($count as $k=>$v){
            $check[] = $v['province'];
        }
//        $data = array_unique($check);
//        sort($data);
//        $data = array_reverse($data);
        return $check;
    }



    public function news_edit(){
        $id = input('id');
        $news_data = Db::name("News")->where(['id'=>$id])->find();
//        var_dump($news_data);die;
        $data = [
            'new'       =>$news_data,
            'title'=>"新闻修改"
        ];
        return view("Settings/news_edit",$data);
    }

    public function news_update(){
        $id = input('id');
//        echo $id;die;
        $file = request()->file('img');
        if(!empty($file)) {
            $info = $file->move('uploads/');
            if ($info) {
                $pic = '/uploads/'.$info->getSaveName();
            }
        }else{
            $pic = Db::name("News")->where(['id'=>$id])->value('img');
        }
//        var_dump(input('content'));die;

        Db::name("News")->where(['id'=>$id])->update([
            'title'=>input('title'),
            'img'=>$pic,
            'content'=>input('content'),
            'from'=>input('from')
        ]);
        return redirect('Settings/news_edit?id='.$id);
    }

    /**
     * @return \think\response\View
     * 统计列表
     */

    public function table(){
        $type = input('type');
        if($type == 1){
            $id = "table_today";
            $name = "注册会员日访问排行";
        }else if($type == 2){
            $id = "table_month";
            $name = "注册会员月访问排行";
        }else if($type == 3){
            $id = "table_ip";
            $name = "地区活跃度排行榜";
        }else if($type == 4){
            $id = "table_notice";
            $name = "公告发布列表";
        }else if($type == 5){
            $id = "table_eva";
            $name = "评价发送列表";
        }else if($type == 6){
            $id = "table_qian";
            $name = "签约统计";
        }

        $j = [
            'title'=>"统计列表",
            'id'=>$id,
            'name'=>$name
        ];

        return view('Settings/table',$j);
    }

    /**
     * @return \think\response\Json
     * 签约统计
     */

    public function qian_new(){
        $sql = "SELECT user_id,count(1) AS counts FROM customer where status = 300 GROUP BY user_id ORDER BY counts DESC ";

        $data = Db::query($sql);

        foreach($data as $k=>$v){
            $id = $v['user_id'];
            $name = Db::name("UserInformation")->where(['user_id'=>$v['user_id']])->value('company_name');
            $data[$k]['company_name'] = "<a href='/admin/user/hetong_read?id=$id' style='color: black'>$name</a>";
        }
        return json(['data'=>$data]);
    }

    public function muban(){
        $data= Db::name('hetong')->select();
        $j = [
            'title'=>'模板合同列表',
            'data'=>$data
        ];
        return view('Settings/muban',$j);
    }


    public function muban_edit(){
        $j = [
            'title'=>"模板合同编辑",
            'id'    =>input('id')
        ];

        return view('Settings/muban_edit',$j);
    }

    public function muban_update(){
        $id = input('id');
        $file_name = explode('.',$_FILES['contract']['name']);
        $data['contract'] = "";
        $data['pdf_contract'] = "";
        $file = request()->file('contract');
        if(!empty($file)) {
            $info = $file->move('uploads/','');
            if ($info) {
                $pic = '/uploads/'.$info->getSaveName();
                $data['contract'] = $pic;
            }
        }
        $pdf = request()->file('pdf_contract');
        if(!empty($pdf)) {
            $info = $pdf->move('uploads/','');
            if ($info) {
                $pic = '/uploads/'.$info->getSaveName();
                $data['pdf_contract'] = $pic;
            }
        }

        if($id == 0){
            $hetong = new Hetong;
            $hetong->contract_name = $file_name[0];
            $hetong->url = $data['contract'];
            $hetong->read_url = $data['pdf_contract'];
            $hetong->create_time = time();
            $s = $hetong->save();
        }else{
            $s = Db::name('Hetong')->where(['id'=>$id])->update([
                'contract_name' =>$file_name[0],
                'url'           =>$data['contract'],
                'read_url'      =>$data['pdf_contract']
            ]);
        }

        if($s){
            echo "<script>alert('保存成功');location.href = '/admin/settings/muban'</script>";
        }
    }

    public function muban_new(){
        $j = [
            'title'=>"模板合同新增",
            'id'   =>0
        ];

        return view('Settings/muban_edit',$j);
    }

    public function muban_del(){
        Db::name('hetong')->where(['id'=>input('id')])->delete();

        return json(200);
    }



    public function site_foot(){
        $j = [
            'title'=>'网站底部信息列表'
        ];
        return view('Settings/site_foot',$j);
    }

    public function site_foot_ajax(){
        $data = Db::name('WebService')->select();
        foreach($data as $k=>$v){
            switch ($v['type']){
                case 1:
                    $text = "平台介绍";
                    $content = $v['web_ptjs'];
                    break;
                case 2:
                    $text = "帮助";
                    $content = $v['web_bz'];
                    break;
                case 3:
                    $text = "服务协议";
                    $content = $v['web_fwxy'];
                    break;
                case 4:
                    $text = "条款与隐私";
                    $content = $v['web_tk'];
                    break;
            }
            $info[$k]['title'] = $text;
            $info[$k]['content'] = mb_substr($content,0,500,'utf-8');
            $info[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            $id = $v['id'];
            $info[$k]['caozuo'] = "<a class=\"btn btn-info\" href=\"/Admin/Settings/site_foot_edit?id=$id\">编辑</a>";
        }

        return json(['data'=>$info]);
    }


    public function site_foot_edit(){
        $id = input('id');
        $data = Db::name('WebService')->where(['id'=>$id])->find();
        switch ($data['type']){
            case 1:
                $text = "平台介绍";
                $content = $data['web_ptjs'];
                break;
            case 2:
                $text = "帮助";
                $content = $data['web_bz'];
                break;
            case 3:
                $text = "服务协议";
                $content = $data['web_fwxy'];
                break;
            case 4:
                $text = "条款与隐私";
                $content = $data['web_tk'];
                break;
        }

        $data['title'] = $text;
        $data['content'] = $content;
        $j = [
            'title'=>'信息编辑',
            'data'=>$data
        ];

        return view('Settings/site_foot_edit',$j);
    }

    public function foot_update(){

        $id = input('id');
        $type = input('type');

        switch ($type){
            case 1:
                $text = "web_ptjs";
                $data   = input('content');
                break;
            case 2:
                $text = "web_bz";
                $data     = input('content');
                break;
            case 3:
                $text = "web_fwxy";
                $data   = input('content');
                break;
            case 4:
                $text = "web_tk";
                $data    = input('content');
                break;
        }



        $s = Db::name('WebService')->where(['id'=>$id])->update([
            "$text"=>$data
        ]);

        if($s){
            echo "<script>alert('修改成功');location.href = '/admin/settings/site_foot'</script>";
        }
    }
}
