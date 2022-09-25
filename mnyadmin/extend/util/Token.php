<?php
// +----------------------------------------------------------------------
// | 通用的jwt类
// +----------------------------------------------------------------------

namespace util;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Token
{
    const SECRET = 'mnyadmin';//密钥
    //创建token

    public static function create_token($user=[])
    {
        if(empty($user)) return false;
        $key = self::SECRET;//加密使用的key，相当于盐值salt
        $token = array(
            'user'=>$user,
            'iss' => 'http://www.helloweba.net', //签发者
            'aud' => 'http://www.helloweba.net', //jwt所面向的用户
            'iat' => time(), //签发时间
            'nbf' => time(), //在什么时间之后该jwt才可用
            'exp' => time() + 86400*3, //生成的token的过期时间，这里为了方便测试设置1分钟,时效一天24小时。测试设置为30秒。
        );
        //以HS256方式加密且生成token
        $jwt = JWT::encode($token,$key,"HS256");
        return $jwt;
//        var_dump($jwt);die;
//        return json_encode($jwt);
    }

    //验证token
    public static function verify_token($token)
    {
        if(!$token) return false;
        $key = self::SECRET;//加密使用的key，相当于盐值salt,验证token的$key需要和生成token的$key保持一致
        try{
            $jwtAuth = json_encode(JWT::decode($token,new Key($key,"HS256")));
            $authInfo = json_decode($jwtAuth,true);
            return ['code'=>1,'msg'=>'token正常','data'=>$authInfo];
        }catch (ExpiredException $e){
            return ['code'=>0,'msg'=>'token过期','data'=>[]];
        }catch (\Exception $e){
//            return json_encode($e->getMessage());
            return ['code'=>0,'msg'=>'token错误','data'=>[]];
        }
    }
}