<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/20
 * Time: 9:55
 */

namespace app\shop\model;
use think\Model;

class Cache extends Model
{
    public function config_cache(){
        $data = unserialize(model('admin/Module')
            ->where(array('module' => 'shop'))
            ->value('setting'));

        cache("Shop_Config", $data);
        return $data;
    }
}