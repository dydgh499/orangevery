<?php

namespace App\Http\Controllers;

use App\Models\Privacy;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrivacyRequest;
use App\Http\Requests\UpdatePrivacyRequest;

class PrivacyController extends Controller
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
     * @param  \App\Http\Requests\StorePrivacyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePrivacyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Privacy  $privacy
     * @return \Illuminate\Http\Response
     */
    public function show(Privacy $privacy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Privacy  $privacy
     * @return \Illuminate\Http\Response
     */
    public function edit(Privacy $privacy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePrivacyRequest  $request
     * @param  \App\Models\Privacy  $privacy
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePrivacyRequest $request, Privacy $privacy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Privacy  $privacy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Privacy $privacy)
    {
        //
    }
}
