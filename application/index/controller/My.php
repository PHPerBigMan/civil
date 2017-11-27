<?php

namespace app\index\controller;
header('Content-type: text/json');
//header("Access-Control-Allow-Origin: *");
use app\common\model\AuthenticationPic;
use app\common\model\Category;
use app\common\model\Customer;
use app\common\model\Evaluate;
use app\common\model\EvaluateUser;
use app\common\model\Notice;
use app\common\model\NoticePic;
use app\common\model\User;
use app\common\model\UserCategory;
use app\common\model\UserInformation;
use Phinx\Config;
use think\Controller;
use think\Db;
use think\model\Collection;
use think\Session;

class My extends Controller
{

    public function index()
    {

    }

    /**
     * 发出的公告
     *
     */

    public function notice()
    {
        $id = input('user_id');
        $Ndata = Notice::where("user_id in ($id)")->order('create_time desc')->select();

        $page = input('page');
        if (empty($page)) {
            if (empty($Ndata)) {
                $data = array();
            } else {

                foreach ($Ndata as $k => $v) {
                    $data[$k]['content'] = $v->notice_content;
                    $data[$k]['create_time'] = $v->create_time;
                    $data[$k]['id'] = $v->id;
                    $notice_url[$k] = empty($v->notice_url) ? array() : explode(',', $v->notice_url);
                    if (empty($notice_url)) {
                        $data[$k]['notice_url'] = array();
                    } else {
                        $a = $notice_url;
                        foreach ($notice_url as $k1 => $v1) {
                           foreach($v1 as $k2=>$v2){
                               $data[$k]['notice_url'][$k2] = Db::name('notice_pic')->where(['id'=>$v2])->value('notice_url');
                           }
                        }
                    }
                    $data[$k]['notice_video'] = $v->notice_video;
                    $data[$k]['category_id'] = Db::name("Category")->where(['category_id' => $v->category_id])->value("category_id");
                    $data[$k]['user_id'] = Db::name("UserInformation")->where(['user_id' => $id])->value("user_id");
                    $data[$k]['user_name'] = Db::name("UserInformation")->where(['user_id' => $id])->value("company_name");
                }
//                var_dump($data);die;
            }

            $j = [
                'data' => $data,
            ];
        } else {
            $n_count = Db::name("Notice")->where(["user_id" => $id])->count();
            //页码
            $n_num = ceil($n_count / 10);
            if ($page <= 1) {
                if ($n_count < 10) {
                    $n_count = $n_count;
                    $n_data = Db::name("Notice")->where(["user_id" => $id])->limit(0, $n_count)->order('create_time desc')->select();
                    if (empty($n_data)) {
                        $data = array();
                    }
                } else {
                    $n_data = Db::name("Notice")->where(["user_id" => $id])->limit(0, 10)->order('create_time desc')->select();
                }
            } else if ($page > 1 && $page <= ($n_num - 1) && $n_num > 1) {
                $n_data = Db::name("Notice")->where(["user_id" => $id])->limit(($page - 1) * 10, 10)->order('create_time desc')->select();
            } else if ($page == $n_num) {
                $n_data = Db::name("Notice")->where(["user_id" => $id])->limit($n_count - ($n_num - 1) * 10)->order('create_time desc')->select();
            }

            foreach ($n_data as $k => $v) {
                $data[$k]['content'] = $v['notice_content'];
                $data[$k]['create_time'] = date("Y-m-d", $v['create_time']);
                $data[$k]['id'] = $v['id'];
                $data[$k]['notice_video'] = $v['notice_video'];
                $data[$k]['category_id'] = Db::name("Category")->where(['category_id' => $v['category_id']])->value("category_id");
                $data[$k]['user_id'] = Db::name("UserInformation")->where(['user_id' => $id])->value("user_id");
                $data[$k]['user_name'] = empty(Db::name('UserInformation')->where("user_id",$id)->value('company_name')) ? Db::name('User')->where("id",$id)->value('login_cell'):
                Db::name('UserInformation')->where("user_id",$id)->value('company_name');

                $notice_url[$k] = empty($v['notice_url']) ? array() : explode(',', $v['notice_url']);
                foreach ($notice_url as $k => $v) {
                    foreach ($v as $k1 => $v1) {
                        if (!empty($v1)) {
                            $data[$k]['notice_url'][$k1] = Db::name("notice_pic")->where(['id' => $v1])->value("notice_url");
                        }
                    }
                }
            }
//            echo "<pre />";
//            var_dump($data);die;
            $j = [
                'count' => $n_count,
                'data' => $data
            ];
        }
        return json_encode($j);
    }

    /**
     * @return object
     * 发出的评价
     */

    public function post()
    {
        $page = input('page');
        $id = input('user_id');
        if (empty($page)) {
            $evaluate_id = Db::name('Evaluate')->where("evaluator_id in ($id)")->select();
            if (empty($evaluate_id)) {
                $data = array();
            } else {
                foreach ($evaluate_id as $k => $v) {
                    $data[$k]['create_time'] = date("Y-m-d",$v['create_time']);
                    $data[$k]['evaluate_content'] = $v['evaluate_content'];
                    $valuator_id[$k] = $v['valuator_id'];
                    $data[$k]['company_name'] = Db::name('UserInformation')->where(['user_id' => $valuator_id[$k]])->value('company_name');
                    $data[$k]['id'] = $v['id'];
                    $data[$k]['valuator'] = $v['valuator_id'];
                }
            }

            return json_encode($data);
        } else {
            $n_count = Db::name("Evaluate")->where(["evaluator_id" => $id])->count();
            //页码
            $n_num = ceil($n_count / 10);
            if ($page <= 1) {
                if ($n_count < 10) {
                    $n_count = $n_count;
                    $n_data = Db::name("Evaluate")->where(["evaluator_id" => $id])->limit(0, $n_count)->select();
                    if (empty($n_data)) {
                        $data = array();
                    }
                } else {
                    $n_data = Db::name("Evaluate")->where(["evaluator_id" => $id])->limit(0, 10)->select();
                }
            } else if ($page > 1 && $page <= ($n_num - 1) && $n_num > 1) {
                $n_data = Db::name("Evaluate")->where(["evaluator_id" => $id])->limit(($page - 1) * 10, 10)->select();
            } else if ($page == $n_num) {
                $n_data = Db::name("Evaluate")->where(["evaluator_id" => $id])->limit($n_count - ($n_num - 1) * 10)->select();
            }

            foreach ($n_data as $k => $v) {
                $data[$k]['create_time'] = date("Y-m-d",$v['create_time']);
                $data[$k]['evaluate_content'] = $v['evaluate_content'];
                $v_id = $v['valuator_id'];
                $data[$k]['company_name'] = Db::name('UserInformation')->where(['user_id' => $v_id])->value('company_name');
                $data[$k]['id'] = $v['id'];
                $data[$k]['valuator_id'] = $v['valuator_id'];
            }
            $j = [
                'count' => $n_count,
                'data' => $data
            ];
            return json_encode($j);
        }
    }

    /**
     * @return string
     * 删除发出的评价
     */

    public function del_eva()
    {
        $id = input('id');
        $c = Evaluate::destroy($id);
        if ($c) {
            $status = 200;
        } else {
            $status = 100;
        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 修改发出的评价
     */

    public function update_eva()
    {
        $id = input('id');
        $eva_content = input('content');
        $e = new Evaluate();
        $s = $e->where(['id' => $id])->update(['evaluate_content' => $eva_content]);
        if ($s) {
            $status = 200;
        } else {
            $status = 100;
        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }

    /**
     * @return object
     * 收到的评价
     */

    public function get()
    {
        $page = input('page');
        $id = input('user_id');
        if (empty($page)) {
            $evaluate_id = Db::name('Evaluate')->where("valuator_id", $id)->select();

            if (empty($evaluate_id)) {
                $data = array();
            } else {
                foreach ($evaluate_id as $k => $v) {
                    $v_id           = $v['valuator_id'];
                    $data[$k]['company_name'] = Db::name('UserInformation')->where(['user_id' => $v_id])->value('company_name');
                    $data[$k]['create_time'] = date("Y-m-d",$v['create_time']);
                    $data[$k]['evaluate_content'] = $v['evaluate_content'];
                }
            }

            return json_encode($data);
        } else {
            $n_count = Db::name("Evaluate")->where(["valuator_id" => $id])->count();
            //页码
            $n_num = ceil($n_count / 10);
            if ($page <= 1) {
                if ($n_count < 10) {
                    $n_count = $n_count;
                    $n_data = Db::name("Evaluate")->where(["valuator_id" => $id])->limit(0, $n_count)->select();
                    if (empty($n_data)) {
                        $data = array();
                    }
                } else {
                    $n_data = Db::name("Evaluate")->where(["valuator_id" => $id])->limit(0, 10)->select();
                }
            } else if ($page > 1 && $page <= ($n_num - 1) && $n_num > 1) {
                $n_data = Db::name("Evaluate")->where(["valuator_id" => $id])->limit(($page - 1) * 10, 10)->select();
            } else if ($page == $n_num) {
                $n_data = Db::name("Evaluate")->where(["valuator_id" => $id])->limit($n_count - ($n_num - 1) * 10)->select();
            }

            foreach ($n_data as $k => $v) {
                $v_id = $v['evaluator_id'];
                $data[$k]['company_name'] = empty(Db::name('UserInformation')->where(['user_id' => $v_id])->value('company_name'))?
                Db::name("User")->where(['id'=>$v_id])->value('login_cell') : Db::name('UserInformation')->where(['user_id' => $v_id])->value('company_name');
                $data[$k]['create_time'] = date("Y-m-d",$v['create_time']);
                $data[$k]['evaluate_content'] = $v['evaluate_content'];
            }
            $j = [
                'count' => $n_count,
                'data' => $data
            ];
            return json_encode($j);
        }
    }

    /**
     * @return object
     * 我的收藏
     */

    public function collection()
    {
        $id = input('user_id');
        $page = input('page');
        if (empty($page)) {
            $collection = Db::name("Collection")->where("collection_user_id", $id)->select();

            if (empty($collection)) {
                $data = array();
            } else {

                foreach ($collection as $k => $v) {
                    $data[$k]['id'] = $v['collectioned_user_id'];
                    $data[$k]['create_time'] = date("Y-m-d H:i:s", $v['create_time']);
                    $data_id[$k] = $v['collectioned_user_id'];
                    foreach ($data_id as $k1 => $v1) {
                        $data[$k]['company_name'] = Db::name("UserInformation")->where(['user_id' => $data_id[$k]])->value('company_name');
                    }
                }
            }
            return json_encode($data);
        } else {
            $n_count = Db::name("Collection")->where(["collection_user_id" => $id])->count();
            //页码
            $n_num = ceil($n_count / 10);
            if ($page <= 1) {
                if ($n_count < 10) {
                    $n_count = $n_count;
                    $n_data = Db::name("Collection")->where(["collection_user_id" => $id])->limit(0, $n_count)->select();
                    if (empty($n_data)) {
                        $data = array();
                    }
                } else {
                    $n_data = Db::name("Collection")->where(["collection_user_id" => $id])->limit(0, 10)->select();
                }
            } else if ($page > 1 && $page <= ($n_num - 1) && $n_num > 1) {
                $n_data = Db::name("Collection")->where(["collection_user_id" => $id])->limit(($page - 1) * 10, 10)->select();
            } else if ($page == $n_num) {
                $n_data = Db::name("Collection")->where(["collection_user_id" => $id])->limit($n_count - ($n_num - 1) * 10)->select();
            }

            foreach ($n_data as $k => $v) {
                $data[$k]['company_name'] = Db::name("UserInformation")->where(['user_id' => $v['collectioned_user_id']])->value("company_name");
                $data[$k]['id'] = $v['collectioned_user_id'];
                $data[$k]['create_time'] = date("Y-m-d", $v['create_time']);
            }
            $j = [
                'count' => $n_count,
                'data' => $data
            ];
            return json_encode($j);
        }

    }

    /**
     * @return object
     * 我的客户
     * 移动端使用
     */

    public function customer()
    {
        $id = input('user_id');
        $customer_id = Db::name("Customer")->where(['user_id' => $id])->select();
        if (empty($customer_id)) {
            $data = array();
        } else {

            foreach ($customer_id as $k => $v) {
                $user_id[] = $v['customer_id'];
                $customer[$k]['contract_name'] = $v['contract_name'];
                $customer[$k]['status'] = $v['status'];
                $customer[$k]['c_status'] = $v['c_status'];
                $customer[$k]['create_time'] = $v['create_time'];
                $customer_id[$k] = $v['customer_id'];
                foreach ($customer_id as $k1 => $v1) {
                    $customer[$k]['company_name'] = Db::name("UserInformation")->where(['user_id' => $customer_id[$k]])->value('company_name');
                }
            }
        }
//            var_dump($customer);
//        die;
        return json_encode($customer);
    }

    /**
     * @return object
     * 我的个人资料
     */

    public function myinfo()
    {
        $id = input('user_id');
        $user_data = UserInformation::where(['user_id' => $id])->select();
        //if说明,发布公告  如果用户在点击保存之前刷新页面，需要清空对应的session，保证数据的正确
        if(!empty(session('notice_db_save_img_url'))){
            session('notice_db_save_img_url',null);
            session('notice_save_id_new',null);
        }
        if (input('pc') == "pc") {
            //已经使用 一起写接口的次数
            $num = Db::name('User')->where(['id'=>$id])->find();
            if (empty($user_data)) {
                $data['head_pic'] = array();
                $data['company_video'] = '';
                $data['company_name'] = '';
                $data['company_address'] = '';
                $data['company_authentication'] = array();
                $data['tel'] = '';
                $data['qq'] = '';
                $data['introduce'] = '';
                $data['company_address'] = "";
                $data['company_address_val'] = array();
                $data['user_name'] = '';
                $data['time'] = '';
                $data['times'] = '';
                $data['name'] = '';
                $data['category'] = array();
//                var_dump($category_none);die;
            } else {
                $c = new Category();
                foreach ($user_data as $k => $v) {
                    $head_pic = empty($v->head_pic) ? "" : $v->head_pic;
                    if (empty($head_pic)) {
                        $data['head_pic'] = "";
                    } else {
                        $data['head_pic'][] = Db::name("head_pic")->where(['id' => $head_pic])->value('pic_url');
                    }
                    $data['company_video'] = $v->company_video;
                    $data['company_name'] = $v->company_name;
                    $data['company_address'] = $v->company_address;
                    $data['company_address_val'] = empty($v->company_address_val) ? array() : json_decode($v->company_address_val);
//                    $data['company_authentication']         = empty($v->company_authentication) ? array() : (array)$v->company_authentication;
                    $data['tel'] = $v->tel;
                    $data['qq'] = $v->qq;
                    $data['introduce'] = $v->introduce;
                    $data['name'] = $v->name;
                    $data['user_name'] = empty(Db::name("User")->where(["id" => $v->user_id])->value("login_cell")) ? "" : Db::name("User")->where(["id" => $v->user_id])->value("login_cell");
                    $authen_id = empty($v->company_authentication) ? "" : explode(',', $v->company_authentication);
                    if (empty($authen_id)) {
                        $data['company_authentication'] = array();
                    } else {
                        foreach ($authen_id as $k => $v) {
                            $data['company_authentication'][$k] = Db::name('authentication_pic')->where([
                                'id' => $v
                            ])->value('pic_url');
                        }
                    }
                    $data['times'] = Db::name('User')->where(['id'=>$id])->value('evaluate_time');
                }
                $category_id = Db::name("UserCategory")->field("category_id")->where([
                    'user_id' => $id
                ])->select();
//                var_dump($category_id);die;
                if (empty($category_id)) {
                    $data['category'] = array();
                } else {
                    foreach ($category_id as $k => $v) {
                        $category_c[$k] = Db::name("Category")->where([
                            'category_id' => $v['category_id']
                        ])->select();
                    }

                    $category_none = [];
                    if (empty($category_c)) {
                        $data['category'] = array();
                    } else {

                        foreach ($category_c as $k => $v) {
                            if (!empty($v[0])) {
                                if ($v[0]['level'] == 1) {
                                    $category_none[$k] = $v[0];
                                } else if ($v[0]['level'] == 2) {
                                    $first = Db::name("Category")->field("pid,c_name,category_id,level")->where([
                                        'category_id' => $v[0]['pid']
                                    ])->select();
                                    $category_none[$k] = $first[0];
                                    $category_none[$k]['child'] = $v[0];
                                } else if ($v[0]['level'] == 3) {
                                    $second = Db::name("Category")->field("pid,c_name,category_id,level")->where([
                                        'category_id' => $v[0]['pid']
                                    ])->select();

                                    $first_c = Db::name("Category")->field("pid,c_name,category_id,level")->where([
                                        'category_id' => $second[0]['pid']
                                    ])->select();
                                    $category_none[$k] = $first_c[0];
                                    $category_none[$k]['child'] = $second[0];
                                    $category_none[$k]['child']['child'] = $v[0];
                                }
                            }
                        }
                    }

                    $data['category'] = [];
                    foreach ($category_none as $v) {
                        $data['category'][] = $v;
                    }
                }
            }
            switch ($num['level']){
                case 0:
                    $time = 10;
                    break;
                case 1:
                    $time = 30;
                    break;
                case 2:
                    $time = 50;
                    break;
                default:
                    $time = 10;
                    break;
            }
            //剩余还能使用次数
            $data['yqxNumber'] = $time - $num['write_time'];
            //已经使用次数
            $data['yqxUsed'] = (int)$num['write_time'];
        } else {
            if (empty($user_data)) {
                $data['company_url'] = '';
                $data['company_video'] = '';
                $data['company_name'] = '';
                $data['company_address'] = '';
                $data['company_authentication'] = '';
                $data['tel'] = Db::name("User")->where(['id' => input('user_id')])->value('login_cell');
                $data['qq'] = '';
                $data['introduce'] = '';
                $data['company_address'] = "";
                $data['user_name'] = '';
                $data['time'] = '';
                $c_name = Category::select();
                foreach ($c_name as $k => $v) {
                    $data[$k]['category_id'] = $v->category_id;
                    $data[$k]['c_name'] = $v->c_name;
                }
            } else {
                foreach ($user_data as $k => $v) {
                    $c_id = $v->business;
                    $data['company_url'] = $v->company_url;
                    $data['company_video'] = $v->company_video;
                    $data['company_name'] = $v->company_name;
                    $data['company_address'] = $v->company_address;
                    $data['company_authentication'] = $v->company_authentication;
                    $data['tel'] = Db::name("User")->where(['id' => input('user_id')])->value('login_cell');
//                      $data['tel'] = 11111;

                    $data['qq'] = $v->qq;
                    $data['introduce'] = $v->introduce;
                }
                $cat = [];
                if (empty($c_id)) {
                    $c_name = Category::select();
                    foreach ($c_name as $k => $v) {
                        $cat[] = $v->c_name;
                    }
                } else {
                    $c_name = Category::where("category_id in ($c_id)")->select();
                    foreach ($c_name as $k => $v) {
                        $cat[] = $v->c_name;
                    }
                }

                $data['category'] = implode(',',$cat);
                $user = User::where(['id' => $id])->select();
                foreach ($user as $k => $v) {
                    $data['user_name'] = $v->login_cell;
                    $data['time'] = $v->evaluate_time;
                }
            }
        }
        return json_encode($data);
    }


    /**
     * @return object
     * 保存公告  修改公告
     */

    public function send_notice()
    {

        $satus = new  Notice;
        $cancel = input('cancel');
        //获取的公告数据
        $data = input('annou_data/a');
            //公告图片
            if ((request()->file('notice_url')) != NULL) {
                $notice_url = request()->file('notice_url');
//                $info = $notice_url->move(ROOT_PATH . 'public' . DS . 'uploads');
//                $notice_name = '/uploads/' . $info->getSaveName();
                $notice_name = CompressImg($notice_url);
                $save = $satus->save_img($notice_name, input('user_id'));

                if ($save['status'] == 200) {
                    if (empty(session('notice_save_id_new'))) {
                        session('notice_save_id_new', $save['content_url_id']);
                        $notice_id = session('notice_save_id_new');
                    } else {
                        $notice_id = session('notice_save_id_new');
                        $notice_id = $notice_id . "," . $save['content_url_id'];
                        session('notice_save_id_new', $notice_id);
                    }
                    $status = 200;
                } else {
                    $status['status'] = 100;
                }
                session('notice_db_save_img_url', $notice_id);
            }
            $now = time();
            $beginTime = date('Y-m-d 00:00:00', mktime(0, 0,0, 1, 1, date('Y', $now)));
            $endTime = date('Y-m-d 23:39:59', mktime(0, 0, 0, 12, 31, date('Y', $now)));
        if((empty($data['id'])) ){

            if(empty(input('user_id'))){
                $user_id = $data['user_id'];
            }else{
                $user_id = input('user_id');
            }
                $content_time = Db::name('User')->field("notice_time,notice_use_time,level")->where('id',$user_id)->find();

                if(($content_time['notice_time'] >0)){

                    if($cancel != NULL && $cancel !=1){

                        //如果 发送公告的有效起始时间 小于当前年份1.1日的时间戳，那么评价次数刷新
                        if($beginTime>=$content_time['notice_use_time']){

//                            var_dump($content_time);die;
                            if($content_time['level'] == 0){
                                $con_time = 6;
                            }else if($content_time['level'] == 1){
                                $con_time = 20;
                            }else if($content_time['level'] == 2){
                                $con_time = 48;
                            }else{
                                $con_time = 20;
                            }


                            Db::name('User')->where("id",$data['user_id'])->update([
                                'notice_time'=>$con_time,
                                'notice_use_time'=>time()
                            ]);
                        }

                        $satus->user_name = empty(Db::name('UserInformation')->where("user_id",$data['user_id'])->value('company_name')) ? Db::name('User')->where("id",$data['user_id'])->value('login_cell'):
                            Db::name('UserInformation')->where("user_id",$data['user_id'])->value('company_name');
                        $satus->notice_url = empty(session('notice_db_save_img_url')) ? "" : session('notice_db_save_img_url');
                        $satus->notice_video = empty($data['notice_video']) ? '' : $data['notice_video'];
                        $satus->category_id = empty($data['category_id']) ? '' : $data['category_id'];
                        $satus->user_id = $data['user_id'];
                        $satus->is_on = 0;
                        $satus->is_top = 0;
                        $satus->notice_content = empty($data['content']) ? '' : $data['content'];
                        $satus->create_time = time();
                        $s = $satus->save();
                        if ($s) {
                            $status = 200;
                            $msg   = "发送成功";
                            session('notice_db_save_img_url', null);
                            session('notice_save_id_new', null);
                            //成功减少一次机会
                            Db::name('User')->where('id',$data['user_id'])->setDec('notice_time');
                            //已发送公告的次数增加1
                            Db::name('User')->where('id',$data['user_id'])->setInc('noticed_time');
                        } else {
                            $status = 100;
                            $msg = "发送失败,请检查！";
                            session('notice_db_save_img_url', null);
                            session('notice_save_id_new', null);
                        }
                    }else if((int)$cancel == 1){
                        session('notice_db_save_img_url', null);
                        session('notice_save_id_new', null);
                        $status = 200;
                        $msg    = "取消成功";
                    }else{
                        $status = 200;
                        $msg    = "成功";
                    }

                }else{
                    $status = 300;
                    $msg = "发送公告次数不足";
                }
            }else{
                $notice_content = empty($data['content']) ? '' : $data['content'];
                $notcie_pic_edit = Db::name("notice")->where(['id' => $data['id']])->value('notice_url');
                if(!empty(session('notice_db_save_img_url'))){

                    if (!empty($notcie_pic_edit)) {
                        $notice_pic_db = $notcie_pic_edit . "," . session('notice_db_save_img_url');
                        $status = 200;
                    } else {
                        $notice_pic_db = session('notice_db_save_img_url');
                        $status = 200;
                    }
                } else {
                    $notice_name = $notcie_pic_edit;
                }

                if($cancel != NULL && $cancel !=1){
                    $notice_url = empty($notice_pic_db)? $notice_name :$notice_pic_db;

                    $notice_video = empty($data['notice_video']) ? '' : $data['notice_video'];
                    $category_id = empty($data['category_id']) ? '' : $data['category_id'];
                    $user_id = $data['user_id'];
                    $is_on = 0;
                    $is_top = 0;
                    $s = $satus->where(['id' => $data['id']])->update(array(
                        'notice_content' => $notice_content,
                        'notice_url' => $notice_url,
                        'notice_video' => $notice_video,
                        'category_id' => $category_id,
                        'user_id' => $user_id,
                        'is_on' => $is_on,
                        'is_top' => $is_top,
                        'edit_time' => time()
                    ));
                    if ($s) {
                        $status = 200;
                        $msg  = '修改成功';
                        session('notice_db_save_img_url', null);
                        session('notice_save_id_new', null);
                        session('edit_db_save_notice_pic_id', null);
                    } else {
                        $status = 100;
                        $msg = "修改失败,请检查！";
                    }
                }else if($cancel == 1){
                    session('notice_db_save_img_url', null);
                    session('notice_save_id_new', null);
                    session('edit_db_save_notice_pic_id', null);
                    $status = 200;
                    $msg  = "取消成功";
                }

            }
        $j = [
            'status' => $status,
            'msg'    =>$msg
        ];
        return json_encode($j);
    }

    /**
     * 删除公告
     */
    public function del_notice()
    {
        $id = input('id');
        $s = Notice::destroy($id);
        if ($s) {
            $status = 200;
        } else {
            $status = 100;
        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }

    /**
     *  保存用户资料   这部分还是要修改，主要是修改分类保存的地点
     *  个人中心保存说明：  由于当时建表的原因，个人中心的数据更新和新增需要在两个数表中操作：UserInformation和UserCategory 主要是分类存储的时候需要将
     *  分类id存储在UserCategory中
     */

    public function mydata()
    {
        $u = new UserInformation();
        $user_data =  input('user_data/a');
        $u_data_check = $u->where("user_id", $user_data['user_id'])->select();
        $img_data_check = $u->where("user_id", input('user_id'))->select();
        $img = input('img');
        $cancel = input('cancel');

        //处理用户地区经纬度
        if(!empty($user_data['company_address'])){

            $url = 'http://restapi.amap.com/v3/geocode/geo?address='.$user_data['company_address'].'&output=json&key=68e535a816c751afc9ae25a975cf8459';

            $string = file_get_contents($url);

                $string = json_decode($string);
            if($string->status == 1){
                $jw = explode(',',$string->geocodes[0]->location);

                $user_data['avl'] = $jw[0];
                $user_data['evl'] = $jw[1];
            }
        }
        if (empty($u_data_check) && empty($img_data_check)) {

            $u->company_video = empty($user_data['company_video']) ? '' : $user_data['company_video'];

            //企业头图
            if($cancel == ""){

                if ((request()->file('head_pic')) != NULL) {
//                    echo 1;
                    $file = request()->file('head_pic');
                    // 移动到框架应用根目录/public/uploads/ 目录下
//                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
//                    $imgs = '/uploads/' . $info->getSaveName();
                    $head_pic_name = CompressImg($file);
                } else {
                    $head_pic_name = '';
                }
//                die;
                //认证图片
                if ((request()->file('authentication')) != NULL) {
                    $a_file = request()->file('authentication');
//                    $info = $a_file->move(ROOT_PATH . 'public' . DS . 'uploads');
//
//                    $company_imgs = '/uploads/' . $info->getSaveName();
                    $company_authentication_name = CompressImg($a_file);

                } else {
                    $company_authentication_name = "";
                }
                $a_pic = new Notice();

                //保存公司认证图片
                $save = $a_pic->auth_pic($company_authentication_name, input('user_id'));

                if ($save['status'] == 200) {
                    $company_authentication_url_id = empty($save['company_authentication_url_id']) ? "" : $save['company_authentication_url_id'];
                    if (empty(session('authentication_save_id_new'))) {
                        session('authentication_save_id_new', $company_authentication_url_id);
                        $authentication_id = session('authentication_save_id_new');
                    } else {
                        $authentication_id = session('authentication_save_id_new');
                        $authentication_id = $authentication_id . "," . $company_authentication_url_id;
                        session('authentication_save_id_new', $authentication_id);
                    }
                    $status = 200;
                    session('authentication_db_save_img_url', $authentication_id);
                } else {
                    $status = 100;
                    session('authentication_db_save_img_url', "");
                }


                if ((session('head_db_save_id_new')) == "") {

                    //保存企业头图
                    $head_save = $a_pic->head_pic($head_pic_name, input('user_id'));

                    if ($head_save['status'] == 200) {
                        session('head_db_save_id_new', $head_save['head_url_id']);
                    } else {
                        $status = 100;
                        session('head_db_save_id_new', "");
                    }
                }
            }else if($cancel == 0){
                    $u->company_authentication = empty(session('authentication_db_save_img_url')) ? "" : session('authentication_db_save_img_url');
                    $u->head_pic = empty(session('head_db_save_id_new')) ? "" : session('head_db_save_id_new');
                    $u->name = empty($user_data['name']) ? "" : $user_data['name'];
                    $u->company_name = empty($user_data['company_name']) ? '' : $user_data['company_name'];
                    $u->company_address = empty($user_data['company_address']) ? '' : $user_data['company_address'];
                    $u->company_address_val = empty($user_data['company_address_val']) ? '' : json_encode($user_data['company_address_val']);
                    $u->tel = empty($user_data['tel']) ? '' : $user_data['tel'];
                    $u->qq = empty($user_data['qq']) ? '' : $user_data['qq'];
                    $u->business = empty($user_data['category']) ? '' : implode(",", $user_data['category']);
                    $u->introduce = empty($user_data['introduce']) ? '' : $user_data['introduce'];
                    $u->user_id = $user_data['user_id'];
                    $u->create_time = time();
                    $u->avl = $user_data['avl'];
                    $u->evl = $user_data['evl'];
                    $u->is_top = 0;
                    $s = $u->save();
                    session('authentication_save_id_new', null);
                    session('authentication_db_save_img_url', null);
                    session('head_save_id_new', null);
                    session('head_db_save_id_new', null);
                    session('intro_img',NULL);

                    //更改用户认证状态  等待验证
                    Db::name('User')->where(['id'=>$user_data['user_id']])->update(['level'=>4]);

                    if (!empty(($user_data['category']))) {
                        $cat = new UserCategory();
                        $category = $user_data['category'];
                        foreach ($category as $k => $v) {
                            $cat->user_id = $user_data['user_id'];
                            $cat->category_id = $v;
                            $cat->save();
                        }
                    }
                    if ($s) {
                        $status = 200;
                    } else {
                        $status = 100;
                    }
                }else if($cancel == 1){
                    session('authentication_save_id_new', null);
                    session('authentication_db_save_img_url', null);
                    session('head_save_id_new', null);
                    session('head_db_save_id_new', null);
                    session('intro_img',NULL);
                }
        } else {

            $company_url = "";
            $a_pic = new Notice();
            //企业头图
            if ((request()->file('head_pic')) != NULL) {
                $file = request()->file('head_pic');
                // 移动到框架应用根目录/public/uploads/ 目录下
//                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
//                $head_pic_name = '/uploads/' . $info->getSaveName();
                $head_pic_name = CompressImg($file);
                $head_save = $a_pic->head_pic($head_pic_name, input('user_id'));
                session('head_pic_url', $head_save['head_url_id']);
//                $status = 200;
            } else {
                $head_pic_name = $u->field('head_pic')->where('user_id', $user_data['user_id'])->select();
                if (empty($head_pic_name)) {
                    $head_pic_name = "";
                } else {
                    foreach ($head_pic_name as $v) {
                        $head_pic_name = $v->head_pic;
                    }
                }
            }

            $company_authentication_name = "";
            if ((request()->file('authentication')) != NULL) {
                $a_file = request()->file('authentication');
//                $info = $a_file->move(ROOT_PATH . 'public' . DS . 'uploads');
//                $company_authentication_imgs = '/uploads/' . $info->getSaveName();
                $company_authentication_name = CompressImg($a_file);
                $edit = $a_pic->auth_pic($company_authentication_name, input('user_id'));
                $authen_id = Db::name('UserInformation')->where(['user_id' => input('user_id')])->value('company_authentication');
                if ($edit['status'] == 200) {
                    if (!empty($authen_id)) {
                        $authentication_id = $authen_id;
                        $authentication_id = $authentication_id . "," . $edit['company_authentication_url_id'];
                        if (session('edit_auth_pic_id') != "") {
                            $authentication_id = session('edit_auth_pic_id');
                            $authentication_id = $authentication_id . "," . $edit['company_authentication_url_id'];
                            session('edit_auth_pic_id', $authentication_id);
                        } else {
                            session('edit_auth_pic_id', $authentication_id);
                        }
                        session("edit_db_save_pic_id", $authentication_id);
                    } else {
                        if ($edit['status'] == 200) {
                            if (empty(session('edit_auth_pic_id'))) {
                                session('edit_auth_pic_id', $edit['company_authentication_url_id']);
                                $authentication_id = session('edit_auth_pic_id');
                            } else {
                                $authentication_id = session('edit_auth_pic_id');
                                $authentication_id = $authentication_id . "," . $edit['company_authentication_url_id'];
                                session('edit_auth_pic_id', $authentication_id);
                            }
                        } else {
                            $status['status'] = 100;
                        }
                        session("edit_db_save_pic_id", $authentication_id);
                    }
                    $status = 200;
                } else {
                    $status = 100;
                }

            }else{
                $comapny_authentication_pic = Db::name('UserInformation')->where(['user_id'=>$user_data['user_id']])->value('company_authentication');
            }

            if ($cancel == 0) {
//                echo 1;die;
                $name                           = $user_data['name'];
                $comapny_authentication         = empty(session("edit_db_save_pic_id")) ? $comapny_authentication_pic : session("edit_db_save_pic_id");
                $head_pic                       = empty(session('head_pic_url')) ? $head_pic_name : session('head_pic_url');
                $company_name                   = $user_data['company_name'];
                $company_address                = $user_data['company_address'];
//                $c_company_address = explode(',',$company_address);

                $tel                            = $user_data['tel'];
                $qq                             = $user_data['qq'];
                $company_address_val            = json_encode($user_data['company_address_val']);
                $business                       = empty($user_data['category'])?"":implode(",",$user_data['category']);
                $introduce                      = $user_data['introduce'];
                $create_time                    = time();
                $s = Db::name('UserInformation')->where("user_id", $user_data['user_id'])->update([
                    'company_url' => $company_url,
                    'name' => $name,
                    'head_pic'=>$head_pic,
                    'company_video' => $user_data['company_video'],
                    'company_authentication' => $comapny_authentication,
                    'company_name' => $company_name,
                    'company_address' => $company_address,
                    'company_address_val' => $company_address_val,
                    'tel' => $tel,
                    'qq' => $qq,
                    'business' => $business,
                    'introduce' => $introduce,
                    'avl'=>$user_data['avl'],
                    'evl'=>$user_data['evl'],
                ]);
                if ($s) {
                    $status = 200;
                    session("edit_db_save_pic_id", NULL);
                    session('edit_auth_pic_id', NULL);
                    session('head_pic_url', NULL);
                    session('intro_img',NULL);
                } else {
                    $status = 200;

                }
            }else if($cancel == 1){
                session("edit_db_save_pic_id", NULL);
                session('edit_auth_pic_id', NULL);
                session('head_pic_url', NULL);
                session('intro_img',NULL);
            }
//            var_dump($user_data['category']);die;
            //先删除UserCategory中的对应user_id的数据，然后再添加进去
            if (empty($user_data['category'])) {

            } else {
                $c_check = Db::name("UserCategory")->where([
                    'user_id' => $user_data['user_id']
                ])->value('category_id');

                if (empty($c_check)) {
                    if (empty($user_data['category'])) {

                    } else {
                        $cat = new UserCategory();
                        $category = $user_data['category'];
//                    var_dump($category);die;
                        foreach ($category as $k => $v) {
                            $cate_data[$k]['user_id'] = $user_data['user_id'];
                            $cate_data[$k]['category_id'] = $v;
                        }
                        $cat->saveAll($cate_data);
                    }
                } else {
                    $c_s = Db::name("UserCategory")->where([
                        'user_id' => $user_data['user_id']
                    ])->delete();

                    if ($c_s) {
                        $cat = new UserCategory();
                        $category = $user_data['category'];
//                    var_dump($category);die;
                        foreach ($category as $k => $v) {
                            $cate_data[$k]['user_id'] = $user_data['user_id'];
                            $cate_data[$k]['category_id'] = $v;
                        }
                        $cat->saveAll($cate_data);
                    }
                }
            }

        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }


    /**
     * @return string
     * 企业介绍图片
     */

    public function intro_img(){
//        session('save_intro_img',null);
//        session('intro_img',null);die;
        $cancel = input('cancel');
        if ((request()->file('image')) != NULL) {
            $file = request()->file('image');
            // 移动到框架应用根目录/public/uploads/ 目录下
//            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
//            $intro_img_save = '/uploads/' . $info->getSaveName();
            $intro_img = CompressImg($file);
        }

        if(empty(session('intro_img'))){
            session('intro_img',$intro_img);

        }

        return json_encode($intro_img);
    }


    /**
     * 取消收藏
     */
    public function cancel()
    {
        $id = input('id');
        $user_id = input('user_id');
        $s = \app\common\model\Collection::where(['collection_user_id' => $user_id, 'collectioned_user_id' => $id])->delete();
        if ($s) {
            $status = 200;
        } else {
            $status = 100;
        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 修改公司名称  手机端使用
     */

    public function update_name()
    {
        $id = input('user_id');
        $name = input('company_name');
        $u = new UserInformation();
        $u_name = Db::name("UserInformation")->where([
            'user_id' => $id
        ])->select();
        //新增
        if (empty($u_name)) {
            $u->user_id = $id;
            $u->company_name = $name;
            $c = $u->save();
        } else {
            //修改
            $c = $u->where(['user_id' => $id])->update(['company_name' => $name]);
        }
        if ($c) {
            $status = 200;
        } else {
            $status = 100;
        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 修改企业地址
     */

    public function update_address()
    {
        $id = input('user_id');
        $ad = input('company_address');
        $u = new UserInformation();
        $u_add = Db::name("UserInformation")->where([
            'user_id' => $id
        ])->select();
        if (empty($u_add)) {
            $u->user_id = $id;
            $u->company_address = $ad;
            $c = $u->save();
        } else {
            $c = $u->where(['user_id' => $id])->update(['company_address' => $ad]);
        }
        if ($c) {
            $status = 200;
        } else {
            $status = 100;
        }
        $j = [
            'status' => $status
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 移动端我的合同
     */
    public function hetong()
    {
        $id = input('user_id');
//        $id = 0;
        $customer_id = Db::name('Customer')->where(['user_id' => $id])->order('create_time desc')->select();

        if (empty($customer_id)) {
            $customer = array();
        } else {
            foreach ($customer_id as $k => $v) {
                $user_id[$k]                        = $v['customer_id'];
                $customer[$k]['contract_name']      = $v['contract_name'];
                $customer[$k]['status']             = $v['status'];
                $customer[$k]['create_time']        = date("Y-m-d",$v['create_time']);
                $customer[$k]['customer_id']        = $v['customer_id'];
                $customer[$k]['company_name'] =     empty($user_id[$k]) ? "" : empty(Db::name('UserInformation')->where(['user_id'=>$user_id[$k]])->value('company_name'))
                    ? Db::name('User')->where(['id'=>$user_id[$k]])->value('login_cell') : Db::name('UserInformation')->where(['user_id'=>$user_id[$k]])->value('company_name');
                $customer[$k]['id'] = $v['id'];
            }
        }
        $j = [
            'data' => $customer
        ];
        return json_encode($j);
    }

    /**
     * @return string
     * 客户状态关系   这一块还是有点问题
     *
     */
    /**
     * status
     * 0 等待上传
     * 101 等待对方确认合作
     * 102 我发是否确认合作
     * 103 被拒接
     * 104 已拒接
     * 200 合同签订中
     * 210 合同签订中有更新
     * 221 等待对方确认
     * 222 等待我确认
     * 300 合同签订成功
     */
    public function status()
    {
        $page = input('page');
        $id = input('user_id');

        $n_count = Db::name("Customer")->where("user_id = $id AND status != 500")->count();
        if (empty($page)) {
            $cus_data = Db::name("Customer")->where("user_id = $id AND status != 500")->order('create_time desc')->select();
            if (empty($cus_data)) {
                $data = array();
            } else {
                //  此处 需要修改
                $data = $cus_data;
                foreach($data as $k=>$v){
                    unset($data[$k]['c_status']);
                    $data[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                    $data[$k]['update_time'] = date('Y-m-d',$v['update_time']);
                    $data[$k]['company_name']= Db::name('UserInformation')->where(['user_id'=>$v['customer_id']])->value('company_name');
                    $data[$k]['company_id']     = $v['customer_id'];
                    $data[$k]['contract_id']    = $v['id'];
                }
            }
            $j = [
                'data' => $data
            ];
        } else {

            //页码
            $n_num = ceil($n_count / 10);
            if ($page <= 1) {
                if ($n_count < 10) {
                    $n_count = $n_count;
                    $cus_data = Db::name("Customer")->where("user_id = $id")->where('status','<>',500)->order('update_time desc')->limit(0, $n_count)->select();

                } else {
                    $cus_data = Db::name("Customer")->where("user_id = $id")->where('status','<>',500)->order('update_time desc')->limit(0, 10)->select();
                }
            } else if ($page > 1 && $page <= ($n_num - 1) && $n_num > 1) {
                $cus_data = Db::name("Customer")->where("user_id = $id")->where('status','<>',500)->order('update_time desc')->limit(($page - 1) * 10, 10)->select();
            } else if ($page == $n_num) {
                $cus_data = Db::name("Customer")->where("user_id = $id")->where('status','<>',500)->order('update_time desc')->limit($n_count - ($n_num - 1) * 10)->select();
            }


            if (empty($cus_data)) {
                $data = array();
                $n_count = 0;
                $status = 100;
            } else {
                $data = $cus_data;
                foreach($data as $k=>$v){
                    unset($data[$k]['c_status']);
                    $data[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                    $data[$k]['update_time'] = date('Y-m-d',$v['update_time']);
                    $data[$k]['company_name']= Db::name('UserInformation')->where(['user_id'=>$v['customer_id']])->value('company_name');
                    $data[$k]['company_id']     = $v['customer_id'];
                    $data[$k]['contract_id']    = $v['id'];
                }
            }

//            var_dump($data);die;
            $j = [
                'count' => $n_count,
                'data' => $data,
            ];
        }
        return json_encode($j);
    }

    //接下来的接口是我的客户那些按钮的状态改变   这里写的我真的是无语，自己都被绕晕了，什么狗屎代码
    public function change_custom()
    {

        $btn_name                   = input('btn_value');
        $user_id                    = input('user_id');
        $cr_time                    = input('cr_time');
        $company_id                 = input('company_id');
        $id                         = input('id');
        $custom_status              = input('c_status');

        $contract                   = input('contact_id');
        //  添加新客户
        if ($btn_name == "first") {

            if($user_id == $company_id){
                $status = 400;
                $msg = "不能和自己合作！！";
            }else{
                //判断之前是否合作？合作是否已经结束？
                $u_check = Db::name('Customer')->where([
                    'user_id'=>$user_id,
                    'customer_id'=>$company_id
                ])->order('create_time desc')->value('status');
                $s_check = Db::name('Customer')->where([
                    'user_id'=>$company_id,
                    'customer_id'=>$user_id
                ])->value('status');


                if(is_numeric($u_check)) {

                    if (($u_check == 104) || ($u_check == 103)) {

                        $cus = new Customer();
                        $cus->customer_id = $company_id;
                        $cus->user_id = $user_id;
                        $cus->contract_name = "";
                        $cus->status = 0;
                        $cus->c_status = 0;
                        $cus->create_time = time();
                        $cus->update_time = time();
                        $s = $cus->save();
                        $cus->add($company_id, $user_id);
                        if ($s) {
                            $status = 200;
                            $msg = "添加客户成功！！";
                        } else {
                            $status = 100;
                            $msg = "添加客户失败！！";
                        }
                    } else if ($u_check == 300 || $u_check == 500) {

                        $cus = new Customer();
                        $cus->customer_id = $company_id;
                        $cus->user_id = $user_id;
                        $cus->contract_name = "";
                        $cus->status = 0;
                        $cus->c_status = 0;
                        $cus->create_time = time();
                        $cus->update_time = time();
                        $s = $cus->save();
                        $cus->add($company_id, $user_id);
                        if ($s) {
                            $status = 200;
                            $msg = "添加客户成功！！";
                        } else {
                            $status = 100;
                            $msg = "添加客户失败！！";
                        }
                    } else {

                        $status = 400;
                        $msg = "合作未完成";
                    }
                }else{
                    $cus = new Customer();
                    $cus->customer_id = $company_id;
                    $cus->user_id = $user_id;
                    $cus->contract_name = "";
                    $cus->status = 0;
                    $cus->c_status = 0;
                    $cus->create_time = time();
                    $cus->update_time = time();
                    $s = $cus->save();
                    $cus->add($company_id, $user_id);
                    if ($s) {
                        $status = 200;
                        $msg = "添加客户成功！！";
                    } else {
                        $status = 100;
                        $msg = "添加客户失败！！";
                    }
                }
            }
        } else  if($btn_name == "accept"){
//                }
            $s = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract-1)])->update(['status'=>200,'c_status'=>0]);
            if($s != 1){
                $s =  Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract+1)])->update(['status'=>200,'c_status'=>0]);

            }
            Db::name('Customer')->where(['id'=>$contract])->update(['c_status'=>0,'status'=>200]);
                $s = 200;
                if($s){
                    $status = 200;
                    $msg    = "确认成功";
                }else{
                    $status = 100;
                    $msg    = "确认失败";
                }

            }else if($btn_name == "refuse"){
                $status = Db::name('Customer')->where(['id'=>$contract])->value('status');
            $s = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id])->where('status','<>','300')->update([
                'status'=> 103
            ]);
            if(!$s){
                $s = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id])->where('status','<>','300')->update([
                    'status'=> 103
                ]);
            }

            //我方合同改为已拒绝
            $m_s = Db::name('Customer')->where(['id'=>$contract])->update([
                'status'=> 104
            ]);

                if($m_s){
                    $status = 200;
                    $msg    = "拒绝成功";
                }else{
                    $status = 100;
                    $msg    = "拒绝失败";
                }


        }else if($btn_name == "delete"){
                //  删除合同列表
                $s = Db::name('Customer')->where(['id'=>$contract])->delete();
                if($s){
                    $status = 200;
                    $msg    = "删除成功";
                }else{
                    $status = 100;
                    $msg    = "删除失败";
                }
        }else if($btn_name == "confirm"){

           //  先判断对方 是否已经确认过了 合同

            $s_check = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract+1)])->field('status,c_status')->find();

            if($s_check == NULL){
                $s_check = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract-1)])->field('status,c_status')->find();
            }


            if(($s_check['status'] == 210 || $s_check['status'] == 200)  && ($s_check['c_status'] == 0)){
                //表示对方  未点击确认
                $s = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract-1)])->update(['status'=>222]);
                if($s != 1){
                    $s = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract+1)])->update(['status'=>222]);

                }
                Db::name('Customer')->where(['id'=>$contract])->update(['c_status'=>1,'status'=>221]);
            }else if($s_check['status'] == 221  && ($s_check['c_status'] == 1)){
                //  表示对方已确认

                $s = Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract-1)])->update(['status'=>300]);


                if($s != 1){
                    $s =  Db::name('Customer')->where(['user_id'=>$company_id,'customer_id'=>$user_id,'id'=>($contract+1)])->update(['status'=>300]);

                }

//                var_dump($s);die;
                Db::name('Customer')->where(['id'=>$contract])->update(['status'=>300,'c_status'=>0]);
            }

            if($s){
                $status = 200;
                $msg    = "确认合同成功";
            }else{
                $status = 100;
                $msg    = "确认合同失败";
            }


        }

        $j = [
            'status' => $status,
            'msg'    =>$msg
        ];
        return json_encode($j);
    }

    /**
     * 删除头图
     * @return string
     */

    public function del_head_img()
    {
        $s = Db::name("UserInformation")->where("user_id", input('user_id'))->update([
            'head_pic' => ""
        ]);
        if ($s) {
            $status = 200;
            $msg = "删除成功";
        } else {
            $status = 400;
            $msg = "删除失败";
        }
        $j = [
            'status' => $status,
            'msg' => $msg
        ];
        return json_encode($j);
    }

    /**
     * 删除认证图片
     * @return string
     */
    public function del_authentication()
    {
        $s = Db::name("UserInformation")->where("user_id", input('user_id'))->update([
            'company_authentication' => ""
        ]);
        if ($s) {
            $status = 200;
            $msg = "删除成功";
        } else {
            $status = 100;
            $msg = "删除失败";
        }
        $j = [
            'status' => $status,
            'msg' => $msg
        ];
        return json_encode($j);
    }

    /**
     * @return string
     *
     * 删除图片
     *
     */

    public function del_img()
    {
        //图片路径
        $url = input('url');
        //删除图片的类型
        $type = input('type');
        //用户id
        $id = input('id');
        $user_id = input('user_id');
        if ($type == "notice") {
            $notice_data = Db::name("Notice")->where([
                'id' => $id
            ])->value("notice_url");
//            var_dump($id);die;
            if (!empty($notice_data)) {
                $notice_url = explode(",", $notice_data);
            }
            foreach ($notice_url as $k => $v) {
                if ($url == (Db::name("notice_pic")->where(['id' => $v])->value('notice_url'))) {
                    unset($v);
                } else {
                    $new_url[] = $v;
                }
            }
            if (empty($new_url)) {
                $new_url = NULL;
            } else {
                $new_url = implode(",", $new_url);
            }

            $s = Db::name("Notice")->where([
                'id' => $id
            ])->update([
                'notice_url' => $new_url
            ]);
            if ($s) {
                $status = 200;
            } else {
                $status = 100;
            }
            $j = [
                'status' => $status
            ];
        } else if ($type == "head") {
            $s = Db::name("UserInformation")->where([
                'user_id' => $user_id
            ])->update([
                'head_pic' => ""
            ]);
            if ($s) {
                $status = 200;
                session('head_pic_url',NULL);
            } else {
                $status = 100;
            }
            $j = [
                'status' => $status,
            ];
        } else if ($type == "authentication") {

            $authentication_data = Db::name("UserInformation")->where([
                'user_id' => $user_id
            ])->value("company_authentication");
            if (!empty($authentication_data)) {
                $authentication_data = explode(",", $authentication_data);
            }
            foreach ($authentication_data as $v) {
                if ($url == (Db::name("authentication_pic")->where(['id' => $v])->value('pic_url'))) {
                    unset($v);
                } else {
                    $new_url[] = $v;
                }
            }
            if (empty($new_url)) {
                $new_url = NULL;
            } else {
                $new_url = implode(",", $new_url);
            }
            /*var_dump($new_url);die;*/

            $s = Db::name("UserInformation")->where([
                'user_id' => $user_id
            ])->update([
                'company_authentication' => $new_url
            ]);
            if ($s) {
                $status = 200;
            } else {
                $status = 100;
            }
            $j = [
                'status' => $status,
            ];

        }
        return json_encode($j);
    }

    public function yaoqing()
    {
        $user_id = input('user_id');
        $page = input('page');
//        $user_id = 125;
        //作为邀请者的 数据
        $con_data = Db::name('Yaoqing')->where(['user_id' => $user_id])->field('contract_id')->select();
        if (empty($con_data)) {
            //作为被邀请者的 数据
            $con_data = Db::name('Yaoqing')->where(['used_id' => $user_id])->field('contract_id')->select();
        }

        $n_count = count($con_data);

        if (empty($con_data)) {
            $hetong = array();
        } else {
            //页码
            $n_num = ceil($n_count / 10);
            if ($page <= 1) {
                if ($n_count < 10) {
                    $n_count = $n_count;
                    //查询合同数据
                    foreach ($con_data as $k => $v) {
                        $hetong[$k] = Db::name('Customer')->where(['id' => $v['contract_id']])->limit(0, $n_count)->find();
                    }

                } else {
                    foreach ($con_data as $k => $v) {
                        $hetong[$k] = Db::name('Customer')->where(['id' => $v['contract_id']])->limit(0, 10)->find();
                    }
                }
            } else if ($page > 1 && $page <= ($n_num - 1) && $n_num > 1) {
                foreach ($con_data as $k => $v) {
                    $hetong[$k] = Db::name('Customer')->where(['id' => $v['contract_id']])->limit(($page - 1) * 10, 10)->limit(0, 10)->find();
                }
            } else if ($page == $n_num) {
                foreach ($con_data as $k => $v) {
                    $hetong[$k] = Db::name('Customer')->where(['id' => $v['contract_id']])->limit($n_count - ($n_num - 1) * 10)->limit(0, 10)->find();
                }
            }

            $user_id = array();
            foreach ($hetong as $k => $v) {
                unset($hetong[$k]['c_status']);
                $hetong[$k]['create_time'] = date('Y-m-d', $v['create_time']);
                $hetong[$k]['update_time'] = date('Y-m-d', $v['update_time']);
                $hetong[$k]['company_name'] = Db::name('UserInformation')->where(['user_id' => $v['customer_id']])->value('company_name');
                $hetong[$k]['company_id'] = $v['customer_id'];
                $hetong[$k]['contract_id'] = $v['id'];
            }
        }
        $j = [
            'count' => $n_count,
            'data' => $hetong,
            'code' => 200,
            'msg' => "获取数据成功"
        ];

        return json($j);
    }

    /**
     * @return \think\response\Json
     * 模板合同列表
     */

    public function hetong_url(){
        $data = Db::name('Hetong')->select();

        $j = [
            'code' => 200,
            'msg'  => "获取数据成功",
            'data' =>$data
        ];
        return json($j);
    }
}
