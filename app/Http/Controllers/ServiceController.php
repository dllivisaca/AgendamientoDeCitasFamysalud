<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Address;

use Illuminate\Validation\Rule;
use File;


use Redirect;
use Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::latest()->get();

        return view('backend.service.index',compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereStatus(1)->get();

        $addresses = Address::where('status', 1)->get();

        return view('backend.service.create', compact('categories', 'addresses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'       => 'required',
            'title'             => 'required|string|max:200',
            'slug'              => 'required|unique:services,slug',
            'image'             => 'nullable|image|mimes:jpg,png,jpeg,gif,svg,webp|max:2048',
            'excerpt'           => 'nullable',
            'body'              => 'nullable',
            'meta_title'        => 'nullable',
            'meta_description'  => 'nullable',
            'meta_keywords'     => 'nullable',
            'price'             => 'required|numeric|min:0', // Validation for price field
            'sale_price'        => 'nullable|numeric|min:0', // Validation for price field
            'featured'          => 'nullable',
            'status'            => 'nullable',
            'other'             => 'nullable',
            'is_presential'    => 'nullable',
            'is_virtual'       => 'nullable',
            'address_id'       => [
                Rule::requiredIf(fn() => $request->boolean('is_presential')),
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where('status', 1),
            ],
        ]);

        $isPresential = $request->boolean('is_presential');
        $isVirtual = $request->boolean('is_virtual');

        if (!$isPresential && !$isVirtual) {
            return back()
                ->withErrors(['modalities' => 'Selecciona al menos una modalidad (presencial y/o virtual).'])
                ->withInput();
        }

        $data['featured'] = $request->featured ?? 0;
        $data['status'] = $request->status ?? 0;
        $data['excerpt'] = $request->excerpt ?? '';

        if($request->file('image'))
        {
            $imageName = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('uploads/images/service/'),$imageName);
            $data['image'] = $imageName;
        }

        $data['is_presential'] = $isPresential;
        $data['is_virtual'] = $isVirtual;
        $data['address_id'] = $isPresential ? ($data['address_id'] ?? null) : null;

        Service::create($data);
        return redirect()->route('service.index')->with('success','El servicio ha sido agregado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $categories = Category::whereStatus(1)->get();

        $addresses = Address::where('status', 1)->get();

        return view('backend.service.edit', compact('service', 'categories', 'addresses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'category_id'       => 'required',
            'title'             => 'required|string|max:200',
            'slug'              => ['required', Rule::unique('services')->ignore($service->id)],
            'image'             => 'nullable|image|mimes:jpg,png,jpeg,gif,svg,webp|max:2048',
            'excerpt'           => 'nullable',
            'body'              => 'nullable',
            'meta_title'        => 'nullable',
            'meta_description'  => 'nullable',
            'meta_keywords'     => 'nullable',
            'price'             => 'required|numeric|min:0', // Validation for price field
            'sale_price'        => 'nullable|numeric|min:0', // Validation for price field
            'featured'          => 'nullable',
            'status'            => 'nullable',
            'other'             => 'nullable',
            'is_presential'    => 'nullable',
            'is_virtual'       => 'nullable',
            'address_id'       => [
                Rule::requiredIf(fn() => $request->boolean('is_presential')),
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where('status', 1),
            ],
        ]);

        $isPresential = $request->boolean('is_presential');
        $isVirtual = $request->boolean('is_virtual');

        if (!$isPresential && !$isVirtual) {
            return back()
                ->withErrors(['modalities' => 'Selecciona al menos una modalidad (presencial y/o virtual).'])
                ->withInput();
        }

        $data['featured'] = $request->featured ?? 0;
        $data['status'] = $request->status ?? 0;
        $data['excerpt'] = $request->excerpt ?? '';

        if($request->file('image'))
        {
            $destination = public_path('uploads/images/service/').$service->image;
            if(File::exists($destination))
            {
                File::delete($destination);
            }

            //create unique name of image
            $imageName = time().'.'.$request->image->getClientOriginalExtension();

            //move image to path you wish -- it auto generate folder
            $request->image->move(public_path('uploads/images/service/'), $imageName);
            $data['image'] = $imageName;
        }

        $data['is_presential'] = $isPresential;
        $data['is_virtual'] = $isVirtual;
        $data['address_id'] = $isPresential ? ($data['address_id'] ?? null) : null;

        $service->update($data);
        return redirect()->route('service.index')->withSuccess('Service has been updated successfully.');

    }



    public function destroy(Service $service)
    {

        $service->delete();
        return back()->withSuccess('Service Succesfully moved to trash!');
    }

    public function trashView(Request $request)
    {
        $services = Service::onlyTrashed()->latest()->get();
        return view('backend.service.trash',compact('services'));
    }

    // restore data
    public function restore($id)
    {
        $data = Service::withTrashed()->find($id);
        if(!is_null($data)){
            $data->restore();
        }
        return redirect()->back()->with("success", "Data Restored Succesfully");
    }

    public function force_delete(Request $request, $id)
    {
        $service = Service::withTrashed()->find($id);

         // Check if the category has any services
         if ($service->appointments->count() > 0) {
            return redirect()->back()->withErrors('Cannot deleted permanently, service with existing bookings.');
        }

        if (!is_null($service)) {

            // Remove image
            $destination = public_path('uploads/images/service/') . $service->image;
            if (\File::exists($destination)) {
                \File::delete($destination);
            }

            $service->forceDelete();
        }

        return redirect()->back()->with("success", "Data Deleted Permanently!!");
    }



}
