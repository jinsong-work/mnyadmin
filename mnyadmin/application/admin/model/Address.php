<?php
// +----------------------------------------------------------------------
// | 地址管理
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Db;
use \think\Model;

/**
 * 模型
 */
class Address extends Model
{

    public function getAddressTreeData($address){
        $tree = new \util\Tree();
        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $tree->init($address);
        $_list  = $tree->getTreeList($tree->getTreeArray(0), 'name');
        return $_list;
    }

    //根据
    public function getAddress($parentid=0){
        $map[] = ['parentid','=',$parentid];
        $map[] = ['status','=',1];
        return Db::name('address')->where($map)->order('listorder desc,id asc')->select();
    }
}