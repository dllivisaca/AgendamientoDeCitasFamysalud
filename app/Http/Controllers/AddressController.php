<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = \App\Models\Address::orderBy('id', 'desc')->get();
        return view('backend.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('backend.addresses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'full_address'    => ['required', 'string'],
            'google_maps_url' => ['nullable', 'string', 'max:2000'],
            'reference'       => ['nullable', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:150'],
            'status'          => ['nullable', 'in:0,1'],
        ], [
            'name.required'         => 'Por favor ingresa el nombre de la sede.',
            'full_address.required' => 'Por favor ingresa la dirección completa.',
            'city.required' => 'Por favor ingresa la ciudad.',
        ]);

        $data['status'] = $data['status'] ?? 1;

        \App\Models\Address::create($data);

        return redirect()
            ->route('addresses.index')
            ->with('success', 'Dirección guardada correctamente ✅');
    }

    public function edit($id)
    {
        $address = \App\Models\Address::findOrFail($id);
        return view('backend.addresses.edit', compact('address'));
    }

    public function update(Request $request, $id)
    {
        $address = \App\Models\Address::findOrFail($id);

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'full_address'    => ['required', 'string'],
            'google_maps_url' => ['nullable', 'string', 'max:2000'],
            'reference'       => ['nullable', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:150'],
            'status'          => ['required', 'in:0,1'],
        ], [
            'name.required'         => 'Por favor ingresa el nombre de la sede.',
            'full_address.required' => 'Por favor ingresa la dirección completa.',
            'city.required'         => 'Por favor ingresa la ciudad.',
        ]);

        $address->update($data);

        return redirect()
            ->route('addresses.index')
            ->with('success', 'Dirección actualizada correctamente ✏️');
    }

    public function destroy($id)
    {
        $address = \App\Models\Address::findOrFail($id);
        $address->delete();

        return redirect()
            ->route('addresses.index')
            ->with('success', 'Dirección eliminada correctamente 🗑️');
    }
}