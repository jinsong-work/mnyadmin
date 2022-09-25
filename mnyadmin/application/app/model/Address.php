<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/21
 * Time: 13:27
 */

namespace app\app\model;
use think\Model;
use think\Db;
use app\common\model\AppBase;

class Address extends AppBase
{
    public function getAddressId($post){
        $province_id = Db::name('address')->where('name',$post['province_name'])->value('id');
        if(!$province_id) $province_id = Db::name('address')->insertGetId(['name'=>$post['province_name'],'parentid'=>0]);
        $city_id = Db::name('address')->where('name',$post['city_name'])->value('id');
        if(!$city_id) $city_id = Db::name('address')->insertGetId(['name'=>$post['city_name'],'parentid'=>$province_id]);
        $area_id = Db::name('address')->where('name',$post['area_name'])->value('id');
        if(!$area_id) $area_id = Db::name('address')->insertGetId(['name'=>$post['area_name'],'parentid'=>$city_id]);

        return compact('province_id','city_id','area_id');
    }
}