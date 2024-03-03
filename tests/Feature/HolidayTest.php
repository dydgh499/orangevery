<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class HolidayTest extends TestCase
{
    public function testInvoke()
    {
        $next_year = (int)Carbon::now()->format('Y') + 1;
        $url = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';
        $params = [
            'ServiceKey' => urldecode(env('OPEN_API_ENC_KEY')),
            'solYear'	 => $next_year,
            'solMonth'	=> sprintf("%02d", $month),
        ];
        $res = get($url, $params);
    }
}
