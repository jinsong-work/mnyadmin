<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2019/10/12
 * Time: 16:53
 */

namespace app\index\job;

use think\Db;
use think\queue\job;
class Hello
{

    /**
     * @param job $job 当前的任务对象
     * @param $data         发布任务时自定义的数据
     */
    public function fire(job $job, $data)
    {


        $isJobDone = $this->doHelloJob($data);
        if ($isJobDone) {
            //如果任务执行成功， 记得删除任务
            print_r("<info>Hello Job has been done and deleted" . "</info>\n");
            $job->delete();
        } else {
            if ($job->attempts() > 3) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print_r("<warn>Hello Job has been retried more than 3 times!" . "</warn>\n");
                $job->delete();
                // 也可以重新发布这个任务
                //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                //$job->release(2); //$delay为延迟时间，表示该任务延迟2秒后再执行
            }
        }
    }

    public function failed($data)
    {//发送失败写入系统日志
        cache('failtime', time());

    }


    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data)
    {
        return true;
    }


    /**
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function doHelloJob($data)
    {
        // 根据消息中的数据进行实际的业务处理...
        file_put_contents('queue_work_success.txt', $data, FILE_APPEND);
        /*$info = [
            'user_name' => $data['bizId'],
        ];
        $res = Db::name('user')->insert($info);
        if( $res ){
            return true;
        }else{
            return false;
        }*/

    }
}