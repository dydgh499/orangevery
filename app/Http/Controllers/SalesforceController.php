<?php

namespace App\Http\Controllers;

use App\Models\Salesforce;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesforceRequest;
use App\Http\Requests\UpdateSalesforceRequest;

class SalesforceController extends Controller
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
     * @param  \App\Http\Requests\StoreSalesforceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesforceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Salesforce  $salesforce
     * @return \Illuminate\Http\Response
     */
    public function show(Salesforce $salesforce)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Salesforce  $salesforce
     * @return \Illuminate\Http\Response
     */
    public function edit(Salesforce $salesforce)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesforceRequest  $request
     * @param  \App\Models\Salesforce  $salesforce
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesforceRequest $request, Salesforce $salesforce)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Salesforce  $salesforce
     * @return \Illuminate\Http\Response
     */
    public function destroy(Salesforce $salesforce)
    {
        //
    }
}
