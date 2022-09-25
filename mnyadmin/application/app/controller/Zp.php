<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/21
 * Time: 17:18
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Exception;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

class Zp extends AppBase
{
    protected $noNeedLogin = ['getGoodsCategory','getGoods'];
    //质品的分类id值
    protected $parentid = 11;

    /**
     * 获取质品的分类
     */
    public function getGoodsCategory(){
        try{
            $catid = $this->request->param('catid',0);
            $where[] = ['parentid','=',$this->parentid];
            $where[] = ['status','=',1];
            $where[] = ['level','=',1];//一级分类
            if($catid) $where[] = ['id','=',$catid];
            $category = model('app/GoodsCategory')->getClassList($where,'id,catname,thumb,level');
            return jsonReturn(1,'获取质品的分类',$category);
        }catch(\Exception $e){
            return jsonReturn(0,$e->getMessage());
        }
    }

    /**
     * 获取质品的商品列表
     * 统一的is_select=0即可。
     */
    public function getGoods(){
        $page = $this->request->post('page');
        $where[] = ['t1.status','=',1];
        $child_ids = model('app/GoodsCategory')->getChildTree($this->parentid);
        $where[] = ['catid','in',$child_ids];
        $field = 't2.parentid,t1.catid,t1.id,t1.thumb,t1.goods_name,t1.limit_buy_num,t1.tag,t1.feature,t1.weight,t1.price,t1.is_select,t1.is_specs';
        $goods = model('app/Goods')->getGoodsPageList($page,$where,$field);
        if(!empty($goods)){
            foreach ($goods as &$v){
                if($v['is_select']){
                    return jsonReturn(0,'商品设置异常');
                    break;
                }
//                正常商品情况，分有规格和无规格，有规格的会覆盖有规格的数据，为了其他地方操作方面，这里直接改goods表数据
                //如果是覆盖表，那么这里取得就是有规格的最低价，无规格的价和重量
            }
        }
        return jsonReturn(1,'获取质品的商品列表',$goods);
    }
}