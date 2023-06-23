<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transaction::factory()->count(100)->create();
        $this->command->info('TransactionSeeder seeder pay completed.');
        Transaction::factory()->count(10)->create();
        $this->command->info('TransactionSeeder seeder cxl completed.');
    }
}
