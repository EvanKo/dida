<?php
namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class beforeOrder
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
        //如果没有订单
        if (!$order) {
          return $next($request);
        }
        return response()->json([
            'code' => '58004',
            'message' => 'You have unfinish order, can't use this API',
            'data' => '',
        ]);
    }
}
