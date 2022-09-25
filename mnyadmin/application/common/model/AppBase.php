<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/21
 * Time: 14:39
 */

namespace app\common\model;
use think\Model;
use think\Db;

class AppBase extends Model
{
    /**
     * 统一获取模型的数据列表
     */
    public function getList($where,$field='*',$limit=8,$order='listorder desc,id desc'){
        return Db::name($this->name)
            ->where($where)
            ->field($field)
            ->order($order)
            ->limit($limit)
            ->select();
    }

    /**
     * 统一分页列表数据
     */
    public function getPageList($page,$where=[],$field='*',$limit=8,$order='listorder desc,id desc'){
        $model = Db::name($this->name)->order($order)->field($field);
        if(!empty($where)) $model->where($where);
        return $model->page($page,$limit)->select();
    }
}