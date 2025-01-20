<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Container;
use Carbon\Carbon;

interface ContainerInterface
{
    public function request(Carbon $date, $trans);
    public function response(Carbon $date);
}
