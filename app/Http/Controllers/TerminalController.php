<?php

namespace App\Http\Controllers;

use App\Models\Terminal;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTerminalRequest;
use App\Http\Requests\UpdateTerminalRequest;

class TerminalController extends Controller
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
     * @param  \App\Http\Requests\StoreTerminalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTerminalRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Terminal  $terminal
     * @return \Illuminate\Http\Response
     */
    public function show(Terminal $terminal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Terminal  $terminal
     * @return \Illuminate\Http\Response
     */
    public function edit(Terminal $terminal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTerminalRequest  $request
     * @param  \App\Models\Terminal  $terminal
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTerminalRequest $request, Terminal $terminal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Terminal  $terminal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Terminal $terminal)
    {
        //
    }
}
