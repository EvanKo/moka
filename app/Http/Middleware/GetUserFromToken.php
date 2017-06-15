<?php
namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class GetUserFromToken
{
    public function handle($request, Closure $next)
    {
        $auth = JWTAuth::parseToken();
        if (!$token = $auth->setRequest($request)->getToken()) {
            return response()->json([
                'code' => '',
                'message' => 'token_not_provided',
                'data' => '',
            ]);
        }
        try {
            $user = $auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'code' => '',
                'message' => 'token_expired',
                'data' => '',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'code' => '',
                'message' => 'token_invalid',
                'data' => '',
            ]);
		}
		$role = JWTAuth::toUser($token);
		        $login = $role['login'];
								          $object = DB::table('Roles')
											                      ->where('id', $role['id']);
					        if (!strpos($role['lastest'],date('y-m-d',time()))) {
										            $login = $login+1;
										            $object->update(['login' => $login]);
													          $object->update(['lastest' => date('y-m-d',time())]);
													        }
		          $fans = $role['fans'];
		          $fee = $role['fee'];
				            $value = $login + $fans + 2 * $fee;
				            $object->update(['value' => $value]);
							        return $next($request);
    }
}
