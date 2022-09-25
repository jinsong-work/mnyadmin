<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/19
 * Time: 10:57
 */

namespace app\app\service;
use app\app\model\User as User_Model;
use util\Token;
use think\Db;

class User
{
    protected static $instance = null;
    protected $_error          = null;
    protected $_logined        = false;
    //当前登录会员详细信息
    protected $_user  = null;
    protected $_token = '';
    //Token默认有效时长
    protected $keeptime = 2592000;
    protected $_jwt = [];
    //默认配置
    protected $config      = [];
    protected $options     = [];
    protected $allowFields = ['id', 'username', 'nickname', 'mobile', 'avatar', 'point', 'amount'];

    public function __construct($options = [])
    {
//        if ($config = cache("Member_Config")) {
//            $this->config = array_merge($this->config, $config);
//        }
//        $this->options = array_merge($this->config, $options);
    }

//    /**
//     * 获取允许输出的字段
//     * @return array
//     */
//    public function getAllowFields()
//    {
//        return $this->allowFields;
//    }
//
//    /**
//     * 获取基本信息
//     */
//    public function getUserinfo()
//    {
//        $data        = $this->_user->toArray();
//        $allowFields = $this->getAllowFields();
//        $userinfo    = array_intersect_key($data, array_flip($allowFields));
//        $userinfo    = array_merge($userinfo, Token::get($this->_token));
//        return $userinfo;
//    }

    /**
     * 退出登录
     */
    public function logout(){
        $res = Db::name('user_token')->where('token',$this->_token)->delete();
        if(!$res) return false;
        return true;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getJwt()
    {
        return $this->_jwt;
    }

    /**
     * 兼容调用user模型的属性
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_user ? $this->_user->$name : null;
    }

    /**
     * 检验用户是否已经登陆
     */
    public function isLogin()
    {
        if ($this->_logined) {
            return true;
        }
        return false;
    }

    /**
     * 根据Token初始化
     *
     * @param string $token Token
     * @return boolean
     */
    public function init($token)
    {
        if ($this->_logined) {
            return true;
        }
        if ($this->_error) {
            return false;
        }
//        数据库中验证是否失效，有效期和jwt的一致+双重判断
        $expire_time = Db::name('user_token')->where('token',$token)->value('expire_time');
        if(!$expire_time) return false;

        if($expire_time <time()){
            $this->setError('登录过期，请重新登录。');
            return false;
        }

        //有效。但是可能jwt的失效
        $jwt = Token::verify_token($token);
        if(!$jwt['code']){
            $this->setError('token失效，请重新登录。');
            return false;
        }

        $user_id = intval($jwt['data']['user']);
        if ($user_id > 0) {
            $user = User_Model::get($user_id);
            if (!$user) {
                $this->setError('账户不存在');
                return false;
            }
            if ($user['status'] !== 1) {
                $this->setError('账户已经被锁定');
                return false;
            }
            $this->_user    = $user;
            $this->_logined = true;
            $this->_token   = $token;
            $this->_jwt = $jwt;
            return true;
        } else {
            $this->setError('你当前还未登录');
            return false;
        }
    }

    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return type
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     */
    public function match($arr = [])
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower(request()->action()), $arr) || in_array('*', $arr)) {
            return true;
        }
        // 没找到匹配
        return false;
    }

    /**
     * 获取示例
     * @param array $options 实例配置
     * @return static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     * 忘记密码
     */
    public function forgetPwd($post){
        $userinfo = Db::name('user')->where('phone',$post['phone'])->field('id,avatar,nickname,point,amount,password,status,encrypt')->find();
        if(!$userinfo) throw new \Exception('账号不存在');
        if(!$userinfo['status']) throw new \Exception('账号被禁用，请联系客服。');
        $passwordinfo = encrypt_password($post['new_password']); //对密码进行处理
        $res = Db::name('user')->where('phone',$post['phone'])->update(['password'=>$passwordinfo['password'],'encrypt'=>$passwordinfo['encrypt']]);
        if(!$res) throw new \Exception('密码修改失败');
    }

    public function login($post){
        $userinfo = Db::name('user')->where('phone',$post['phone'])->field('id,avatar,nickname,point,amount,password,status,encrypt')->find();
        if(!$userinfo) throw new \Exception('账号不存在');
        if(!$userinfo['status']) throw new \Exception('账号被禁用，请联系客服。');
        if ($userinfo['password'] != encrypt_password($post['password'], $userinfo['encrypt'])) {
            throw new \Exception('密码不正确');
        }
        unset($userinfo['password']);
//        登录
        $token = Token::create_token($userinfo['id']);
        //        这里存入到mysql中
        $res3 = Db::name('user_token')->insertGetId(['token'=>$token,'user_id'=>$userinfo['id'],'create_time'=>time(),'expire_time'=>time()+86400*7]);
        if(!$res3) throw new \Exception('登录失败');
        $res_arr = [
            'token'=>$token,
            'delivery'=>[],
            'unread_num'=>0,
            'userinfo'=>$userinfo
        ];
        return $res_arr;
    }

    public function register($post,$province_name='',$city_name='',$area_name=''){
        $is_exist = Db::name('user')->where('phone',$post['phone'])->count();
        if($is_exist) throw new \Exception('该手机号已被注册');

        //启用事务
        Db::startTrans();
//        var_dump($post);die;
        $passwordinfo = encrypt_password($post['password']); //对密码进行处理

        $user_id = Db::name('user')->insertGetId([
            'phone'=>$post['phone'],
            'password'=>$passwordinfo['password'],
            "encrypt"  => $passwordinfo['encrypt'],
            'nickname'=>genRandomString(),
            'sex'=>0,
            'point'=>0,
            'amount'=>0,
            'avatar'=>'/static/app/images/default_avatar.jpg',
            'reg_time'=>time(),
            'reg_province_name'=>$province_name,
            'reg_city_name'=>$city_name,
            'reg_area_name'=>$area_name,
            'status'=>1
        ]);
        if(!$user_id) throw new \Exception('注册失败');

//        var_dump($user_id);die;
        //获取定位所在的省市区与区域进行比对
//        $region_info = Db::name('region')
//            ->where('province_name',$province_name)
//            ->where('city_name',$city_name)
//            ->where('area_name',$area_name)
//            ->find();
//        $region_id = !empty($region_info)?$region_info['id']:0;

        //更新区域用户数量
//        if($region_id){
//            $res1 = Db::name('region')->where('id',$region_id)->setInc('user_num');
//            if(!$res1) throw new \Exception('注册失败');
//        }

//        $res2 = Db::name('user')->where('id',$user_id)->update(['region_id'=>$region_id]);
//        if(!$res2) throw new \Exception('注册失败');

        $token = Token::create_token($user_id);//7天时效
//        这里存入到mysql中
        $res3 = Db::name('user_token')->insertGetId(['token'=>$token,'user_id'=>$user_id,'create_time'=>time(),'expire_time'=>time()+86400*7]);
        if(!$res3) throw new \Exception('注册失败');
        // 提交事务
        Db::commit();
        $userinfo = Db::name('user')->where('id',$user_id)->field('id,avatar,nickname,point,amount')->find();

        $res_arr = [
            'token'=>$token,
            'delivery'=>[],
            'unread_num'=>0,
            'userinfo'=>$userinfo
        ];
        return $res_arr;
    }
}