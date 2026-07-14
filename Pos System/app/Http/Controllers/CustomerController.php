<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Customer::query();

        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $query->where('showroom_id', $user->showroom_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers    = $query->latest()->paginate(15)->withQueryString();
        
        $statsQuery = Customer::query();
        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $statsQuery->where('showroom_id', $user->showroom_id);
        }
        $totalCustomers = (clone $statsQuery)->count();
        $activeCustomers = (clone $statsQuery)->where('is_active', true)->count();

        return view('customers.index', compact('customers', 'totalCustomers', 'activeCustomers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:30|unique:customers',
            'email'        => 'nullable|email|max:255|unique:customers',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        Customer::create([
            'name'         => $request->name,
            'phone'        => $request->phone ?: null,
            'email'        => $request->email ?: null,
            'address'      => $request->address,
            'city'         => $request->city,
            'credit_limit' => $request->credit_limit ?? 0,
            'notes'        => $request->notes,
            'is_active'    => $request->boolean('is_active', true),
            'showroom_id'  => auth()->user()->showroom_id,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        // Merge FK-linked sales with historical phone-matched sales (deduped by id)
        $fkSales    = Sale::where('customer_id', $customer->id)->latest()->get();
        $phoneSales = $customer->phone
            ? Sale::where('customer_phone', $customer->phone)
                  ->whereNull('customer_id')   // avoid double-counting FK-linked rows
                  ->latest()
                  ->get()
            : collect();

        $recentSales = $fkSales->merge($phoneSales)->sortByDesc('created_at')->take(10)->values();
        $totalSpent  = $fkSales->sum('total_amount') + $phoneSales->sum('total_amount');
        $totalOrders = $fkSales->count() + $phoneSales->count();

        return view('customers.show', compact('customer', 'recentSales', 'totalSpent', 'totalOrders'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:30|unique:customers,phone,' . $customer->id,
            'email'        => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $customer->update([
            'name'         => $request->name,
            'phone'        => $request->phone ?: null,
            'email'        => $request->email ?: null,
            'address'      => $request->address,
            'city'         => $request->city,
            'credit_limit' => $request->credit_limit ?? 0,
            'notes'        => $request->notes,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /** AJAX: search customers for POS autocomplete */
    public function search(Request $request)
    {
        $user = auth()->user();
        $query = Customer::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('phone', 'like', '%' . $request->q . '%');
            });

        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $query->where('showroom_id', $user->showroom_id);
        }

        $customers = $query->limit(8)->get(['id', 'name', 'phone', 'email', 'loyalty_points']);

        return response()->json($customers);
    }
}
