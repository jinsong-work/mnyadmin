<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/23
 * Time: 9:23
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

/**
 * Class Message
 * @package app\app\controller
 * 消息通知
 */
class Message extends AppBase
{
    protected $noNeedLogin = ['myKefu'];
    /**
     * 我的客服
     */
    public function myKefu(){
        $data = cache('Shop_Config')['my_kefu'];

        return jsonReturn(1,'我的客服',$data);
    }
}