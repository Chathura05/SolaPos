<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers      = $query->latest()->paginate(15)->withQueryString();
        $totalSuppliers = Supplier::count();
        $totalPayable   = Supplier::sum('payable_balance');

        return view('suppliers.index', compact('suppliers', 'totalSuppliers', 'totalPayable'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30|unique:suppliers',
            'email'        => 'nullable|email|max:255|unique:suppliers',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'tax_number'   => 'nullable|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        Supplier::create([
            'name'         => $request->name,
            'company_name' => $request->company_name,
            'phone'        => $request->phone ?: null,
            'email'        => $request->email ?: null,
            'address'      => $request->address,
            'city'         => $request->city,
            'tax_number'   => $request->tax_number,
            'notes'        => $request->notes,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30|unique:suppliers,phone,' . $supplier->id,
            'email'        => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'tax_number'   => 'nullable|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $supplier->update([
            'name'         => $request->name,
            'company_name' => $request->company_name,
            'phone'        => $request->phone ?: null,
            'email'        => $request->email ?: null,
            'address'      => $request->address,
            'city'         => $request->city,
            'tax_number'   => $request->tax_number,
            'notes'        => $request->notes,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
