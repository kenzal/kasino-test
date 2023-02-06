<?php

namespace App\Http\Controllers;

use App\Models\Games\Dice;
use Illuminate\Http\Request;

class DiceController extends Controller
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
     * @param  \App\Models\Games\Dice  $dice
     * @return \Illuminate\Http\Response
     */
    public function show(Dice $dice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Games\Dice  $dice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dice $dice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Games\Dice  $dice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dice $dice)
    {
        //
    }
}
