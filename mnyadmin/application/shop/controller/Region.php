<?php
namespace app\shop\controller;
use app\common\controller\AdminBase;
use app\shop\model\Region as RegionModel;
use app\shop\model\Delivery as DeliveryModel;

class Region extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
        $this->modelClass = new RegionModel;
        $this->DeliveryModel = new DeliveryModel;
    }

    public function index(){
        if ($this->request->isAjax()) {
            list($page, $limit, $where) = $this->buildTableParames();
            $_list                      = $this->modelClass->where($where)->order(['id' => 'desc'])->page($page, $limit)->select();
            foreach ($_list as $k => &$v) {
                $v['name'] = $v['province_name'].'-'.$v['city_name'].'-'.$v['area_name'];
            }
            unset($v);
            $total  = $this->modelClass->where($where)->count();
            $result = array("code" => 0, "count" => $total, "data" => $_list);
            return json($result);
        }
        return $this->fetch();
    }
}