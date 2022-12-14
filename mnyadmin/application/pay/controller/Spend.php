<?php
// +----------------------------------------------------------------------
// | Yzncms [ 御宅男工作室 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://yzncms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 御宅男 <530765310@qq.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会员支付前台
// +----------------------------------------------------------------------
namespace app\pay\controller;

use app\common\controller\AdminBase;
use app\pay\model\Spend as SpendModel;

class Spend extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
        $this->modelClass = new SpendModel;

    }
}
