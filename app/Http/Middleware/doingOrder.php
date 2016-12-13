<?php
namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class doingOrder
{
  /**
   *@author Arius
   *@function tel validate
   *
   *
   */
    public function handle($request, Closure $next)
    {
        $user=JWTAuth::toUser();
        $order = Redis::hgetall("usecar:",$user['tel']);
        //订单正在进行
        if ($order) {
            if($order['isAccept']==3){
                return $next($request);
            }
        }
        return response()->json([
            'code' => '58002',
            'message' => 'NO order Doing,You have can not use this API',
            'data' => '',
        ]);
    }
}
