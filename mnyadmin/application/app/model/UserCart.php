<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/23
 * Time: 11:08
 */

namespace app\app\model;
use app\common\model\AppBase;
use think\Db;

class UserCart extends AppBase
{
    public function getUserCartPageGoods($page,$where,$field,$limit=10,$order='t1.add_time desc,t1.id desc'){
        return $this
            ->alias('t1')
            ->leftJoin('goods t2','t1.goods_id=t2.id')
            ->withAttr('weight',function($value,$data){
                return $value.($this->getWeightUnit());
            })
            ->where($where)
            ->field($field)
            ->order($order)
            ->page($page,$limit)
            ->select();
    }
}