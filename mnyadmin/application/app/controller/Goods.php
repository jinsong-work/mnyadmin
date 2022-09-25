<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/21
 * Time: 17:46
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

class Goods extends AppBase
{
    protected $noNeedLogin = ['goodsDetail','getGoodsPop','getSpecsPrice'];

    /**
     * 获取规格对应的价格和重量等
     */
    public function getSpecsPrice(){
        $goods_id = $this->request->param('goods_id');
        $type = $this->request->param('type');
        $specs = $this->request->param('specs','');

//        var_dump($specs);die;
        $goods_info = Db::name('goods')->where('id',$goods_id)->where('status',1)->find();
        if(!$goods_info) return jsonReturn(0,'商品不存在或已下架');
        $price_info = Db::name('goods_price')
            ->where('goods_id',$goods_id)
            ->where('item_id_group',$specs)
            ->where('type',$type)
            ->find();

        $data['price'] = $price_info['price'];
        $data['weight'] = $price_info['weight'].(model('app/Goods')->getWeightUnit());
        $data['stock'] = $price_info['stock'];
        //质选和特卖
        if($type){
            $zx_tm = Db::name('goods_item')
                ->where('type',$type)
                ->where('goods_id',$goods_id)
                ->find();
            $data['thumb'] = $zx_tm['thumb'];
        }else{
            $data['thumb'] = $goods_info['thumb'];
        }
        return jsonReturn(1,'获取规格对应的价格和重量等',$data);
    }

    /**
     * 获取商品规格弹窗
     */
    public function getGoodsPop(){
        $goods_id = $this->request->param('goods_id');
//        var_dump($goods_id);die;
        try{
            $specs = model('app/Goods')->getGoodsPop($goods_id);
            return jsonReturn(1,'获取商品规格弹窗',$specs);
        }catch(\Exception $e){
            return jsonReturn(0,$e->getMessage().'-'.$e->getLine());
        }
    }

    /**
     * 商品详情
     *
     *
     */
    public function goodsDetail(){
        $id = $this->request->param('id',0);
        try{
            $goods = Db::name('goods')
                ->where('id',$id)
                ->withAttr('banner',function($value,$data){
                    return [
                        [
                            'image'=>'https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fb-ssl.duitang.com%2Fuploads%2Fblog%2F201308%2F06%2F20130806220019_sjhz8.jpeg&refer=http%3A%2F%2Fb-ssl.duitang.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1666497204&t=8e995388f085a3319176c944c8442364'
                        ],
                        [
                            'image'=>'https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fb-ssl.duitang.com%2Fuploads%2Fblog%2F201308%2F06%2F20130806220019_sjhz8.jpeg&refer=http%3A%2F%2Fb-ssl.duitang.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1666497297&t=ef782e4886a391841572a591b465f55c'
                        ]
                    ];
                })
                ->field('id,region_id,is_select,goods_name,banner,content,is_specs,status')
                ->find();
            if(empty($goods)) throw new \Exception('商品不存在');
            if(!$goods['status']) throw new \Exception('商品已下架');
            $goods['pop'] = model('app/Goods')->getGoodsPop($goods['id']);
            //购物车数量
            $goods['cart_num'] = Db::name('user_cart')->where('user_id',$this->user->id)->where('goods_id',$goods['id'])->sum('num');
            return jsonReturn(1,'获取商品详情',$goods);
        }catch(\Exception $e){
            return jsonReturn(0,$e->getMessage().'-'.$e->getLine());
        }
    }



    /**
     * 加入到购物车
     */
    public function addReduceCart(){
        $add_reduce = $this->request->param('add_reduce');
        $goods_id = $this->request->param('goods_id');
        $type = $this->request->param('type');
        //选择规格后传，有规格的时候必传
        $specs = $this->request->param('specs','');
        try{
            $goods_info = Db::name('goods')->where('id',$goods_id)->where('status',1)->find();
            if(empty($goods_info)) throw new \Exception('该商品不存在或已下架');

            if($add_reduce=='1'){
                $goods_specs = Db::name('goods_specs')->where('goods_id',$goods_id)->select();
                if(!$specs && !empty($goods_specs)) throw new \Exception('该商品存在规格,无法直接加入。');

                $cart_num = model('app/Goods')->addCart($this->user->id,$goods_id,$type,$specs);
                return jsonReturn(1,'加入成功',$cart_num);
            }else{
                $cart_num = model('app/Goods')->reduceCart($this->user->id,$goods_id,$type,$specs);
                return jsonReturn(1,'减少成功',$cart_num);
            }
        }catch (\Exception $e){
            return jsonReturn(0,$e->getMessage());
        }
    }
}