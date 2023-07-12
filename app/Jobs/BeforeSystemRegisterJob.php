<?php

namespace App\Jobs;

use App\Http\Traits\StoresTrait;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BeforeSystem\Brand;
use App\Http\Controllers\BeforeSystem\PaymentGateway;
use App\Http\Controllers\BeforeSystem\PaymentSection;
use App\Http\Controllers\BeforeSystem\Classification;

use App\Http\Controllers\BeforeSystem\Salesforce;
use App\Http\Controllers\BeforeSystem\Merchandise;
use App\Http\Controllers\BeforeSystem\PaymentModule;

use Illuminate\Support\Facades\Log;

class BeforeSystemRegisterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, StoresTrait;

    public $maxExceptions = 1;
    public $tries = 3;

    public $brand_id;
    public $before_brand_id;
    public $dns;

    public $paywell;
    public $payvery;

    public function __construct($brand_id, $before_brand_id, $dns)
    {
        $this->brand_id = $brand_id;
        $this->before_brand_id = $before_brand_id;
        $this->dns = $dns;
    }

    public function failed()
    {
        $this->payvery = DB::connection('mysql');
        $this->payvery->table('brands')->where('id', $this->brand_id)->update(['is_transfer'=>0]);
        setBrandByDNS($this->dns);
        Log::warning("before-system-register-job-fail", ['retry'=>$this->attempts()]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        logging([], 'before-system-register-job-start');
        $this->paywell = DB::connection('paywell');
        $this->payvery = DB::connection('mysql');
        $result = DB::transaction(function () {
            $brand = new Brand();
            $brand->getPaywell($this->paywell->table('service'), $this->brand_id, $this->before_brand_id);
            $brand->setPayvery($this->payvery->table('brands'), $this->brand_id);

            $pg = new PaymentGateway();
            $pg->getPaywell($this->paywell->table('agency_info'), $this->brand_id, $this->before_brand_id);
            $pg->setPayvery($this->payvery->table('payment_gateways'), $this->brand_id);

            $ps = new PaymentSection($pg->payvery, $pg->paywell_to_payvery);
            $ps->getPaywell($this->paywell->table('item_classification'), $this->brand_id, $this->before_brand_id);
            $ps->setPayvery($this->payvery->table('payment_sections'), $this->brand_id);

            $cfic = new Classification();
            $cfic->getPaywell($this->paywell->table('item_classification'), $this->brand_id, $this->before_brand_id);
            $cfic->setPayvery($this->payvery->table('classifications'), $this->brand_id);

            $sale = new Salesforce();
            $sale->getPaywell($this->paywell, $this->brand_id, $this->before_brand_id);
            $sale->setPayvery($this->payvery->table('salesforces'), $this->brand_id);
            logging(['sales'=>'ok'], 'before-system-register-job');

            $mcht = new Merchandise();
            $mcht->connectSalesInfo($sale->payvery, $sale->paywell_to_payvery);
            $mcht->connectClsInfo($cfic->payvery, $cfic->paywell_to_payvery);
            $mcht->getPaywell($this->paywell, $this->brand_id, $this->before_brand_id);
            $mcht->setPayvery($this->payvery->table('merchandises'), $this->brand_id);
            logging(['mcht'=>'ok'], 'before-system-register-job');

            $pmod = new PaymentModule($pg->pg_types);
            $pmod->connectPGInfo($pg->payvery, $pg->paywell_to_payvery, $ps->payvery, $ps->paywell_to_payvery);
            $pmod->connectClsInfo($cfic->payvery, $cfic->paywell_to_payvery);
            $pmod->connectMchtInfo($mcht->payvery, $mcht->paywell_to_payvery);
            $pmod->getPaywell($this->paywell, $this->payvery, $this->brand_id, $this->before_brand_id);
            $pg->payvery_pgs = $pmod->payvery;  // 수기, 인증, 간편 관련 PG사 추가됨
            logging(['paymod'=>'ok'], 'before-system-register-job');

            $this->payvery->table('brands')->where('id', $this->brand_id)->update(['is_transfer'=>2]);
            setBrandByDNS($this->dns);
            return true;
        });
        logging(['finish'=>$result], 'before-system-register-job');
        return $result;
    }
}
