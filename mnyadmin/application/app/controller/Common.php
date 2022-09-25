<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/19
 * Time: 14:43
 */

namespace app\app\controller;
use app\common\controller\AppBase;
//use app\model\Delivery as DeliveryModel;

class Common extends AppBase
{
    protected $noNeedLogin = ['*'];

    /**
     * @return \think\response\Json
     * 获取配送点状态枚举
     */
    public function deliverStatusEnum(){
        $arr = model('app/Delivery')->deliverStatusEnum();
        return jsonReturn(1,'获取配送点状态枚举',$arr);
    }

    /**
     * 获取性别枚举
     */
    public function getSexEnum(){
        $data = model('app/User')->getSexEnum();

        return jsonReturn(1,'获取性别枚举',$data);
    }

    /**
     * 获取短信验证码
     */
    public function getShortCode(){

        return jsonReturn(1,'获取成功',1234);
    }

    /**
     * 换取地址id值
     */
    public function getAddressId(){
        $post = $this->request->post();
        try{
            $res = model('app/Address')->getAddressId($post);
            return jsonReturn(1,'换取地址id值',$res);
        }catch (\Exception $e){
            return jsonReturn(0,$e->getMessage());
        }
    }
}