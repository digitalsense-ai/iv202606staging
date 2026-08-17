<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CRMContractTemplate;

class ContractTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language = $request->language ?? 'english';

        // $templates = ContractTemplate::where('language', $language)
        //     ->orderBy('clause_number')
        $templates = CRMContractTemplate::orderBy('clause_number')
                        ->get();

        return view('crm.contract_templates.index', compact(
            'templates',
            'language'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('crm.contract_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        CRMContractTemplate::create([
            'language' => $request->language,
            'clause_number' => $request->clause_number,
            'title' => $request->title,
            'content' => $request->content,
            'created_by' => auth()->id()
        ]);

        return redirect()
            ->route('crm.contract-templates.index')
            ->with('success', 'Template created');
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
        $template = CRMContractTemplate::findOrFail($id);

        return view('crm.contract_templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $template = CRMContractTemplate::findOrFail($id);

        $template->update([
            'language' => $request->language,
            'clause_number' => $request->clause_number,
            'title' => $request->title,
            'content' => $request->content,
            'updated_by' => auth()->id()
        ]);

        return back()->with('success', 'Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        CRMContractTemplate::findOrFail($id)->delete();

        return back()->with('success', 'Deleted');
    }
}
