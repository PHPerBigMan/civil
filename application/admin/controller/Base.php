<?php

namespace app\admin\controller;

use think\Controller;

class Base extends Controller
{
    public function _initialize()
    {
        mb_internal_encoding("UTF-8");
        if (\think\Request::instance()->controller() != 'Login') {
            if (! session('admin')) {
                $this->redirect('login/index');
            }
        }
    }


}
