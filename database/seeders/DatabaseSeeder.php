<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BrandSeeder;

use Database\Seeders\MerchandiseSeeder;
use Database\Seeders\SalesforceSeeder;

use Database\Seeders\ClassificationSeeder;
use Database\Seeders\PaymentGatewaysSeeder;
use Database\Seeders\PaymentSectionSeeder;
use Database\Seeders\PaymentModuleSeeder;

use Database\Seeders\TransactionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BrandSeeder::class,
            ClassificationSeeder::class,
            SalesforceSeeder::class,
            MerchandiseSeeder::class,
            OperatorSeeder::class,
            PaymentGatewaysSeeder::class,
            PaymentSectionSeeder::class,
            PaymentModuleSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
