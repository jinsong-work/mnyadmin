<?php
// +----------------------------------------------------------------------
// | 商城设置
// +----------------------------------------------------------------------
namespace app\shop\controller;

use app\admin\model\Module as Module_Model;
use app\common\controller\AdminBase;

class Setting extends AdminBase
{
    //cms设置
    public function index()
    {
        if ($this->request->isPost()) {
            $setting         = $this->request->param('setting/a');
            $data['setting'] = serialize($setting);
            if (Module_Model::update($data, ['module' => 'shop'])) {
                cache('Cms_Config', null);
                $this->success("更新成功！");
            } else {
                $this->success("更新失败！");
            }
        } else {
            $setting = Module_Model::where('module', 'shop')->value("setting");
            $this->assign("setting", unserialize($setting));
            return $this->fetch();
        }
    }
}
