<?php

namespace app\admin\controller;

use app\admin\controller\Base;

class Index extends Base
{
    public function index()
    {
        $data = [
            'title'=>'首页'
        ];
       return view('index/index',$data);
    }
}
