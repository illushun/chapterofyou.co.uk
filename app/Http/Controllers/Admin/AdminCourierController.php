<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminCourierController extends Controller
{
    /**
     * Display a listing of couriers.
     */
    public function index()
    {
        $couriers = Courier::query()
            ->select('id', 'name', 'type', 'status', 'cost', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('admin/courier/Index', [
            'couriers' => $couriers,
        ]);
    }

    /**
     * Show the form for creating a new resource. (Create)
     */
    public function create()
    {
        return Inertia::render('admin/courier/CreateEdit', [
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage. (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['Royal Mail', 'FedEx', 'Evri', 'DPD'])],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
            'cost' => ['required', 'numeric', 'min:0.01'],
        ]);

        return DB::transaction(function () use ($validated) {
            $courier = Courier::create($validated);

            return redirect()->route('admin.couriers.index')
                ->with('success', "Courier '{$courier->name}' created successfully!");
        });
    }

    /**
     * Show the form for editing the specified resource. (Edit)
     */
    public function edit(Courier $courier)
    {
        return Inertia::render('admin/courier/CreateEdit', [
            'courier' => $courier,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified resource in storage. (Update)
     */
    public function update(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['Royal Mail', 'FedEx', 'Evri', 'DPD'])],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
            'cost' => ['required', 'numeric', 'min:0.01'],
        ]);

        return DB::transaction(function () use ($request, $validated, $courier) {
            $courier->update($validated);

            return redirect()->route('admin.couriers.index')
                ->with('success', "Courier '{$courier->name}' updated successfully!");
        });
    }

    /**
     * Remove the specified resource from storage. (Destroy)
     */
    public function destroy(Courier $courier)
    {
        $courier->delete();

        return redirect()->route('admin.couriers.index')
            ->with('success', "Courier '{$courier->name}' deleted successfully.");
    }
}
