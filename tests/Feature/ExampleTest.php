<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Controllers\Utils\Comm;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_interacting_with_headers()
    {
        $res = $this->post('/api/v1/ezpg/sign-in', [
            'user_name' => 'ezob1229',
            'user_pw' => 'jyh0409Kr@',
        ]);
        if($res->getStatusCode() === 200)
        {
            $access_token = json_decode($res->getContent(), true)['access_token'];
            $res = Comm::post('/api/v1/ezpg/reconciliation', [
                'page' => '1',
                'page_size' => '20',
                's_dt' => '2024-07-01 00:00:00',
                'e_dt' => '2024-10-01 00:00:00',
            ], [
                "Bearer $access_token",
            ]);
            dump($res);
        }
        $res->assertStatus(200);
    }
}
