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

class Province extends Model
{
    /**
     * @param $father
     * @return array
     * author hongwenyang
     * method description :获取城市 地区列表
     */
    static  public function city($father){
        $data = Db::table('city')->where('father',$father)->select();

        $cityList = "";
        foreach($data as $v){
            $cityList .= "<option value=".$v['cityID'].">".$v['city']."</option>";
        }


        $area = Db::table('area')->where('father',$data[0]['cityID'])->select();;
        $areaList = "";
        foreach($area as $v){
            $areaList .= "<option value=".$v['areaID'].">".$v['area']."</option>";
        }
        $j = [
            'city'=>$cityList,
            'area'=>$areaList
        ];
        return $j;
    }

    /**
     * @param $father
     * @return string
     * author hongwenyang
     * method description : 获取地区列表
     */
    static public function area($father){
        $data = Db::table('area')->where('father',$father)->select();

        $areaList = "";
        foreach($data as $v){
            $areaList .= "<option value=".$v['areaID'].">".$v['area']."</option>";
        }
        return $areaList;
    }

    /**
     * @param $fid
     * @return array
     * author hongwenyang
     * method description : 获取二级、三级分类
     */

    static public function cat($fid)
    {
        $data = Db::table('category')->where('pid',$fid)->select();
        $List = "";
        //二级分类
        foreach($data as $v){
            $List .= "<option value=".$v['category_id'].">".$v['c_name']."</option>";
        }
        $Tlist = "";
        if(!empty($data[0])){
            $third = Db::table('category')->where('pid',$data[0]['category_id'])->select();

            if(!empty($third)){
                foreach($third as $v){
                    $Tlist .= "<option value=".$v['category_id'].">".$v['c_name']."</option>";
                }
            }
        }
        $j = [
            'sec'=>$List,
            'third'=>$Tlist
        ];
        return $j;
    }


    /**
     * @param $fid
     * @return array
     * author hongwenyang
     * method description : 获取三级分类
     */

    static public function catSec($fid)
    {
        $data = Db::table('category')->where('pid',$fid)->select();
        $List = "";
        //二级分类
        foreach($data as $v){
            $List .= "<option value=".$v['category_id'].">".$v['c_name']."</option>";
        }

        $j = [
            'th'=>$List,
        ];
        return $j;
    }
}
