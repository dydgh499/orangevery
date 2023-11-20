<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\PaymentModule;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_that_true_is_true()
    {
        $pay_modules = PaymentModule::where('mcht_id', 94889)
            ->where('module_type', 1)
            ->get([
                'id',
                'is_old_auth',
                'installment',
                'pay_year_limit',
                'pay_month_limit',
                'pay_day_limit',
                'pay_single_limit',
            ])
            ->with(['PayLimitAmount']);
        print_r($pay_modules->toArray());
        $this->assertTrue(true);
    }
}
