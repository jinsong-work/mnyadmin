<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/23
 * Time: 16:23
 */

namespace app\app\validate;

use think\Validate;
class UserPicker extends Validate
{
    protected $rule = [
        'phone|手机号'=>'require|mobile',
        'name|姓名'=>'require'
    ];
}