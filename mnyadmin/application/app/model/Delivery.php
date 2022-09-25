<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/22
 * Time: 15:53
 */

namespace app\app\model;
use app\common\model\AppBase;
use think\Db;

class Delivery extends AppBase
{
    protected $status_arr =[1=>'启用',2=>'暂停配送'];

    public function deliverStatusEnum(){
        return $this->status_arr;
    }
}