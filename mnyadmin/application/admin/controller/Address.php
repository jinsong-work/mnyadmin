<?php

// +----------------------------------------------------------------------
// | 日志首页
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\AdminLog as AdminlogModel;
use app\common\controller\AdminBase;
use app\shop\model\Category as CategoryModel;
use think\Db;
use app\admin\model\Address as AddressModel;

class Address extends AdminBase
{
    protected function initialize()
    {
        parent::initialize();
        $this->modelClass = new AddressModel;
    }

    public function index(){
        if ($this->request->isAjax()) {
            $tree       = new \util\Tree();
            $tree->icon = array('', '', '');
            $tree->nbsp = '';
            $result     = AddressModel::order(array('listorder', 'id' => 'asc'))->select()->toArray();
            $tree->init($result);
            $_list  = $tree->getTreeList($tree->getTreeArray(0), 'name');
            $total  = count($_list);
            $result = array("code" => 0, "count" => $total, "data" => $_list);
            return json($result);
        }
        return $this->fetch();
    }

    public function add(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            $parentid = $this->request->param('parentid',0);

            $level = 1;
            if($parentid){
                $parent_info = Db::name('address')->where('id',$parentid)->find();
                if(empty($parent_info)) $this->error('父级城市不存在');
                $level = $parent_info['level'] + 1;
            }

            $param['level'] = $level;
            $address_id = Db::name('address')->strict(false)->insertGetId($param);
            if(!$address_id) $this->error('添加失败');
            $this->success('添加成功');
        }else{
            $tree   = new \util\Tree();
            $pid    = $this->request->param('parentid/d', 0);
            $result = AddressModel::order(array('listorder', 'id' => 'asc'))->select()->toArray();
            $tree->init($result);
            $select_categorys = $tree->getTreeAddress(0, '', $pid);
            $this->assign("select_categorys", $select_categorys);
            return $this->fetch();
        }
    }

    public function edit(){
        $tree   = new \util\Tree();
        $id     = $this->request->param('id/d', 0);
        $rs     = AddressModel::where(["id" => $id])->find();
        $result = AddressModel::order(array('listorder', 'id' => 'asc'))->select()->toArray();
        $array  = array();
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
            $array[]       = $r;
        }
        $tree->init($array);
        $select_categorys = $tree->getTreeAddress(0, '', $rs['parentid']);
        $this->assign("select_categorys", $select_categorys);
        return parent::edit();
    }
}