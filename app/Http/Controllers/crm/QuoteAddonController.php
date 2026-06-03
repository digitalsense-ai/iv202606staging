<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CRMQuote;
use App\Models\CRMQuoteAddon;

class QuoteAddonController extends Controller
{
    /**
     * Store single addon
     */
    public function store(Request $request)
    {
        CRMQuoteAddon::create([
            'quote_id' => $request->quote_id,
            'addon_name' => $request->addon_name,
            'enabled' => $request->enabled ?? false,
            'price' => $request->price ?? 0
        ]);

        return back()->with('success','Addon added');
    }

    /**
     * Update addon
     */
    public function update(Request $request, $id)
    {
        $addon = CRMQuoteAddon::findOrFail($id);

        $addon->update([
            'enabled' => $request->enabled ?? false,
            'price' => $request->price ?? 0
        ]);

        return back()->with('success','Addon updated');
    }

    /**
     * Remove addon
     */
    public function destroy($id)
    {
        $addon = CRMQuoteAddon::findOrFail($id);

        $addon->delete();

        return back()->with('success','Addon removed');
    }

    /**
     * Quote totals calculation
     */
    public function calculate($quoteId)
    {
        $quote = CRMQuote::with('addons')->findOrFail($quoteId);

        $basePrice = $quote->base_price;
        $registration = $quote->registration_price;

        $addonTotal = $quote->addons()
            ->enabled()
            ->sum('price');

        $monthly = $basePrice + $addonTotal;
        $yearly = $monthly * 12;

        return response()->json([
            'base_price' => $basePrice,
            'registration_price' => $registration,
            'addon_total' => $addonTotal,
            'monthly_total' => $monthly,
            'yearly_total' => $yearly
        ]);
    }
}