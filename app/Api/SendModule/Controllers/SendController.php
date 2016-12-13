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
    /*
    *author:824315402@qq.com
    *
    *
    */
    public function carNum(Request $request)
    {
        $query = new UserPostController();
        $result = $query->carNum($request);
        return $result;
    }
    public function price(Request $request)
    {
        return 19;
    }
    public function orderPush(Request $request)
    {
        $userphone = $request->get('userphone');
        $userphone_save = Redis::sadd('userphone', $userphone);
        //获得出发
        $fromWho = $request->get('fromWho');
        //获得出发地经纬度
        $fromPosition = $request->get('fromPosition');
        //获得目的
        $toWho = $request->get('toWho');
        //获得目的地经纬度
        $toPositon = $request->get('toPositon');
        //
        $type = $request->get('type');
        $isAccept = 0;
        $query = Redis::hmset(
            'send:'.$userphone,
            'fromWho',
            $fromWho,
            'fromPosition',
            $fromPosition,
            'toWho',
            $toWho,
            'toPositon',
            $toPositon,
            'type',
            $type,
            'isAccept',
            $isAccept
        );
        if ($query) {
            $result = $this->returnMsg('200', 'ok');
            return response()->json($result);
        } else {
            $result = $this->returnMsg('500', 'save order fail');
            return response()->json($result);
        }
    }
}
