<?php
namespace app\admin\controller;

use app\common\model\Admin;
use app\common\model\News;
use GuzzleHttp\Client;
use QL\QueryList;
use think\Controller;
use think\Db;
use think\Request;
use phpQuery;
class Auser extends Controller
{
    public function Index()
    {
        $info = Db::table('admin')
            ->paginate(10);

        $data = [
            'title'=>"账号管理",
            'info'=>$info
        ];
       return view('Auser/index',$data);
    }

    public function edit(){
        $id = input('id');

        $a_data = Db::name("admin")->where("id",$id)->find();

//        $this->assign("id",$data['id']);
//        $this->assign("admin_name",$data['admin_name']);
//        $this->assign("title","管理员信息编辑");
//        $this->fetch("Auser/admin_edit");
        $data = [
            'id'=>$a_data['id'],
            'admin_name'=>$a_data['admin_name'],
            'title'=>'管理员信息编辑'
        ];
        return view('Auser/admin_edit',$data);
    }

    /**
     * 更新数据
     */

    public function update(){
        if(empty(input('admin_pwd'))){
            Db::name("Admin")->where([
                'id'=>input('id')
            ])->update([
                'admin_name'=>input('admin_name')
            ]);
        }else{
            Db::name("Admin")->where([
                'id'=>input('id')
            ])->update([
                'admin_name'=>input('admin_name'),
                'admin_pwd'=>sha1(input('admin_pwd'))
            ]);
        }
        return $this->success("修改信息成功！您需要重新登录","Login/index",3);
    }

    public function Cando(){
        echo 1;
    }

    public function news(){
        $data = [
            'title'=>'新闻采集'
        ];
        return view("Auser/news",$data);
    }

    public function get_news(){

        $web_site = input('web_site');
        $url1 = input('url');
        $news = new News();
        $pics = [
            '/uploads/pic_new01.png',
            '/uploads/pic_new02.png',
            '/uploads/pic_new03.png',
            '/uploads/pic_new04.png',
            '/uploads/pic_new05.png',
            '/uploads/pic_new06.png',
        ];
        $use_pic_id = rand(0,count($pics)-1);

        if($web_site == 0){
            //中国工程机械租赁网
            $page = $url1;
            //采集规则
            $reg = array(
                //采集文章标题
                'title' => array('h1','text'),
                //采集文章发布日期,这里用到了QueryList的过滤功能，过滤掉span标签和a标签

                //采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
                'content' => array('.content','html')
            );
            $ql = QueryList::Query($page,$reg);
            $data = $ql->getData();

//            var_dump($data[0]['content']);die;
            $news->title    = $data[0]['title'];
            $news->time     = time();
            $news->content  = $data[0]['content'];
            $news->from     = "中国工程机械租赁网";
            $news->img      = $pics[$use_pic_id];
            $news->pv       = 6;

        }else if($web_site == 1){
            //中国工程建设网
            $page = $url1;
            //采集规则
            $reg = array(
                //采集文章标题
                'title' => array('h1','text'),
                //采集文章发布日期,这里用到了QueryList的过滤功能，过滤掉span标签和a标签

                //采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
                'content' => array('#commendetails p','html')
            );
            $ql = QueryList::Query($page,$reg);
            $data = $ql->getData();

            $content = "";
            for($i=1;$i<count($data);$i++){
                $content .= $data[$i]['content'];
            }
            $content = $data[0]['content'].$content;
            $news->title    = $data[0]['title'];
            $news->time     = time();
            $news->content  = $content;
            $news->from     = "中国工程建设网";
            $news->img      = $pics[$use_pic_id];
            $news->pv       = 6;

        }else if($web_site == 2){
            //造价通
            $page = $url1;
            //采集规则
            $reg = array(
                //采集文章标题
                'title' => array('h2','text'),
                //采集文章发布日期,这里用到了QueryList的过滤功能，过滤掉span标签和a标签

                //采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
                'content' => array('.entry-text','html')
            );
            $ql = QueryList::Query($page,$reg);
            $data = $ql->getData();

            $news->title    = $data[0]['title'];
            $news->time     = time();
            $news->content  = $data[0]['content'];
            $news->from     = "造价通";
            $news->img      = $pics[$use_pic_id];
            $news->pv       = 6;


        }else if($web_site == 3){
            //中国工程机械品牌网
            $page = $url1;
            //采集规则
            $reg = array(
                //采集文章标题
                'title' => array('h1','text'),
                //采集文章发布日期,这里用到了QueryList的过滤功能，过滤掉span标签和a标签

                //采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
                'content' => array('.STYLEliwei3:first','html')
            );
            $ql = QueryList::Query($page,$reg);
            $data = $ql->getData();
            $content        = mb_detect_encoding($data[0]['content'], array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
            $match          = mb_convert_encoding($data[0]['content'], 'utf-8', $content);

            $title          = mb_detect_encoding($data[0]['title'], array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
            $t_match        = mb_convert_encoding($data[0]['title'], 'utf-8', $title);


            $news->title    = $t_match;
            $news->time     = time();
            $news->content  = $match;
            $news->from     = "中国工程机械品牌网";
            $news->img      = $pics[$use_pic_id];
            $news->pv       = 6;

        }else if($web_site == 4){
            // 土木工程网
            $page = $url1;
            //采集规则
            $reg = array(
                //采集文章标题
                'title' => array('.m_g_title','text'),
                //采集文章发布日期,这里用到了QueryList的过滤功能，过滤掉span标签和a标签

                //采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
                'content' => array('.m_g_content','html')
            );
            $ql = QueryList::Query($page,$reg);
            $data = $ql->getData();
            $encode = mb_detect_encoding($data[0]['title'], array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
            $data[0]['title'] = mb_convert_encoding($data[0]['title'], 'UTF-8', $encode);

            $encode = mb_detect_encoding($data[0]['content'], array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
            $data[0]['content'] = mb_convert_encoding($data[0]['content'], 'UTF-8', $encode);


            $news->title    = $data[0]['title'];
            $news->time     = time();
            $news->content  = $data[0]['content'];
            $news->from     = "土木工程网";
            $news->img      = $pics[$use_pic_id];
            $news->pv       = 6;
        }
        $s = $news->save();
        if($s){
            $status = 200;
        }else{
            $status = 100;
        }
        return json_encode($status);
    }

    public function test(){
        $page = 'http://www.ccm-1.com/2017/0428/79569.html';
        //采集规则
        $reg = array(
            //采集文章标题
            'title' => array('h1','text'),
            //采集文章发布日期,这里用到了QueryList的过滤功能，过滤掉span标签和a标签

            //采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
            'content' => array('.STYLEliwei3:first','text')
        );
        $ql = QueryList::Query($page,$reg);
        $data = $ql->getData();
        print_r($data);
    }


    public function get1(){
        $data = file_get_contents('http://civil.gyl.sunday.so/admin/auser/test');
        var_dump($data);
    }
}
