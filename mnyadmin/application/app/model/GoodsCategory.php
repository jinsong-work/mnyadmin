<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/21
 * Time: 15:12
 */

namespace app\app\model;
use app\common\model\AppBase;
use think\Db;
use util\Tree;

class GoodsCategory extends AppBase
{
    /**
     * 找出分类id的所有的子集，并以，隔开返回,每次都从全局数据中找
     */
    public function getChildTree($catid,&$child_arr=[]){
        //按照我的思路来
        $child = $this->where('parentid',$catid)->select();
        if($child){
            foreach ($child as $v){
                $child_arr[] = $v['id'];
                $this->getChildTree($v['id'],$child_arr);
            }
        }
        return implode(',',$child_arr);
    }

    public function getClassList($where,$field='*',$order='listorder desc,id desc'){
        //一级分类
        $level_one = $this
            ->where($where)
            ->field($field)
            ->order($order)
            ->select();
//        var_dump($level_one[0]['id']);die;
//        根据一级分类获取二级分类
        if($level_one){
            foreach ($level_one as &$v){
                $v['item'] = Db::name('goods_category')->where('parentid',$v['id'])->field('id,thumb,catname,level')->order('listorder desc,id desc')->select();
            }
        }else{
            throw new \Exception('分类未找见');
        }
        return $level_one;
    }
}