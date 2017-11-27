<?php
/**
 * Created by 洪文扬
 * User: MR.HONG
 * Date: 2017/3/18
 * Time: 19:50
 */
namespace app\common\model;

use think\Db;
use think\Model;

class Category extends Model
{
    //

    /**
     * @param $category_id  int id
     * @return bool
     * 根据类别层级删除数据  level为1时返回false  不删除
     * 状态码: 101 为一级分类   102 二级分类下包含视频文件  200 可以删除的分类
     */

    public function level_num($category_id)
    {
        //获取分类的等级
        $level = $this->where('category_id', $category_id)
            ->select();
        //获取到当前分类的级别
        $level_id = $level[0]['level'];
        if($level_id == 1){
            //根据pid 找下级分类  如果一级分类下包含二级分类，则无法删除返回提示错误
            if($this->name('category')->where(['pid'=>$category_id,'level'=>2])->find()){
                return 101;
            }else{
                $this->name('category')->where('category_id',$category_id)->delete();
                return 200;
            }
        }
    }

    /**
     * @param $list
     * @param string $pk
     * @param string $pid
     * @param string $child
     * @param int $root
     * @return array
     * 无限极分类
     */

    function make_tree1($list,$pk='category_id',$pid='pid',$child='child',$root=0){
        $tree=array();
        foreach($list as $key=> $val){

            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree1($list,$pk,$pid,$child,$val[$pk]);
                    if(!empty($child)){
                        $val['child']=$child;
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }




    function make_tree2($list,$pk='category_id',$pid='pid',$child='child',$root=0){
        $tree=array();
        foreach($list as $key=> $val){

            if($val[0][$pid]==$root){
                //获取当前$pid所有子类
                unset($list[0][$key]);
                if(! empty($list)){
                    $child=$this->make_tree2($list,$pk,$pid,$child,$val[0][$pk]);
                    if(!empty($child)){
                        $val['child']=$child;
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }

}
