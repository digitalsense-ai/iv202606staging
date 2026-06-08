<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CRMAddon;

use App\Classes\CommonClass;
use App\Classes\CVRApiClass;

class AddonsController extends Controller
{
    public $authUser;

    public $commonClass;
    public $cvrApiClass;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            $this->cvrApiClass = new CVRApiClass();

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $addons = CRMAddon::latest()->get();

        return view('content.crm.addon.index', compact('pageConfigs', 'addons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('content.crm.addon.create');
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
            ->route('content.crm.addon.index')
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

        return view('content.crm.pricing.edit', compact('pricing'));
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
