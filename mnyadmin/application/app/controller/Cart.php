<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/23
 * Time: 9:36
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

class Cart extends AppBase
{
    protected $noNeedLogin = [];

    /**
     * 获取购物车商品列表
     */
    public function getCartGoods(){
        try{
            $page = $this->request->param('page',1);
            $where[] = ['user_id','=',$this->user->id];
            $data = model('app/UserCart')->getUserCartPageGoods($page,$where,'t1.*,t2.goods_name');

            return jsonReturn(1,'获取购物车商品列表',$data);
        }catch (\Exception $e){
            return jsonReturn(0,$e->getMessage().'-'.$e->getLine());
        }
    }
}