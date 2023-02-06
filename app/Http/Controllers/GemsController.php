<?php

namespace App\Http\Controllers;

use App\Models\Games\Gems;
use Illuminate\Http\Request;

class GemsController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Games\Gems  $gems
     * @return \Illuminate\Http\Response
     */
    public function show(Gems $gems)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Games\Gems  $gems
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Gems $gems)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Games\Gems  $gems
     * @return \Illuminate\Http\Response
     */
    public function destroy(Gems $gems)
    {
        //
    }
}
