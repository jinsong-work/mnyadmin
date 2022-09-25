<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/20
 * Time: 10:24
 */

namespace app\admin\model;
use think\Model;
use think\Db;

class Cache extends Model
{
    /**
     * 获取组合后的地址
     */
    public function address_cache(){
        $address = Db::name('address')->order('listorder DESC, id asc')->select();
        $address_tree_data = model('admin/Address')->getAddressTreeData($address);
        cache("Address", $address_tree_data);
        return $address_tree_data;
    }
}