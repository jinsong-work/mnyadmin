<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/22
 * Time: 16:39
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

class Delivery extends AppBase
{
    /**
     * 选择配送点
     */
    public function selectDelivery(){
        // 启动事务
        Db::startTrans();

        $delivery_id = $this->request->param('delivery_id',0);
        $delivery = Db::name('delivery')->where('id',$delivery_id)->find();
        if(!$delivery) return jsonReturn(0,'该配送点不存在');
        if($delivery['status'] == '2') return jsonReturn(0,'该配送点暂停配送');

        //将用户的所有的当前配送点改为0；
        Db::name('user_delivery')->where('user_id',$this->user->id)->where('is_current',1)->update(['is_current'=>0]);
        //将选择的配送点设为当前配送点
        $user_delivery = Db::name('user_delivery')->where('delivery_id',$delivery_id)->where('user_id',$this->user->id)
            ->find();
        if($user_delivery){
            Db::name('user_delivery')->where('id',$user_delivery['id'])->update(['is_current'=>1]);
        }else{
            Db::name('user_delivery')->insertGetId(['user_id'=>$this->user->id,'delivery_id'=>$delivery_id,'is_current'=>1]);
        }

        // 提交事务
        Db::commit();
        return jsonReturn(1,'选择配送点成功');
    }
}