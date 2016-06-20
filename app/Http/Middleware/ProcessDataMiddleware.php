<?php

namespace App\Http\Middleware;

use Closure;

class ProcessDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // lay du lieu
        $data = $request->input('data');
        $data = (array) json_decode($data);

        // nếu có history thì lưu thêm không thì tạo mới
        if ($request->session()->has('history')) {
            $history = $request->session()->get('history');
            app('BuildWorld')->writeInfoLog("======== DAY ".(count($history) +1)." =========");
            array_unshift($history, $data);
            $request->session()->put('history', $history);

        }else{
            $newArray[] = $data;
            $request->session()->put('history', $newArray);
            app('BuildWorld')->writeInfoLog("======== DAY 1 =========");
        }


        return $next($request);
    }
}
