<?php

namespace App\Api\SendModule\Controllers;

use Illuminate\Http\Request;
use App\Api\Controllers\BaseController;
use App\Api\GoModule\Controllers\UserPostController;
use Illuminate\Support\Facades\Redis;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class SendController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    *@author 824315402@qq.com
    *
    *return the number of cars when user needs to create a order
    *
    *@return $result
    */
    public function carNum(Request $request)
    {
        $query = new UserPostController();
        $result = $query->carNum($request);
        return $result;
    }
    /**
    *return the probable price
    *
    *@param distance
    */
    public function price(Request $request)
    {
        //temp
        $query = new UserPostController();
        $result = $query->price($request);
        return $result;
    }
    /**
    *to save and push order to drivers when the user confirm to create a order
    *
    *
    *@param string $userphone
    *@return json $result about status
    */
    public function orderPush(Request $request)
    {
        //save userphone in Redis
        $userphone = $request->get('userphone');
        $userphone_save = Redis::sadd('userphone', $userphone);
        //get the sender info
        $fromWho = $request->get('fromWho');
        //get the position of sender
        $fromPosition = $request->get('fromPosition');
        //get the receiver info
        $toWho = $request->get('toWho');
        //get the receiver positon
        $toPosition = $request->get('toPosition');
        //get the item type
        $type = $request->get('type');
        //default unaccepted
        $isAccept = 0;
        //save in Redis
        $query = Redis::hmset(
            'send:'.$userphone,
            'fromWho',
            $fromWho,
            'fromPosition',
            $fromPosition,
            'toWho',
            $toWho,
            'toPosition',
            $toPosition,
            'type',
            $type,
            'isAccept',
            $isAccept
        );
        if ($query) {
           /* // 建立socket连接到内部推送端口
            $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
            // 推送的数据，包含driverPhone字段，表示是给这个driverPhone推送
            $data = array(
                'send:userphone'=> $userphone,
                'fromWho'       => $fromWho,
                'fromPosition'  => $fromPosition,
                'toWho'         => $toWho,
                'toPosition'     => $toPosition,
                'type'          => $type,
                );
            // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
            fwrite($client, json_encode($data)."\n");
            // 读取推送结果
            echo fread($client, 8192);*/
            $result = $this->returnMsg('200', 'ok');
            return response()->json($result);
        } else {
            $result = $this->returnMsg('56001', 'save order fail');
            return response()->json($result);
        }
    }
}
