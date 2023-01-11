<?php

namespace App\Http\Controllers;

use App\Models\TransNotificationLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransNotificationLogRequest;
use App\Http\Requests\UpdateTransNotificationLogRequest;

class TransNotificationLogController extends Controller
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
     * @param  \App\Http\Requests\StoreTransNotificationLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransNotificationLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TransNotificationLog  $transNotificationLog
     * @return \Illuminate\Http\Response
     */
    public function show(TransNotificationLog $transNotificationLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TransNotificationLog  $transNotificationLog
     * @return \Illuminate\Http\Response
     */
    public function edit(TransNotificationLog $transNotificationLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransNotificationLogRequest  $request
     * @param  \App\Models\TransNotificationLog  $transNotificationLog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransNotificationLogRequest $request, TransNotificationLog $transNotificationLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TransNotificationLog  $transNotificationLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransNotificationLog $transNotificationLog)
    {
        //
    }
}
