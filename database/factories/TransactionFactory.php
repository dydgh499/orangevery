<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Models\PaymentSection;
use App\Models\Classification;
use App\Models\Transaction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function mcht()
    {
        $mcht = Merchandise::where('brand_id', 1)->inRandomOrder()->first();
        $mcht = json_decode(json_encode($mcht), true);
        $keys = [
            'brand_id',
            'sales5_id',
            'sales5_fee',
            'sales4_id',
            'sales4_fee',
            'sales3_id',
            'sales3_fee',
            'sales2_id',
            'sales2_fee',
            'sales1_id',
            'sales1_fee',
            'sales0_id',
            'sales0_fee',
            'hold_fee',
            'custom_id',
        ];
        $data = [];
        $data['mcht_id'] = $mcht['id'];
        $data['mcht_fee'] = $mcht['trx_fee'];
        for ($i=0; $i < count($keys) ; $i++)
        {
            $data[$keys[$i]] = $mcht[$keys[$i]];
        }
        return $data;
    }

    public function paymod($mcht_id)
    {
        $paymod = PaymentModule::where('mcht_id', $mcht_id)->inRandomOrder()->first();
        $paymod = json_decode(json_encode($paymod), true);
        $keys = [
            'module_type',
            'pg_id',
            'ps_id',
            'terminal_id',
            'mid',
        ];
        $data = [];
        $data['pmod_id'] = $paymod['id'];
        $data['tid'] = $paymod['tid'];
        $data['mcht_settle_type'] = $paymod['settle_type'];
        $data['mcht_settle_fee'] = $paymod['settle_fee'];
        for ($i=0; $i < count($keys) ; $i++)
        {
            $data[$keys[$i]] = $paymod[$keys[$i]];
        }
        return $data;
    }

    public function ps($ps_id)
    {
        $data = [];
        $ps = PaymentSection::where('id', $ps_id)->first();
        $data['ps_fee']  = $ps->trx_fee/100;
        return $data;
    }

    public function common()
    {
        $data = [];

        return $data;
    }
    public function definition()
    {
        $cnt = Transaction::all()->count();
        $data = $this->mcht();
        $data = array_merge($data, $this->paymod($data['mcht_id']));
        $data = array_merge($data, $this->ps($data['ps_id']));
        if($cnt > 90 && rand(0, 1))
        {
            $trx = Transaction::all()->random();
            $data['is_cancel'] = true;
            $data['cxl_dt'] = date('2023-06-d');
            $data['cxl_tm'] = date('H:i:s');
            $data['ori_trx_id'] = $trx->trx_id;

            $data['trx_dt'] = $trx->trx_dt;
            $data['trx_tm'] = $trx->trx_tm;
            $data['ord_num']    = $trx->ord_num;
            $data['trx_id']     = $trx->trx_id;
            $data['card_num']   = $trx->card_num;
            $data['installment'] = $trx->installment;
            $data['issuer']     = $trx->issuer;
            $data['acquirer']   = $trx->acquirer;
            $data['appr_num']   = $trx->appr_num;
            $data['buyer_name'] = $trx->buyer_name;
            $data['buyer_phone'] = $trx->buyer_phone;
            $data['item_name']  = $trx->item_name;
            $data['amount'] = $trx->amount * -1;
            $data['mcht_settle_fee']  = $data['mcht_settle_fee'] * -1;
        }
        else
        {
            $data['is_cancel'] = false;
            $data['cxl_dt'] = null;
            $data['cxl_tm'] = null;
            $data['ori_trx_id'] = null;
            $data['amount'] = rand(100, 999999);
            $data['trx_dt'] = date('2023-06-d');
            $data['trx_tm'] = date('H:i:s');
            $data['ord_num']    = $this->faker->isbn10();
            $data['trx_id']     = $this->faker->unique->isbn13();
            $data['card_num']   = '1234-56******-1234';
            $data['installment'] = rand(2, 11);
            $data['issuer']     = '비씨';
            $data['acquirer']   = '비씨';
            $data['appr_num']   = $this->faker->randomNumber();
            $data['buyer_name'] = $this->faker->name();
            $data['buyer_phone'] = $this->faker->phoneNumber();
            $data['item_name']  = $this->faker->sentence();
        }
        return $data;
    }
}
