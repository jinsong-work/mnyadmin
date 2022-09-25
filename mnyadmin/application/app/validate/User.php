<?php
// +----------------------------------------------------------------------
// | 模型验证
// +----------------------------------------------------------------------
namespace app\app\validate;

use think\Validate;

class User extends Validate
{
    //定义验证规则
    protected $rule = [
        'phone|手机号' => 'require|mobile',
        'code|验证码'  => 'require',
        'password|密码'  => 'require|length:3,20',
        'province_name|省名称'=>'require',
        'city_name|市名称'=>'require',
        'area_name|区名称'=>'require',
        'new_password|新密码'=>'require|length:3,20',
    ];

    protected $scene = [
        'register'=>['phone','code','password'],
        'login'=>['phone','password'],
        'forget'=>['phone','code','new_password'],
        'edit_phone'=>['code','phone']
    ];

}
