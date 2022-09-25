<?php
/**
 * Created by PhpStorm.
 * User: 金松
 * Date: 2022/9/25
 * Time: 1:19
 */

namespace app\index\job;


class Demo
{
    public function fire(Job $job,$data){
        //将队列删除
//        $job->delete();

        if(true){
//            任务执行成功就删除队列中的任务
            $job->delete();
            print("--删除队列--");
        }else{
            //查询这个任务重试了几次，重试三次后去删除队列
            if($job->attempts() > 3){
                print("--队列重试了三次以上--");
                $job->delete();
            }
        }
    }
}