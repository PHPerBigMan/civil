<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use app\index\controller\TinyImg;
use Intervention\Image\ImageManagerStatic as Image;
use Tinify\Tinify;


/**
 * @param $url
 * @return string
 * author hongwenyang
 * method description :  压缩图片
 */

function MakeImg($url)
{
    Tinify::setKey('fmkIqmBx4HGW1cstrk9Uii-lHqlZS6cS');
    $source = \Tinify\fromUrl(URL . $url);
    $saveImg = md5(time() . rand(0, 999999)) . '.png';
    $source->toFile('./ysImg/' . $saveImg);
    return '/ysImg/' . $saveImg;
}


function CompressImg($file)
{
    $img = Image::make($file);
    $size = $img->filesize();
    $limit = 100000; // 100kb以上压缩
    if ($size > $limit) {
        $rate = $limit / $size * 75;
        $img->encode('jpg', $rate);
    }
    $path = date('Ymd') . '/' . md5(time() . rand(0, 999999)) . '.jpg';
    $savePath = ROOT_PATH . 'public/uploads/' . $path;
    if(!is_dir(ROOT_PATH . 'public/uploads/' .date('Ymd') )){
        mkdir(ROOT_PATH . 'public/uploads/' .date('Ymd'),0777,true);
    }
    $img->save($savePath);
    return '/uploads/' . $path;
}

/*demo*/
function MakeImg_1($url)
{
    $tingImg = new TinyImg();
    $key = "fmkIqmBx4HGW1cstrk9Uii-lHqlZS6cS";
    $saveImg = md5(time() . rand(0, 999999)) . '.png';
    $tingImg->compressImgsFolder($key, URL . $url, './ysImg/' . $saveImg);
}


/**
 * @param $arr
 * @return mixed
 * author hongwenyang
 * method description : 二维数组去重
 */

function getArrayUniqueByKeys($arr)
{
    $arr_out = array();
    foreach ($arr as $k => $v) {
        $key_out = $v['company_name'] . "-" . $v['id']; //提取内部一维数组的key(name age)作为外部数组的键

        if (array_key_exists($key_out, $arr_out)) {
            continue;
        } else {
            $arr_out[$key_out] = $arr[$k]; //以key_out作为外部数组的键
            $arr_wish[$k] = $arr[$k];  //实现二维数组唯一性
        }
    }
    return $arr_wish;
}
