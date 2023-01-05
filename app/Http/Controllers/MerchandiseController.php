<?php

namespace App\Http\Controllers;

use App\Models\Merchandise;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchandiseRequest;
use App\Http\Requests\UpdateMerchandiseRequest;

class MerchandiseController extends Controller
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
     * @param  \App\Http\Requests\StoreMerchandiseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMerchandiseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Merchandise  $merchandise
     * @return \Illuminate\Http\Response
     */
    public function show(Merchandise $merchandise)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Merchandise  $merchandise
     * @return \Illuminate\Http\Response
     */
    public function edit(Merchandise $merchandise)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMerchandiseRequest  $request
     * @param  \App\Models\Merchandise  $merchandise
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMerchandiseRequest $request, Merchandise $merchandise)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Merchandise  $merchandise
     * @return \Illuminate\Http\Response
     */
    public function destroy(Merchandise $merchandise)
    {
        //
    }
}
