<?php

namespace App\Http\Controllers;
use Exception;
use App\Models\ServiceSwapType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class ServiceSwapTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ServiceSwapType::all();
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
     * @param  \App\Models\ServiceSwapType  $serviceSwapType
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceSwapType $serviceSwapType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceSwapType  $serviceSwapType
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceSwapType $serviceSwapType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceSwapType  $serviceSwapType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ServiceSwapType $serviceSwapType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceSwapType  $serviceSwapType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceSwapType $serviceSwapType)
    {
        //
    }
}
