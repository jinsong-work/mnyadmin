<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/20
 * Time: 9:43
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

/**
 * Class Index
 * @package app\app\controller
 * 首页相关数据
 */
class Index extends AppBase
{
    protected $noNeedLogin = ['getIndexBg','getBanner','getNotice','getIndexGoodsCategory','getIndexGoods'];


    /**
     * 选择其他配送点
     */
    public function selectOtherDelivery(){
        $page = $this->request->param('page',1);
        $region_id = $this->request->param('region_id',0);
        $keyword = $this->request->param('keyword','');

        $map1[] = ['status','=',1];
        $region = model('app/Region')->getList($map1,'id,area_name');

        if(!$region_id){
            $region_id = Db::name('region')->where('status',1)->order('listorder desc,id desc')->value('id');
        }
        $map2[] = ['region_id','=',$region_id];
        if($keyword) $map2[] = ['name','like',"%{$keyword}%"];
        $delivery = model('app/Delivery')->getPageList($page,$map2,'id,region_id,name,delivery_time,address,description,thumb');
        if(!empty($delivery)){
            foreach ($delivery as &$v){
                $v['delivery_time'] = json_decode($v['delivery_time'],true);
            }
        }

        $data['region'] = $region;
        $data['delivery'] = $delivery;
        $data['current_region_name'] = (Db::name('region')->where('id',$this->region_id)->value('area_name'))?:'';
        return jsonReturn(1,'选择其他配送点',$data);
    }

    /**
     * 搜索商品
     */
    public function searchGoods(){
        $keyword = $this->request->param('keyword');
        $page = $this->request->param('page',1);
        $type = $this->request->param('type',1);
        $where[] = ['t1.status','=',1];
        $where[] = ['t1.region_id','=',$this->region_id];
        if($keyword){
            model('app/Goods')->addUserSearch($this->user->id,$keyword);
            $where[] = ['goods_name','like',"%{$keyword}%"];
        }

        switch ($type){
            case '1':
                $order = 't1.is_new desc,t1.sale_num desc,t1.price asc,t1.listorder desc,t1.id desc';
                break;
            case '2':
                $order = 't1.is_new desc,t1.listorder desc,t1.id desc';
                break;
            case '3':
                $order = 't1.sale_num desc,t1.listorder desc,t1.id desc';
                break;
            case '4':
                $order = 't1.price desc,t1.listorder desc,t1.id desc';
                break;
            case '5':
                $order = 't1.price asc,t1.listorder desc,t1.id desc';
                break;
        }

        $field = 't2.parentid,t1.id,t1.thumb,t1.goods_name,t1.limit_buy_num,t1.tag,t1.feature,t1.weight,t1.price,t1.is_select,t1.ad_thumb,t1.is_specs,t1.stock,t1.listorder';
        $goods = model('app/Goods')->getGoodsPageList($page,$where,$field,10,$order);
        $type=1;//首页商品对于下拉的，type=1代表质品。
//        var_dump($goods);die;
        if($goods){
            foreach ($goods as &$v){
                //区分质选和特卖
                if($v['is_select']){
                    $select_goods_info = Db::name('goods_item')
                        ->where('goods_id',$v['id'])
                        ->where('type',$type)
                        ->find();
                    $v['thumb'] = $v['ad_thumb']?:$select_goods_info['thumb'];
                    $v['type'] = $type;//默认质选
                    $v['limit_buy_num'] = $select_goods_info?$select_goods_info['limit_buy_num']:0;
                    if($v['is_specs']){
                        $min_price_info = Db::name('goods_price')
                            ->where('goods_id',$v['id'])
                            ->where('type',$type)
                            ->order('price asc,id desc')
                            ->find();
                        $v['price'] = $min_price_info?$min_price_info['price']:'0.00';
                        $v['stock'] = $min_price_info?$min_price_info['stock']:0;
                        $v['weight'] = $min_price_info?$min_price_info['weight']:0;
                    }else{
                        //无规格
                        $price_info = Db::name('goods_price')
                            ->where('goods_id',$v['id'])
                            ->find();
                        $v['price'] = $price_info?$price_info['price']:'0.00';
                        $v['stock'] = $price_info?$price_info['stock']:0;
                        $v['weight'] = $price_info?$price_info['weight']:0;
                    }

//                    购物车
                    $cart_num = Db::name('user_cart')
                        ->where('user_id',$this->user->id)
                        ->where('goods_id',$v['id'])
                        ->where('type',$type)
                        ->value('num');
                    $v['cart_num'] = $cart_num?:0;
                }else{
                    $v['thumb'] = $v['ad_thumb']?:$v['thumb'];
                    $v['type'] = 0;
                    if($v['is_specs']){
                        $min_price_info = Db::name('goods_price')
                            ->where('goods_id',$v['id'])
                            ->where('type',0)
                            ->order('price asc,id desc')
                            ->find();
                        $v['price'] = $min_price_info?$min_price_info['price']:'0.00';
                        $v['stock'] = $min_price_info?$min_price_info['stock']:0;
                        $v['weight'] = $min_price_info?$min_price_info['weight']:0;
                    }

//                    购物车数量
                    $cart_num = Db::name('user_cart')
                        ->where('user_id',$this->user->id)
                        ->where('goods_id',$v['id'])
                        ->where('type',0)
                        ->value('num');
                    $v['cart_num'] = $cart_num?:0;
                }
//                $v['weight'] = 'dd';
            }
        }
        return jsonReturn(1,'获取搜素商品列表',$goods);
    }

    /**
     * 获取历史搜索记录
     */
    public function searchRecord(){
        $lists = Db::name('user_search')
            ->where('user_id',$this->user->id)
            ->order('search_time desc')
            ->select();

        return jsonReturn(1,'获取历史搜索记录',$lists);
    }

    /**
     * 删除历史搜索记录
     */
    public function delSearchRecord(){
        $res = Db::name('user_search')->where('user_id',$this->user->id)->delete();
        if(!$res) jsonReturn(0,'删除失败');

        return jsonReturn(1,'删除成功');
    }

    /**
     * 首页当前配送点
     */
    public function getIndexDelivery(){
        $delivery = [];
        $delivery_info = Db::name('user_delivery')
            ->alias('t1')
            ->leftJoin('delivery t2','t1.delivery_id = t2.id')
            ->where('t1.user_id',$this->user->id)
            ->where('t2.region_id',$this->region_id)
            ->where('t1.is_current',1)
            ->withAttr('delivery_time',function($value,$data){
                return json_decode($value,true);
            })
            ->field('t2.name,t2.id,t2.thumb,t2.address,t2.delivery_time,t2.status,t2.province_id,t2.city_id,t2.area_id,t2.description,t1.is_current')
            ->find();
        if($delivery_info){
            $delivery_info['province_name'] = Db::name('address')->where('id',$delivery_info['province_id'])->value('name');
            $delivery_info['city_id_name'] = Db::name('address')->where('id',$delivery_info['city_id'])->value('name');
            $delivery_info['area_name'] = Db::name('address')->where('id',$delivery_info['area_id'])->value('name');
            $delivery = $delivery_info;
        }

        return jsonReturn(1,'首页当前配送点',$delivery);
    }

    /**
     * 历史配送点
     */
    public function historyDelivery(){
        $delivery_info = Db::name('user_delivery')
            ->alias('t1')
            ->leftJoin('delivery t2','t1.delivery_id = t2.id')
            ->where('t1.user_id',$this->user->id)
            ->where('t2.region_id',$this->region_id)
            ->where('is_current','<>',1)
            ->withAttr('delivery_time',function($value,$data){
                return json_decode($value,true);
            })
            ->field('t2.name,t2.id,t2.thumb,t2.address,t2.delivery_time,t2.status,t2.province_id,t2.city_id,t2.area_id,t2.description,t1.is_current')
            ->order('t1.id desc')
            ->select();
        if($delivery_info){
            foreach ($delivery_info as &$v){
                $v['province_name'] = Db::name('address')->where('id',$v['province_id'])->value('name');
                $v['city_name'] = Db::name('address')->where('id',$v['city_id'])->value('name');
                $v['area_name'] = Db::name('address')->where('id',$v['area_id'])->value('name');
            }
        }

        return jsonReturn(1,'历史配送点',$delivery_info);
    }

    /**
     * 新人红包
     */
    public function newRedbao(){
        $is_new = false;
        $is_have_new_redbao = Db::name('user_redbao')
            ->alias('t1')
            ->leftJoin('redbao t2','t1.redbao_id = t2.id')
            ->where('t1.user_id',$this->user->id)
            ->where('t2.type',1)//新人红包
            ->find();
        if(empty($is_have_new_redbao)) $is_new = true;
        //获取新人红包详情，当前区域
        $new_redbao = Db::name('redbao')
            ->where('type','1')
            ->where('region_id',$this->region_id)
            ->field('id,min_deduct_money,deduct_money,expire_day')
            ->find();
        $new_redbao['expire_time'] = date("Y-m-d",strtotime("+{$new_redbao['expire_day']} day"));

        $data = [
            'is_new'=>$is_new,
            'new_redbao'=>$new_redbao
        ];
        return jsonReturn(1,'新人红包',$data);
    }

    //获取首页背景图
    public function getIndexBg(){
        $data = cache('Shop_Config');

        return jsonReturn(1,'获取成功',$data['index_bg']);
    }

    /**
     * 获取首页banner图,
     */
    public function getBanner(){
        $where[] = ['status','=',1];
        $where[] = ['region_id','=',$this->region_id];
        $banner = model('app/Banner')->getList($where);
        return jsonReturn(1,'获取首页banner图',$banner);
    }

    /**
     * 获取公告通知
     */
    public function getNotice(){
        $where[] = ['status','=',1];
        $where[] = ['region_id','=',$this->region_id];
        $notice = model('app/Article')->getList($where);
        return jsonReturn(1,'获取公告通知',$notice);
    }

    /**
     * 获取首页商品分类
     */
    public function getIndexGoodsCategory(){
        $where[] = ['parentid','=',10];
        $where[] = ['level','=',1];//一级分类
        $where[] = ['status','=',1];
        $category = model('app/GoodsCategory')->getList($where,'catname,thumb',100);
        return jsonReturn(1,'获取首页商品分类',$category);
    }

    /**
     * 获取首页商品列表
     * 规格：
     *      ['果肉'=>['饱满','满满'],'颜色'=>['白色','红色']]
     *
     */
    public function getIndexGoods(){
        $page = $this->request->param('page',1);
        $where[] = ['t1.status','=',1];
        $where[] = ['t2.parentid','=',10];
        $where[] = ['t1.region_id','=',$this->region_id];
        $field = 't2.parentid,t1.id,t1.thumb,t1.goods_name,t1.limit_buy_num,t1.tag,t1.feature,t1.weight,t1.price,t1.is_select,t1.ad_thumb,t1.is_specs,t1.stock';
        $goods = model('app/Goods')->getGoodsPageList($page,$where,$field,10);
        $type=1;//首页商品对于下拉的，type=1代表质品。
//        var_dump($goods);die;
        if($goods){
            foreach ($goods as &$v){
                //区分质选和特卖
                if($v['is_select']){
                    $select_goods_info = Db::name('goods_item')
                        ->where('goods_id',$v['id'])
                        ->where('type',$type)
                        ->find();
                    $v['thumb'] = $v['ad_thumb']?:$select_goods_info['thumb'];
                    $v['type'] = $type;//默认质选
                    $v['limit_buy_num'] = $select_goods_info?$select_goods_info['limit_buy_num']:0;
                    if($v['is_specs']){
                        $min_price_info = Db::name('goods_price')
                            ->where('goods_id',$v['id'])
                            ->where('type',$type)
                            ->order('price asc,id desc')
                            ->find();
                        $v['price'] = $min_price_info?$min_price_info['price']:'0.00';
                        $v['stock'] = $min_price_info?$min_price_info['stock']:0;
                        $v['weight'] = $min_price_info?$min_price_info['weight']:0;
                    }else{
                        //无规格
                        $price_info = Db::name('goods_price')
                            ->where('goods_id',$v['id'])
                            ->find();
                        $v['price'] = $price_info?$price_info['price']:'0.00';
                        $v['stock'] = $price_info?$price_info['stock']:0;
                        $v['weight'] = $price_info?$price_info['weight']:0;
                    }

//                    购物车
                     $cart_num = Db::name('user_cart')
                        ->where('user_id',$this->user->id)
                        ->where('goods_id',$v['id'])
                        ->where('type',$type)
                        ->value('num');
                     $v['cart_num'] = $cart_num?:0;
                }else{
                    $v['thumb'] = $v['ad_thumb']?:$v['thumb'];
                    $v['type'] = 0;
                    if($v['is_specs']){
                        $min_price_info = Db::name('goods_price')
                            ->where('goods_id',$v['id'])
                            ->where('type',0)
                            ->order('price asc,id desc')
                            ->find();
                        $v['price'] = $min_price_info?$min_price_info['price']:'0.00';
                        $v['stock'] = $min_price_info?$min_price_info['stock']:0;
                        $v['weight'] = $min_price_info?$min_price_info['weight']:0;
                    }

//                    购物车数量
                    $cart_num = Db::name('user_cart')
                        ->where('user_id',$this->user->id)
                        ->where('goods_id',$v['id'])
                        ->where('type',0)
                        ->value('num');
                    $v['cart_num'] = $cart_num?:0;
                }
//                $v['weight'] = 'dd';
            }
        }
        return jsonReturn(1,'获取首页商品列表',$goods);
    }
}