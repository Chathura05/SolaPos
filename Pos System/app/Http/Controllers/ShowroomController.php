<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShowroomController extends Controller
{
    public function index()
    {
        $showrooms = \App\Models\Showroom::withCount('users')->latest()->paginate(15);
        return view('showrooms.index', compact('showrooms'));
    }

    public function create()
    {
        return view('showrooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        \App\Models\Showroom::create($request->all());

        return redirect()->route('showrooms.index')->with('success', 'Showroom created successfully.');
    }

    public function edit(\App\Models\Showroom $showroom)
    {
        return view('showrooms.edit', compact('showroom'));
    }

    public function update(Request $request, \App\Models\Showroom $showroom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $showroom->update($request->all());

        return redirect()->route('showrooms.index')->with('success', 'Showroom updated successfully.');
    }

    public function destroy(\App\Models\Showroom $showroom)
    {
        // Prevent deletion if showroom has linked data
        if ($showroom->users()->count() > 0) {
            return redirect()->route('showrooms.index')
                ->with('error', 'Cannot delete showroom — it still has users assigned to it.');
        }

        if ($showroom->sales()->count() > 0) {
            return redirect()->route('showrooms.index')
                ->with('error', 'Cannot delete showroom — it has sales records linked to it.');
        }

        if ($showroom->products()->wherePivot('stock_quantity', '>', 0)->count() > 0) {
            return redirect()->route('showrooms.index')
                ->with('error', 'Cannot delete showroom — it still has stock. Transfer or remove stock first.');
        }

        $showroom->delete();
        return redirect()->route('showrooms.index')->with('success', 'Showroom deleted successfully.');
    }
}
