<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promos = \App\Models\PromoCode::latest()->paginate(15);
        return view('promos.index', compact('promos'));
    }

    public function create()
    {
        return view('promos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'       => 'required|string|unique:promo_codes',
            'type'       => 'required|in:fixed,percent',
            'value'      => 'required|numeric|min:0',
            'min_spend'  => 'nullable|numeric|min:0',
            'max_uses'   => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active'  => 'boolean'
        ]);

        \App\Models\PromoCode::create([
            'code'       => strtoupper($request->code),
            'type'       => $request->type,
            'value'      => $request->value,
            'min_spend'  => $request->min_spend ?? 0,
            'max_uses'   => $request->max_uses,
            'expires_at' => $request->expires_at,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('promos.index')->with('success', 'Promo code created successfully.');
    }

    public function edit(\App\Models\PromoCode $promo)
    {
        return view('promos.edit', compact('promo'));
    }

    public function update(Request $request, \App\Models\PromoCode $promo)
    {
        $request->validate([
            'code'       => 'required|string|unique:promo_codes,code,' . $promo->id,
            'type'       => 'required|in:fixed,percent',
            'value'      => 'required|numeric|min:0',
            'min_spend'  => 'nullable|numeric|min:0',
            'max_uses'   => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active'  => 'boolean'
        ]);

        $promo->update([
            'code'       => strtoupper($request->code),
            'type'       => $request->type,
            'value'      => $request->value,
            'min_spend'  => $request->min_spend ?? 0,
            'max_uses'   => $request->max_uses,
            'expires_at' => $request->expires_at,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('promos.index')->with('success', 'Promo code updated successfully.');
    }

    public function destroy(\App\Models\PromoCode $promo)
    {
        $promo->delete();
        return redirect()->route('promos.index')->with('success', 'Promo code deleted successfully.');
    }

    public function toggleStatus(\App\Models\PromoCode $promo)
    {
        $promo->update(['is_active' => !$promo->is_active]);
        return back()->with('success', 'Promo status updated.');
    }
}
