<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/20
 * Time: 16:02
 */

namespace app\shop\controller;
use app\shop\model\Delivery as DeliveryModel;
use app\common\controller\AdminBase;
use think\Db;

class Delivery extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
        $this->modelClass = new DeliveryModel;
    }

    /**
     * 配送点管理
     */
    public function index(){
        $region_id = $this->request->get('id');

        if ($this->request->isAjax()) {
            list($page, $limit, $where) = $this->buildTableParames();
            $_list                      = $this->modelClass->where($where)->order(['id' => 'desc'])->page($page, $limit)->select();
            foreach ($_list as $k => &$v) {
//                $v['name'] = $v['province_name'].'-'.$v['city_name'].'-'.$v['area_name'];
            }
            unset($v);
            $total  = $this->modelClass->where($where)->count();
            $result = array("code" => 0, "count" => $total, "data" => $_list);
            return json($result);
        }else{
            $this->assign('region_id',$region_id);
            return $this->fetch();
        }
    }

    /**
     * 添加
     */
    public function add(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            $param['delivery_time'] = json_encode($param['delivery_time']);


            Db::name('delivery')->strict(false)->insert($param);
        }else{
            $region_id = $this->request->get('region_id');
            var_dump($region_id);die;
            //取地址中的所有省
            $province = model('admin/Address')->getAddress(0);
            $this->assign('province',$province);
            return $this->fetch();
        }
    }

    public function edit(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            var_dump($param['delivery_time']);die;
        }else{
            //取地址中的所有省
            $province = model('admin/Address')->getAddress(0);
            $this->assign('province',$province);
            return $this->fetch();
        }
    }

    /**
     * @return \think\response\Json
     *
     * 获取省市区
     */
    public function selectSsq(){
        $pid = $this->request->param('pid');
        $lists = model('admin/Address')->getAddress($pid);
        return json(['code'=>1,'msg'=>'获取成功','data'=>$lists]);
    }
}