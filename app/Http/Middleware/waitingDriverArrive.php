<?php
namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class waitingDriverArrive
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
        //如果订单没有被接
        if ($order) {
            if($order['isAccept']==2){
                return $next($request);
            }
        }
        return response()->json([
            'code' => '58003',
            'message' => 'NO order waiting for driver arrive,You have can not use this API',
            'data' => '',
        ]);
    }
}
