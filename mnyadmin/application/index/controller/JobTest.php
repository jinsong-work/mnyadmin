<?php

/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2019/10/12
 * Time: 16:47
 */

namespace app\index\controller;

//include_once "\addons\";
use think\Queue;

//require ADDON_PATH . 'queue/SDK/src/Queue.php';
class JobTest
{

    /**

     *   入队：项目域名/index.php/index/job_test/actionWithHelloJob
        数据处理：php think queue:work --queue helloJobQueue (单次执行) ||
    ​    php think queue:listen --queue helloJobQueue(一直会监听)
     */
    public function actionWithHelloJob(){
//        var_dump(ADDON_PATH);die;
        // 1.当前任务将由哪个类来负责处理。
        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        //$jobHandlerClassName  = 'application\index\job\Hello';  //这里千万不要写成这个，不然将会失败
        $jobHandlerClassName  = 'app\index\job\Hello';
        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName  	  = "helloJobQueue";
        // 3.当前任务所需的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串
        //   ( jobData 为对象时，需要在先在此处手动序列化，否则只存储其public属性的键值对)
        $jobData       	  = "jinsong\n";
        $isPushed = Queue::push( $jobHandlerClassName , $jobData, $jobQueueName );
        // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
        if( $isPushed !== false ){
            echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
        }else{
            echo 'Oops, something went wrong.';
        }
    }

}
