<?php
namespace app\index\controller;
use think\Controller;
use think\facade\Cache;
use think\Queue;

class Index extends Controller
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function test(){
        if($this->request->isAjax()){
            var_dump($this->request->param());die;
        }
        return $this->fetch();
    }

//    生成首字母头像
    public function createLetterAvatar(){

        echo "<img src='".letter_avatar('M')."'/>";die;
//        var_dump(letter_avatar('T'));die;
    }

    /**
     * 测试队列
     */
    public function test_queue(){
        //创建任务
//        当轮到该任务时，将会生成一个该类的实例，并调用fire方法
        $jobHandlerClassName = "app\index\job\Demo";

//        当前任务归属的队列名称，如果为新队列会自动创建
        $jobQueueName = "DemoJobQueue";

        //执行数据
        $jobData = ['ts'=>time()];

//        exit('333');

        var_dump(new Queue());die;
//        将该任务推送到消息队列，等待对应的消费者去执行
        $isPushed = Queue::push($jobHandlerClassName,$jobData,$jobQueueName);

//        $isPushed = new \addons\queue\Queue();

//        $isPushed = Queue::push()
//        var_dump($isPushed);die;
    }

    /**
     * 测试redis是否生效
     */
    public function test_redis(){
//        Cache::store('redis')->set('name','jinsong',35);
        $redis_name = Cache::store('redis')->get('name');
        var_dump($redis_name);die;
    }
}
