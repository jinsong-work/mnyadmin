<?php
// +----------------------------------------------------------------------
// | 公共控制模块
// +----------------------------------------------------------------------
namespace app\common\controller;

use think\Controller;

class Base extends Controller
{
    //空操作
    public function _empty()
    {
        $this->error('该页面不存在！');
    }
}
