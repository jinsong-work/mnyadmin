<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/21
 * Time: 15:57
 */

namespace app\app\model;
use app\common\model\AppBase;
use think\Db;

class Goods extends AppBase
{

    public function ceshi(){

    }

    public function getWeightUnit(){
        return cache('Shop_Config')['site_weight_unit'];
    }

    public function getFeatureAttr($value){
        return $value?explode(',',$value):[];
    }

    public function addUserSearch($user_id,$keyword){
        $search = Db::name('user_search')->where('user_id',$user_id)->where('keyword',$keyword)->find();
        if(!$search){
            Db::name('user_search')->insertGetId([
                'keyword'=>$keyword,
                'user_id'=>$user_id,
                'search_time'=>time()
            ]);
        }
    }

    /**
     * 获取商品的规格弹窗数据
     */
    public function getGoodsPop($goods_id){
        $goods_info = Db::name('goods')->where('id',$goods_id)->where('status',1)->find();
        if(!$goods_info) throw new \Exception('该商品不存在或已下架');
        //根据is_select区分规格
        if($goods_info['is_select']){
            $data = [];
            $item_lists = Db::name('goods_item')->where('goods_id',$goods_id)->order('type asc')->select();
            if(count($item_lists) != 2) throw new \Exception('商品设置错误');
            foreach ($item_lists as $v) {
                $specs_arr = [];
                $is_specs = Db::name('goods_specs')->where('goods_id',$goods_id)->where('type',$v['type'])->count();
                if($is_specs){
                    $specs_lists = Db::name('goods_specs')
                        ->where('goods_id',$goods_id)
                        ->where('type',$v['type'])
                        ->select();
                    foreach ($specs_lists as $v2){
                        $spec_item = Db::name('goods_specs_item')->where('specs_id',$v2['id'])->select();
                        $specs_arr[] = ['specs_name'=>$v2['specs_name'],'item'=>$spec_item];
                    }
                    $min_price_goods = Db::name('goods_price')->where('goods_id', $v['goods_id'])->where('type', $v['type'])
                        ->order('price asc,id desc')
                        ->find();
                    $data[] = [
                        'id' => $v['goods_id'],
                        'type' => $v['type'],
                        'thumb' => $v['thumb'],
                        'is_specs'=>$goods_info['is_specs'],
                        'price' => $min_price_goods['price'],
                        'weight' => $min_price_goods['weight'].($this->getWeightUnit()),
                        'item_id_group'=>$min_price_goods['item_id_group'],
                        'limit_buy_num' => $v['limit_buy_num'],
                        'specs_arr' => $specs_arr
                    ];
                }else{
                    $data[] = [
                        'id' => $v['goods_id'],
                        'type' => $v['type'],
                        'thumb' => $v['thumb'],
                        'is_specs'=>$goods_info['is_specs'],
                        'price' => $v['price'],
                        'weight' => $v['weight'].($this->getWeightUnit()),
                        'item_id_group'=>[],
                        'limit_buy_num' => $v['limit_buy_num'],
                        'specs_arr' => $specs_arr
                    ];
                }
            }
        }else{
            $data=[];
            $specs_arr = [];
            $is_specs = Db::name('goods_specs')->where('goods_id',$goods_id)->where('type',0)->count();
            //普通，有规格
            if($is_specs){
                $specs_lists = Db::name('goods_specs')
                    ->where('goods_id',$goods_id)
                    ->where('type',0)
                    ->select();
                foreach ($specs_lists as $v){
                    $spec_item = Db::name('goods_specs_item')->where('specs_id',$v['id'])->select();
                    $specs_arr[] = ['specs_name'=>$v['specs_name'],'item'=>$spec_item];
                }
                //获取所有规格的最低价，覆盖原先价格。
                $min_price_goods = Db::name('goods_price')->where('goods_id', $goods_info['id'])->where('type', 0)
                    ->order('price asc,id desc')
                    ->find();
                $data[] = [
                    'id'=>$goods_info['id'],
                    'type'=>0,
                    'thumb'=>$goods_info['thumb'],
                    'is_specs'=>$goods_info['is_specs'],
                    'price'=>$min_price_goods['price'],
                    'weight'=>$min_price_goods['weight'].($this->getWeightUnit()),
                    'item_id_group'=>$min_price_goods['item_id_group'],
                    'limit_buy_num'=>$goods_info['limit_buy_num'],
                    'specs_arr'=>$specs_arr
                ];
            }else{
                $data[] = [
                    'id'=>$goods_id,
                    'type'=>0,
                    'thumb'=>$goods_info['thumb'],
                    'is_specs'=>$goods_info['is_specs'],
                    'price'=>$goods_info['price'],
                    'weight'=>$goods_info['weight'].($this->getWeightUnit()),
                    'limit_buy_num'=>$goods_info['limit_buy_num'],
                    'specs_arr'=>$specs_arr
                ];
            }
        }
        return $data;
    }

    /**
     * 减少购物车
     */
    public function reduceCart($user_id,$goods_id,$type,$specs){
        $cart_info = Db::name('user_cart')->where('user_id',$user_id)
            ->where('goods_id',$goods_id)
            ->where('type',$type)
            ->find();
        if(!$cart_info) throw new \Exception('无法减少');
        if($cart_info['num'] == 1){
            $res = Db::name('user_cart')->where('id',$cart_info['id'])->delete();
        }else{
            $res = Db::name('user_cart')->where('id',$cart_info['id'])->setDec('num');
        }
        if(!$res) throw new \Exception('减少失败');
        return $cart_info['num'] -1;
    }

    /**
     * @param $user_id
     * @param $goods_id
     * @param $goods_type
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * $specs:  3,4,5   5,6  8,9,3
     */
    public function addCart($user_id,$goods_id,$type,$specs){
        //选择规格后加入到购物车
        if($specs){
            //判断购物车中是否有同规格，同类型，同一个商品
            $cart_info = Db::name('user_cart')
                ->where('user_id',$user_id)
                ->where('goods_id',$goods_id)
                ->where('type',$type)
                ->where('specs',$specs)
                ->find();
            if(empty($cart_info)){
                //直接加入购物车
                $res = Db::name('user_cart')->insertGetId(['goods_id'=>$goods_id,'user_id'=>$user_id,'type'=>$type,'specs'=>$specs,'num'=>1,'add_time'=>time()]);
            }else{
                $res = Db::name('user_cart')->where('id',$cart_info['id'])->setInc('num');
            }
            if(!$res) throw new \Exception('添加购物车失败[有规格商品]');
            return empty($cart_info)?1:($cart_info['num'] + 1);
        }else{
            $map[] = ['user_id','=',$user_id];
            $map[] = ['goods_id','=',$goods_id];
            $map[] = ['type','=',$type];
            $cart_goods = Db::name('user_cart')
                ->where($map)
                ->find();
            if($cart_goods){
                $res = Db::name('user_cart')->where('id',$cart_goods['id'])->setInc('num');
            }else{
                $res = Db::name('user_cart')->insertGetId(['type'=>$type,'user_id'=>$user_id,'goods_id'=>$goods_id,'num'=>1,'add_time'=>time()]);
            }
            if(!$res) throw new \Exception('添加购物车失败');
            return empty($cart_goods)?1:($cart_goods['num'] + 1);
        }
    }

    /**
     * 统一分页数据获取 site_weight_unit
     */
    public function getGoodsPageList($page,$where,$field='t1.*,t2.parentid',$limit=5,$order='t1.listorder desc,t1.id desc'){
//        var_dump($this->getWeightUnit());die;
        return $this
            ->alias('t1')
            ->leftJoin('goods_category t2','t1.catid=t2.id')
            ->withAttr('parentid',function($value,$data){
                return '一级分类：'.(Db::name('goods_category')->where('id',$value)->value('catname'));
            })
            ->withAttr('catid',function($value,$data){
                return '二级分类：'.(Db::name('goods_category')->where('id',$value)->value('catname'));
            })
            ->withAttr('weight',function($value,$data){
                return $value.($this->getWeightUnit());
            })
            ->where($where)
            ->field($field)
            ->order($order)
            ->page($page,$limit)
            ->select();
    }

    /**
     * 获取商品详情
     */
    public function getGoodsDetail($id){

    }

//    public function getPriceAttr($value,$data){
//        return Db::name('goods_price')->where('goods_id',$data['id'])->value('price');
//    }
}