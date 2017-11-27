<?php
namespace app\admin\controller;

use app\common\model\AuthenticationPic;
use app\common\model\HeadPic;
use think\Db;
use app\common\model\Category;
use app\common\model\UserCategory;
use app\common\model\UserInformation;
use app\admin\controller\Base;

class User extends Base
{


    /**
     * @return \think\response\View
     * 填写企业信息的数据
     */

    public function index()
    {

        $data = [
            'title'=>'企业列表',
        ];

        return view('User/index',$data);
    }
    /**
     * @return \think\response\Json
     * 已完善企业信息部分
     */

    public function user_ajax(){
        $info = Db::table('user u')
            ->join('user_information i', 'u.id = i.user_id')
            ->field('u.*,i.is_top,i.company_name')
            ->order('u.id desc')
            ->select();

        foreach($info as $k=>$v){
            $img = $v['head_img'];
            $id = $v['id'];
            if(empty($img)){
                $info[$k]['head_img']   = "";
            }else{
                $info[$k]['head_img']   = "<img src='$img' style='width: 50px;height: 50px'>";
            }
            $info[$k]['register_time'] = date('Y-m-d H:i:s',$v['register_time']);
            if($v['is_top'] == 1){
                $tui = "<button class='btn btn-danger' onclick='tuijian($id)' id='{$id}'>取消推荐</button>";
            }else{
                $tui = "<button class='btn btn-success' onclick='tuijian($id)' id='{$id}'>首页推荐</button>";
            }
            $info[$k]['caozuo']     = " <td class=\"center hidden-phone\">
                    <button class=\"btn btn-info\" onclick=\"edit($id)\">修改密码</button>
                    <button class=\"btn btn-info\" onclick=\"more($id)\">查看企业信息</button>
                    <button class=\"btn btn-danger\" onclick=\"del($id)\">删除</button>
                    
                     ".$tui."  
                </td>";
            $info[$k]['register_time'] = date('Y-m-d H:i:s',$v['register_time']);

            if($v['type']){
                $info[$k]['type'] = "后台手动添加";
            }else{
                $info[$k]['type'] = "用户注册";
            }
        }
        return json(['data'=>$info]);
    }

    /**
     * @return \think\response\View
     * 未完善企业信息列表
     */

    public function index_no()
    {

        $data = [
            'title'=>'企业列表',
        ];

        return view('User/index_no',$data);
    }


    /**
     * @return \think\response\Json
     * 未完善用户数据
     */

    public function user_ajax_no(){
        $info = Db::table('user')->where("")->order('register_time desc')
            ->select();

        foreach($info as $k=>$v){
            $check = Db::name('UserInformation')->where(['user_id'=>$v['id']])->value('id');
            if(empty($check)){
                $img = $v['head_img'];
                $id = $v['id'];
                if(empty($img)){
                    $info[$k]['head_img']   = "";
                }else{
                    $info[$k]['head_img']   = "<img src='$img' style='width: 50px;height: 50px'>";
                }


                $info[$k]['register_time'] = date('Y-m-d H:i:s',$v['register_time']);

                $info[$k]['caozuo']     = " <td class=\"center hidden-phone\">
                    <button class=\"btn btn-info\" onclick=\"edit($id)\">修改密码</button>
                    <button class=\"btn btn-info\" onclick=\"more($id)\">查看企业信息</button>
                    <button class=\"btn btn-danger\" onclick=\"del($id)\">删除</button>
                 
                </td>";
                $info[$k]['register_time'] = date('Y-m-d H:i:s',$v['register_time']);
            }else{
                unset($info[$k]);
            }
        }

        sort($info);
//                echo "<pre>";
//        var_dump($info);die;
        return json(['data'=>$info]);
    }


    public function edit_pwd ()
    {
        $this->assign('id', input('id'));
        $this->assign('title', '修改密码');
        return $this->fetch('User/edit_pwd');
    }


    public function update_pwd ()
    {
        $data['user_pwd'] = sha1(input('password'));
        Db::table('user')->where('id', input('id'))->update($data);
        echo '<script>
            parent.$("#handle_status").val("1");
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);</script>';
    }


    public function category()
    {
        $first = Category::where('level',1)->select();
        $second = Category::where('level',2)->select();
        $third = Category::where('level',3)->select();


        $data  = [
            'first'=>$first,
            'second'=>$second,
            'third'=>$third,
            'title'=>'企业分类'
        ];
       return view('User/category',$data);
    }

    /**
     * 保存企业分类  一级分类和二级分类使用
     */
    public function save(){
        $pid = input('p_id');
        if(empty($pid)){
//            echo 1;die;
            $first = input('first_lev');
            $category = new Category();
            $category->level = 1;
            $category->c_name = $first;
//            $category->pid = $category->category_id;
            $category->is_show = 0;
            $category->save();
        }else{
            $second = input('c_name');
            $category = new Category();
            $category->level = 2;
            $category->c_name = $second;
            $category->pid = $pid;
            $category->is_show = 0;
            $category->save();
        }
    }
    /**
     * 第三级分类添加使用
     */

    public function new_save(){
        $pid = input('p_id');
        $third = input('c_name');
        $category = new Category();
        $category->level = 3;
        $category->c_name = $third;
        $category->pid = $pid;
        $category->is_show = 0;
        $category->save();
    }



    public function edit(){
        $id = input('c_id');
        $first = input('first_lev');
        Category::update(['category_id'=>$id,'c_name'=>$first]);
    }

    public function del(){

        $category_id = input('c_id');
        $level =  new \app\common\model\Category();
        $result = $level->level_num($category_id);
        $note = "该一级分类包含二级分类,无法删除分类";
        $note_video = "该二级分类下包含视频文件,无法删除分类 ";
        $note_success = "删除分类成功";
        if($result == 101){
            return $note;
        }else if($result == 102){
            return $note_video;
        }else if($result == 200){
            if(\app\common\model\Category::table('category')->where('category_id',$category_id)->delete()){
                return $note_success;
            }else{
                return $note_success;
            }
        }
    }

    public function c_del(){

        $category_id = input('c_id');
        Category::destroy($category_id);
        return "删除成功";
    }

    /**
     * @return \think\response\View
     * 查看会员的公司全部信息
     */
    public function read(){
        $id = input('id');
        $info = Db::name("UserInformation")->where(['user_id'=>$id])->find();
//        $category = Db::name("Category")->field("c_name")->where("category_id","in","25,29")->select();

        if(empty($info['business'])){
            $info['business'] = "";
        }else{
            $info_c = explode(',',$info['business']);
            foreach($info_c as $k=>$v){
                $cate['business'][$k] = Db::name("Category")->where(['category_id'=>$v])->value('c_name');
            }
            $info["business"] = empty($cate["business"]) ? "" : implode(",", $cate["business"]);
        }
        if(empty($info['company_authentication'])){
            $info['company_authentication'] = "" ;
        }else{
            $info['company_authentication'] = explode(",",$info['company_authentication']);
            foreach($info['company_authentication'] as $k=>$v){
                $info['company_authentication'][$k]  = Db::name("authentication_pic")->where([
                    'id'=>$v
                ])->value('pic_url');;
            }
        }
        if(empty($info['head_pic'])){
            $info['head_pic'] = "" ;
        }else{
            $info['head_pic'] = Db::name('head_pic')->where(['id'=> $info['head_pic']])->value('pic_url') ;
        }
        $data = [
            'title'=>'企业信息',
            'info'=>$info
        ];
        return view('User/read',$data);
    }

    /**
     * 推荐修改
     */
    public function T_edit(){
        $id = input('id');
        $is_top = Db::name("UserInformation")->where(['user_id'=>$id])->value('is_top');

        if($is_top === NULL){
            $info['msg'] = '推荐失败!~未填写企业资料!~ 请用户填写后在操作！';
            $info['status'] = 300;
        }else if(($is_top === 0)){
            UserInformation::where(['user_id'=>$id])->update(['is_top'=>1]);
            $info['msg'] = '推荐成功';
            $info['status'] = 100;
        }else if($is_top === 1){
            UserInformation::where(['user_id'=>$id])->update(['is_top'=>0]);
            $info['msg'] = '取消成功';
            $info['status'] = 101;
        }
        return $info;
    }


    public function authentication ()
    {

        $data  = [
            'title'=>'认证企业'
        ];

       return view('User/authentication', $data);
    }


    public function auth_ajax(){
        $a_data = Db::table('user_information')
            ->field('id,company_name,is_authentication,introduce,business,company_authentication,user_id')
            ->where('company_authentication', '<>', '')
            ->order('create_time desc')
            ->select();

        foreach($a_data as $k=>$v){
            $info[$k]['id']                 = $v['user_id'];
            $info[$k]['company_name']       = $v['company_name'];
            $info[$k]['is_authentication']  = $v['is_authentication'];
            $info[$k]['introduce']          = mb_substr($v['introduce'],0,100);
            $c_id[$k]                       = $v['business'];
            $c_name[$k]["business"] = Db::name("Category")->field("c_name")->where("category_id", "in", "$c_id[$k]")->select();
            foreach ($c_name[$k]["business"] as $k1 => $v1) {
                $c_name_use[$k][$k1] = empty($v1['c_name']) ? "" : $v1['c_name'];
            }
            $info[$k]["business"] = empty($c_name_use[$k]) ? "" : implode(",", $c_name_use[$k]);


            $authen_id[$k]                              = $v['company_authentication'];
            if(empty($authen_id)){
                $info[$k]['img_url'] = array();
            }else{
                $com_authen_id = explode(',',$authen_id[$k]);
                foreach($com_authen_id as $k2=>$v2){
                    $img = Db::name("authentication_pic")->where([
                        'id'=>$v2
                    ])->value('pic_url');
                    $info[$k]['img'][$k2] = "<img src='$img' style='width: 100px;height: 75px;'>";
                }

            }
            $level = Db::name('User')->where(['id'=>$v['user_id']])->value('level');
            switch ($level){
                case 0:
                    $text = "未认证";
                    break;
                case 1:
                    $text = "初级认证";
                    break;
                case 2:
                    $text = "高级认证";
                    break;
                case 4:
                    $text = "等待验证通过";
            }
            $info[$k]['renzheng'] = $text;
            $info[$k]['level'] = $level;
            $id = $v['user_id'];
            $info[$k]['caozuo']  = "<button class=\"btn btn-success\" onclick=\"renzheng($id)\" id=\"$id\">认证操作</button>";
        }

        return json(['data'=>$info]);
    }

    public function img(){
        $category_id = input('category_id');
        $c_data = Db::name("Category")->where(['category_id'=>$category_id])->field("pc_pic,c_pic,c_name")->find();

        $data = [
            'title' => '分类图片管理',
            'pc_img'   => $c_data['pc_pic'],
            'c_img'   => $c_data['c_pic'],
            'c_name'   => $c_data['c_name'],
            'category_id'=>$category_id
        ];
        return view('User/img',$data);
    }

    public function Update_pc_img(){
        $category_id = input('category_id');
        $file = request()->file('pc_pic');
        if(!empty($file)) {
            $info = $file->move('uploads/');
            if ($info) {
                $pic = '/uploads/'.$info->getSaveName();
                Db::name('Category')->where('category_id',$category_id)->update(['pc_pic' => $pic]);
            }
        }
        return redirect('User/img?category_id='.$category_id);
    }

    public function update_m_img(){
        $category_id = input('category_id');
        $file = request()->file('c_pic');
        if(!empty($file)) {
            $info = $file->move('uploads/');
            if ($info) {
                $pic = '/uploads/'.$info->getSaveName();
                Db::name('Category')->where('category_id',$category_id)->update(['c_pic' => $pic]);
            }
        }
        return redirect('User/img?category_id='.$category_id);
    }

    public function category_tui(){
        $id = input('id');
        $is_show = Category::where(['category_id'=>$id])->value('is_show');

        if($is_show == 0){
            Category::where(['category_id'=>$id])->update(['is_show'=>1]);
            $info['msg'] = '推荐成功';
            $info['status'] = 100;
        }else{
            Category::where(['category_id'=>$id])->update(['is_show'=>0]);
            $info['msg'] = '取消成功';
            $info['status'] = 101;
        }
        return $info;
    }


    public function hetong_read(){
        $id = input('id');
        $j = [
            'title'=>"签订合同详细列表",
            'id'=>$id
        ];

        return view('User/hetong',$j);
    }


    public function hetong_ajax(){
        $id= input('id');
        $cus_data = Db::table('customer c')->where(['user_id'=>$id,'status'=>300])->select();

        foreach($cus_data as $k=>$v){
            $cus_data[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            $cus_data[$k]['update_time'] = date('Y-m-d',$v['update_time']);
            $cus_data[$k]['user_name']   = Db::name('UserInformation')->where(['user_id'=>$v['user_id']])->value('company_name');
            $cus_data[$k]['customer_name']   = Db::name('UserInformation')->where(['user_id'=>$v['customer_id']])->value('company_name');
            switch ($v['status']){
                case 300:
                    $cus_data[$k]['status'] = "合同签订成功";
            }
        }

        return json(['data'=>$cus_data]);
    }


    public function renzheng(){
        $id = input('id');
        $j = [
            'id'=>$id
            ];
        return view('User/renzheng',$j);
    }


    public function a_edit(){
        $level  = input('level');
        $id     = input('id');
        Db::name('User')->where(['id'=>$id])->update(['level'=>$level]);
        $msg['code'] = 200;
        return json($msg);
    }




    public function back_add(){
        $data['user_id']            = input('user_id');
        $data['company_name']       = input('company_name');
        $data['company_address']    = input('company_address');
        $data['tel']                = input('tel');
        $data['qq']                 = input('qq');
        $data['introduce']          = input('introduce');
        $data['company_video']      = input('company_video');
        $data['business']           = input('business');

        if ((request()->file('head_pic')) != NULL) {
            $file = request()->file('head_pic');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            $data['head_pic'] = '/uploads/' . $info->getSaveName();
        }else{
            $data['head_pic'] = Db::name('UserInformation')->where(['user_id'=>$data['user_id']])->value('head_pic');
        }

        if ((request()->file('company_authentication')) != NULL) {
            $files = request()->file('company_authentication');
            $i =0;
            foreach($files as $file){
                $i++;
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if($info){
                    $data['company_authentication'][$i] = '/uploads/' . $info->getSaveName();
                }else{
                    // 上传失败获取错误信息
                    echo $file->getError();
                }
            }
        }else{
            $data['company_authentication'] = explode(',',Db::name('UserInformation')->where(['user_id'=>$data['user_id']])->value('company_authentication'));
            foreach($data['company_authentication'] as $k=>$v){
                $data['company_authentication'][$k] = Db::table('authentication_pic')->where(['id'=>$v])->value('pic_url');
            }
        }

        if(!empty($data['head_pic']) && !empty($data['company_authentication'])){
            $data['head_pic'] = Db::name('head_pic')->where(['id'=>$data['head_pic']])->value('pic_url');

            Db::name('AuthenticationPic')->where(['user_id'=>$data['user_id']])->delete();
            Db::name('HeadPic')->where(['user_id'=>$data['user_id']])->delete();
//            dump($data);die;
            foreach ($data['company_authentication'] as $k=>$v){
                $s = new AuthenticationPic();
                $s->user_id = $data['user_id'];
                $s->pic_url = $v;
                $s->save();
            }

            $h = new HeadPic();
            $h->pic_url = $data['head_pic'];
            $h->user_id = $data['user_id'];
            $h->save();
            $company_auth = Db::name('AuthenticationPic')->where(['user_id'=>$data['user_id']])->field('id')->select();
            $data['head_pic'] = Db::name('HeadPic')->where(['user_id'=>$data['user_id']])->value('id');

            foreach ($company_auth as $k=>$v){
                $id[$k] = $v['id'];
            }
            $data['company_authentication'] = implode(',',$id);

        }else{

            $data['head_pic'] = Db::name('head_pic')->where(['id'=>$data['head_pic']])->value('pic_url');
            $data['company_authentication'] = Db::name('UserInformation')->where(['user_id'=>$data['user_id']])->value('company_authentication');
        }

        $data['business'] = explode(',',$data['business']);
        foreach($data['business'] as $k=>$v){
            $data['business'][$k] = Db::name('category')->where(['c_name'=>$v])->value('category_id');
        }
        $data['business'] = implode(',',$data['business']);

        //保存数据
        $i = Db::name('UserInformation')->where(['user_id'=>$data['user_id']])->update([
            'company_name'      =>$data['company_name'],
            'company_address'   =>$data['company_address'],
            'tel'               =>$data['tel'],
            'qq'                =>$data['qq'],
            'introduce'         =>$data['introduce'],
            'company_video'     =>$data['company_video'],
            'head_pic'          =>$data['head_pic'],
            'business'          =>$data['business'],
            'company_authentication'=>$data['company_authentication'],
        ]);
        if($i){
            echo '<script>alert("修改成功");history.go(-1)</script>';
        }else{
            echo "<script>alert('修改失败');history.go(-1)</script>";
        }
    }

    /**
     * @return \think\response\Json
     * author hongwenyang
     * method description : 删除注册用户
     */

    public function udel(){
        $id = input('id');
        $s = Db::name('user')->where('id',$id)->delete();
        //删除用户填写的企业信息
        $isHave = Db::name('UserInformation')->where('user_id',$id)->find();
        if($isHave){
            Db::name('UserInformation')->where('user_id',$id)->delete();
        }
        if($s){
           $code = 200;
           $msg = "删除成功";
        }else{
            $code = 404;
            $msg = "删除失败";
        }

        $j = [
            'code'=>$code,
            'msg'=>$msg
        ];
        return json($j);
    }
}
