<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/19
 * Time: 11:15
 */

namespace app\app\model;
use think\Model;
use app\common\model\AppBase;

class User extends AppBase
{
    protected $sex_enum = [1=>'男',2=>'女'];
    public function getSexEnum(){
        return $this->sex_enum;
    }
}