<?php


namespace app\common\controller;
use app\app\service\User;
use think\Db;

class AppBase extends Base
{
    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [];
    protected $user = null;
    protected $province_id=0;
    protected $city_id=0;
    protected $area_id = 0;
    protected $region_id=0;

    //初始化
    protected function initialize()
    {
        //根据头部省市区，赋值全局调用
        $this->province_id = request()->header("province");
        $this->city_id = request()->header("city");
        $this->area_id = request()->header("area");
        //查看是否存在该区域
        $region_map[] = ['province_id','=',$this->province_id];
        $region_map[] = ['city_id','=',$this->city_id];
        $region_map[] = ['area_id','=',$this->area_id];
        $this->region_id = (Db::name('region')->where($region_map)->value('id'))?:0;

        //直接获取user对象
        $this->user = User::instance();
        $token = request()->header("token");
        $path  = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
        //不是免登陆方法
        if(!$this->user->match($this->noNeedLogin, $path)){
            //初始化
            $this->user->init($token);
            //检测是否登录,统一放在需要登录的页面
            if (!$this->user->isLogin()) {
                echo json_encode(['code'=>-1,'msg'=>$this->user->getError() ?: '请先登录']);die;
            }
        }else{
//          有token才初始化数据
            if($token){
                //初始化
                $this->user->init($token);
            }
        }
    }


}