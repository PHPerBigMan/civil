<?php
namespace app\admin\controller;

use think\Db;
use app\common\model\Category;
use app\common\model\User;
use app\admin\controller\Base;

class Notice extends Base
{
    /**
     * 企业公告
     */
    public function user_notice()
    {

        $info = Db::table('notice')
            ->join('user','notice.user_id = user.id')
            ->field('user.login_cell,notice.id,notice.notice_content,notice.user_id,category_id,create_time')
            ->order('create_time desc')
            ->select();

        foreach($info as $k=>$v){
            $data[$k]['user_name']      = empty(Db::name('UserInformation')->where("user_id",$v['user_id'])->value('company_name')) ? Db::name('User')->where("id",$v['user_id']):
                Db::name('UserInformation')->where("user_id",$v['user_id'])->value('company_name');
            $data[$k]['id']             = $v['id'];
            $data[$k]['notice_content'] = $v['notice_content'];
            $data[$k]['user_id']        = $v['user_id'];
            $data[$k]['c_name']         = Db::name('Category')->where(['category_id'=>$v['category_id']])->value('c_name');
            $data[$k]['create_time']    = date('Y-m-d H:i:s',$v['create_time']);
        }
//        echo "<pre>";
//        var_dump($data);die;
        $data = [
            'notice'=>$info,
            'data'=>$data,
            'title'=>'企业公告'
        ];
        return view('Notice/index',$data);
    }


    public function user_notice_ajax(){
        $info = Db::table('notice')
            ->join('user','notice.user_id = user.id')
            ->field('user.login_cell,notice.id,notice.notice_content,notice.user_id,category_id,create_time')
            ->order('create_time desc')
            ->select();

        foreach($info as $k=>$v){
            $data[$k]['user_name']      = empty(Db::name('UserInformation')->where("user_id",$v['user_id'])->value('company_name')) ? Db::name('User')->where("id",$v['user_id']):
                Db::name('UserInformation')->where("user_id",$v['user_id'])->value('company_name');
            $data[$k]['id']             = $v['id'];
            $data[$k]['notice_content'] = mb_substr($v['notice_content'],0,33);
            $data[$k]['user_id']        = $v['user_id'];
            $data[$k]['c_name']         = Db::name('Category')->where(['category_id'=>$v['category_id']])->value('c_name');
            $data[$k]['create_time']    = date('Y-m-d H:i:s',$v['create_time']);
            $user_id = $v['user_id'];
            $id      = $v['id'];
            $data[$k]['caozuo']         = "<button class=\"btn btn-success\" onclick=\"more($user_id)\">查看企业信息</button>
                    <button class=\"btn btn-info\" onclick=\"get($id)\">查看公告信息</button><button class=\"btn btn-danger\" style='margin-left: 5px' onclick=\"del($id)\">删除公告</button>";
        }

        return json(['data'=>$data]);
    }


    public function read()
    {
        $id = input('id');
        $info = Db::table('notice n')
            ->join('category c','n.category_id = c.category_id')
            ->where('n.id',$id)
            ->field('n.*,c.c_name')
            ->find();
        $notice_url_id = explode(',',$info['notice_url']);
        foreach($notice_url_id as $k=>$v){
            $notice_url[$k] = Db::name('notice_pic')->where(['id'=>$v])->value('notice_url');
        }
        $info['notice_url'] = $notice_url;

        //获取id下公告的数据
        $stop = Db::name('Notice')->where(['id'=>$id])->value('stop_time');
        if($stop < time()){
            Db::name('Notice')->where(['id'=>$id])->update([
                'is_top'=>0
            ]);
        }

//        var_dump($info);die;
        $data = [
            'title'=>'公告详情',
            'info'=>$info,
        ];
        return view('Notice/read',$data);
    }


    public function update_top ()
    {
        $id                     = input('id');
        $is_top                 = input('is_top');
        $start_time             = input('start_time');
        $stop_time              = input('stop_time');
         Db::table('notice')
            ->where('id', $id)
            ->update(['is_top' => $is_top]);
        if ($is_top) {
            Db::table('notice')
                ->where('id', $id)
                ->update([
                    'start_time' => strtotime($start_time),
                    'stop_time' => strtotime($stop_time)
                ]);
        }

        return redirect(url('Notice/read').'?id='.$id);
    }


    /**
     * 网站公告
     */
    public function web_notice()
    {
        $info = Db::table('web_notice')->paginate(10);
        $data = [
            'title'=>'网站公告',
            'info' => $info
        ];
        return view('Notice/web_index',$data);
    }


    public function web_notice_add ()
    {
        $this->assign('title', '新增公告');
        return $this->fetch('Notice/web_notice_add');
    }


    public function web_notice_insert ()
    {
        $title  = input('title');
        $data = [
            'title'=>$title,
            'create_time' => time(),
            'content' => input('content')
        ];
        Db::table('web_notice')->insert($data);
        echo '<script>
            parent.$("#handle_status").val("1");
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);</script>';
    }


    public function web_notice_del ()
    {
        $status = Db::table('web_notice')->where('id',input('id'))->delete();
        if ($status) {
            echo 1;
        } else {
            echo 2;
        }
    }


    public function web_notice_edit ()
    {
        $notice = Db::table('web_notice')->where('id', input('id'))->find();
//        var_dump($notice);die;
        $this->assign('notice', $notice);
        $this->assign('title', '修改公告');
        return $this->fetch('Notice/web_notice_edit');
    }


    public function web_notice_update ()
    {
        $data = [];

        $data['content'] = input('content');
        $data['title']   = input('title');
        Db::table('web_notice')->where('id', input('id'))->update($data);
        echo '<script>
            parent.$("#handle_status").val("1");
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);</script>';
    }


    /**
     * @return \think\response\Json
     * 删除企业公告
     */

    public function del_u_notice(){
        $id = input('id');
        $s = Db::name('notice')->where(['id'=>$id])->delete();
        if($s){
            $msg['code'] = 200;
            $msg['msg']  = "删除成功";
        }else{
            $msg['code'] = 404;
            $msg['msg']  = "删除失败";
        }

        return json($msg);
    }
}
