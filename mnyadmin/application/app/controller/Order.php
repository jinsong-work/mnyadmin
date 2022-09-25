<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/23
 * Time: 9:38
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

/**
 * Class UserOrder
 * @package app\app\controllerd
 * 订单数据
 */
class Order extends AppBase
{
    protected $noNeedLogin = [];

    public function createOrder(){
        $goods_id = $this->request->param('goods_id');
        $specs = $this->request->param('specs');
        $buy_num = $this->request->param('buy_num');
        model('app/UserOrder')->createOrder($this->user->id,$goods_id,$specs,$buy_num);
    }

    /**
     * 获取提货人列表
     */
    public function getPickerList(){
        $page = $this->request->param('page',1);
        $data = model('app/UserPicker')->getPageList($page,[],'id,name,phone',10,'id desc');
        return jsonReturn(1,'获取提货人列表',$data);
    }

    /**
     * 添加和修改提货人
     */
    public function addEditPicker(){
//        Db::startTrans();
        $id = $this->request->param('id',0);
        $name = $this->request->param('name','');
        $phone = $this->request->param('phone','');
        $is_default = $this->request->param('is_default',1);
        $result = $this->validate($this->request->param(),'app\app\validate\UserPicker');
        if(true !== $result) return jsonReturn(0,$result);

        if($is_default) Db::name('user_picker')->where('user_id',$this->user->id)->update(['is_default'=>0]);
        //修改
        if($id){
            $res = Db::name('user_picker')->where('user_id',$this->user->id)->update(compact('name','phone','is_default'));
            if(!$res) return jsonReturn(0,'修改失败');
            return jsonReturn(1,'修改成功');
        }else{
            $res = Db::name('user_picker')->insertGetId(['name'=>$name,'phone'=>$phone,'is_default'=>$is_default,'user_id'=>$this->user->id]);
            if(!$res) return jsonReturn(0,'添加失败');
            return jsonReturn(1,'添加成功');
        }
    }

    /**
     * 订单列表
     */
    public function orderList(){

    }
}