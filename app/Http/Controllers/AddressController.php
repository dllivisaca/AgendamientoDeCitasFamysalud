<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = []; // por ahora vacío
        return view('backend.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('backend.addresses.create');
    }
}