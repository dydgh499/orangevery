<?php

namespace App\Http\Controllers;

use App\Models\paymentGateway;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorepaymentGatewayRequest;
use App\Http\Requests\UpdatepaymentGatewayRequest;

class PaymentGatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorepaymentGatewayRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepaymentGatewayRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\paymentGateway  $paymentGateway
     * @return \Illuminate\Http\Response
     */
    public function show(paymentGateway $paymentGateway)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\paymentGateway  $paymentGateway
     * @return \Illuminate\Http\Response
     */
    public function edit(paymentGateway $paymentGateway)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepaymentGatewayRequest  $request
     * @param  \App\Models\paymentGateway  $paymentGateway
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepaymentGatewayRequest $request, paymentGateway $paymentGateway)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\paymentGateway  $paymentGateway
     * @return \Illuminate\Http\Response
     */
    public function destroy(paymentGateway $paymentGateway)
    {
        //
    }
}
