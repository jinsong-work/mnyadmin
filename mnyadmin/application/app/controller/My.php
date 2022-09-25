<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/9/20
 * Time: 11:13
 */

namespace app\app\controller;
use app\common\controller\AppBase;
use think\Model;
use util\Token;
use think\exception\ValidateException;
use think\Db;

class My extends AppBase
{
    protected $noNeedLogin = ['aboutWe','contactWe'];

    /**
     * 提交反馈
     */
    public function submitFeedback(){
        $content = $this->request->param('content');
        $res = Db::name('user_feedback')->insertGetId(['content'=>$content,'add_time'=>time(),'user_id'=>$this->user->id]);
        if(!$res) return jsonReturn(0,'提交失败');
        return jsonReturn(1,'感谢你的反馈！');
    }

    /**
     * 我的钱包
     */
    public function getMyAmount(){
        $page = $this->request->param('page',1);
        $add_reduce = $this->request->param('add_reduce',0);
        $where[] = ['type','=',1];
        if($add_reduce) $where[] = ['add_reduce','=',$add_reduce];
        $lists = model('app/UserDynamic')->getPageList($page,$where,'*',10,'create_time desc,id desc');
        if($lists){
            foreach ($lists as &$v){
                switch ($v['from_type']){
                    case '1':
                        $v['order_sn'] = Db::name('user_order')->where('id',$v['bind_id'])->value('order_sn');
                        break;
                }
            }
        }

        $data['lists'] = $lists;
        $data['point'] = $this->user->amount;
        return jsonReturn(1,'我的钱包',$data);
    }

    /**
     * 我的积分
     */
    public function getMyPoint(){
        $page = $this->request->param('page',1);
        $add_reduce = $this->request->param('add_reduce',0);
        $where[] = ['type','=',2];
        if($add_reduce) $where[] = ['add_reduce','=',$add_reduce];
        $lists = model('app/UserDynamic')->getPageList($page,$where,'*',10,'create_time desc,id desc');
        if($lists){
            foreach ($lists as &$v){
                switch ($v['from_type']){
                    case '1':
                        $v['order_sn'] = Db::name('user_order')->where('id',$v['bind_id'])->value('order_sn');
                        break;
                }
            }
        }

        $data['lists'] = $lists;
        $data['point'] = $this->user->point;
        return jsonReturn(1,'我的积分',$data);
    }

    /**
     * @return \think\response\Json
     * 关于我们
     */
    public function aboutWe(){
        $data = cache('Shop_Config')['about'];

        return jsonReturn(1,'关于我们',$data);
    }

    /**
     * @return \think\response\Json
     * 联系我们
     */
    public function contactWe(){
        $data = cache('Shop_Config')['contact'];

        return jsonReturn(1,'联系我们',$data);
    }

    /**
     * 设置支付密码
     * ['']
     */
    public function setPayPassword(){

    }

    /**
     * 我的红包列表
     */
    public function myRedbao(){
//        var_dump($this->user->id,$this->region_id);die;
        $page = $this->request->param('page');
        $lists = Db::name('user_redbao')
            ->alias('t1')
            ->leftJoin('redbao t2','t1.redbao_id = t2.id')
            ->where('t1.region_id',$this->region_id)
            ->where('t1.user_id',$this->user->id)
            ->field('t2.*,t1.get_time,t1.expire_time,t1.status')
            ->withAttr('get_time',function($value,$data){
                return date('Y-m-d',$value);
            })
            ->withAttr('expire_time',function($value,$data){
                return date('Y-m-d',$value);
            })
            ->page($page,10)
            ->select();
        return jsonReturn(1,'我的红包列表',$lists);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 删除红包
     */
    public function delRedbao(){
        $ids = $this->request->param('ids');
        $map[] = ['user_id','=',$this->user->id];
        $map[] = ['id','in',$ids];
        $map[] = ['region_id','=',$this->region_id];
        $del_lists = Db::where('user_redbao')->where($map)->select();
        if(!$del_lists) return jsonReturn(0,'无红包可删除');
        foreach ($del_lists as $v){
            if($v['status'] == '1'){
                return jsonReturn(0,'未使用红包不可删除');
                break;
            }
        }
        return jsonReturn(1,'删除成功');
    }

    /**
     * 修改手机号
     */
    public function editPhone(){
        $post = $this->request->post();
        if(!isset($post['type'])) return jsonReturn(0,'type参数未传');
        if($post['type']=='1'){
            //验证短信验证码

            return jsonReturn(1,'短信验证成功');
        }elseif($post['type'] == '2'){
            try{
                $result = $this->validate($post,'app\app\validate\User.edit_phone');
                if(true !== $result) throw new ValidateException($result);
                //验证短信验证码
                if($post['phone'] == $this->user->phone) return jsonReturn(0,'手机号未改变');

                $is_exist = Db::name('user')->where('phone',$post['phone'])->count();
                if($is_exist) return jsonReturn(0,'手机号已被使用');
                $res = Db::name('user')->where('id',$this->user->id)->update(['phone'=>$post['phone']]);
                if(!$res) return jsonReturn(0,'修改失败');
                return jsonReturn(1,'修改成功',$post['phone']);
            }catch (\Exception $e){
                return jsonReturn(0,$e->getMessage());
            }
        }
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(){
        $data = Db::name('user')
            ->where('id',$this->user->id)
            ->withAttr('phone',function($value,$data){
                return strreplace($value,3,4);
            })
            ->field('avatar,nickname,sex,birthday,phone,point,amount')
            ->find();
        return jsonReturn(1,'获取用户信息',$data);
    }

    /**
     * 设置
     */
    public function setting(){
        $update_data = [];
        $avatar = $this->request->post('avatar','');
        if($avatar) $update_data['avatar'] = $avatar;

        $nickname = $this->request->post('nickname','');
        if($nickname) $update_data['nickname'] = $nickname;

        $sex = $this->request->post('sex','');
        if($sex) $update_data['sex'] = $sex;

        $birthday = $this->request->post('birthday','');
        if($birthday) $update_data['birthday'] = strtotime($birthday);

        $res = Db::name('user')->where('id',$this->user->id)->update($update_data);
        if(!$res) return jsonReturn(0,'数据未做任何修改');
        return jsonReturn(1,'设置成功');
    }
}