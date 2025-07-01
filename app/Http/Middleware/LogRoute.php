<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Ablilty\Ablilty;

class LogRoute
{
    private function masking($text, $rate=0.8)
    {
        $mask_size = round(strlen($text) * $rate);
        $mask_text = substr($text, 0, strlen($text) - $mask_size);
        for ($i=0; $i < $mask_size; $i++) 
        {
            $mask_text .= '*';
        }
        return $mask_text;
    }

    private function setSensitiveFormat($data)
    {
        if(isset($data['current_pw']))
            $data['current_pw'] = $this->masking($data['current_pw']);
        if(isset($data['user_pw']))
            $data['user_pw'] = $this->masking($data['user_pw']);
        if(isset($data['card_num']))
            $data['card_num'] = $this->masking($data['card_num']);
        if(isset($data['auth_num']))
            $data['auth_num'] = $this->masking($data['auth_num']);
        if(isset($data['card_pw']))
            $data['card_pw'] = $this->masking($data['card_pw']);
        if(isset($data['yymm']))
            $data['yymm'] = $this->masking($data['yymm']);
        return $data;
    }

    private function getLogFormat($request)
    {
        $user = $request->user();
        $logs = [
            'ip'    => $request->ip(),
            'method'=> $request->method(),
            'user-agent'=> $request->header('User-Agent'),
            'login' => [],
        ];
        if(isset($user))
        {
            $logs['login']['user_name'] = $user->user_name;
            if(Ablilty::isOperator($request))
                $logs['login']['level'] = $user->level;
        }
        $logs['input'] = $request->all();
        $logs['login'] = $this->setSensitiveFormat($logs['login']);
        $logs['input'] = $this->setSensitiveFormat($logs['input']);
        return $logs;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $url = url()->full();
        try
        {
            if($request->is('/build/assets/*'))
                return $next($request);
            else
            {
                $logs = $this->getLogFormat($request);
                Log::info($url, $logs);    
            }
        }
        catch(Exception $ex)
        {
            Log::error($url, ['msg'=>$ex->getMessage()]);
        }
        return $next($request);
    }
}
