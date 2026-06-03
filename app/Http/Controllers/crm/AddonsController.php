<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CRMAddon;

class AddonsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addons = CRMAddon::latest()->get();

        return view('crm.addon.index', compact('addons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('crm.addon.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        foreach($request->rows as $row)
        {
            CRMAddon::create([
                'name' => $row['addon_name'],
                'price' => $row['price'],
                'frequency' => $row['frequency'],
                'created_by' => auth()->id()
            ]);
        }

        return redirect()
            ->route('crm.addon.index')
            ->with('success', 'Pricing added');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pricing = CRMAddon::findOrFail($id);

        return view('crm.pricing.edit', compact('pricing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pricing = CRMAddon::findOrFail($id);

        $pricing->update([
            'name' => $request->product_name,
            'price' => $request->price,
            'frequency' => $request->frequency,
            'updated_by' => auth()->id()
        ]);

        return back()->with('success', 'Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CRMAddon::findOrFail($id)->delete();

        return back()->with('success', 'Deleted');
    }
}
