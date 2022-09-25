<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/19
 * Time: 10:56
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;

class User extends AppBase
{
    protected $noNeedLogin = ['getUserAgreement', 'getUserPrivacy', 'register', 'login','forgetPwd'];

    /**
     * 退出登录
     */
    public function logout(){
        if($this->user->logout()){
            return jsonReturn(1,'退出成功');
        }else{
            return jsonReturn(0,'退出失败');
        }
    }

    /**
     * 忘记密码
     */
    public function forgetPwd(){
        $post = $this->request->post();
        try{
            //验证器位置
            $result = $this->validate($post,'app\app\validate\User.forget');
            if(true!== $result) throw new ValidateException($result);
            $this->user->forgetPwd($post);
            return jsonReturn(1,'密码修改成功');
        }catch(\Exception $e){
            return jsonReturn(0,$e->getMessage().'-'.$e->getLine());
        }
    }

    /**
     * 获取用户协议
     */
    public function getUserAgreement(){
        $member_config = cache('Member_Config');

        return jsonReturn(1,'获取用户协议成功',$member_config['agreement']);
    }

    /**
     * 获取隐私协议
     */
    public function getUserPrivacy(){
        $member_config = cache('Member_Config');

        return jsonReturn(1,'获取隐私协议成功',$member_config['privacy']);
    }


    //用户注册
    public function register(){
        $post = $this->request->post();

        try{
            //验证器位置
            $result = $this->validate($post,'app\app\validate\User.register');
            if(true!== $result) throw new ValidateException($result);
            $res = $this->user->register($post);
            return jsonReturn(1,'注册并登录成功',$res);
        }catch (\Exception $e){
            return jsonReturn(0,$e->getMessage().'-'.$e->getLine());
        }
    }

    /**
     * 用户登录
     */
    public function login(){
        $post = $this->request->post();

        try{
            //验证器位置
            $result = $this->validate($post,'app\app\validate\User.login');
            if(true!== $result) throw new ValidateException($result);
            $res = $this->user->login($post);
            return jsonReturn(1,'登录成功',$res);
        }catch(\Exception $e){
            return jsonReturn(0,$e->getMessage().'-'.$e->getLine());
        }
    }
}