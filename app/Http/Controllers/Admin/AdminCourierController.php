<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminCourierController extends Controller
{
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

    public function create()
    {
        return Inertia::render('admin/courier/CreateEdit', [
            'isEditing' => false,
        ]);
    }

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

    public function edit(Courier $courier)
    {
        return Inertia::render('admin/courier/CreateEdit', [
            'courier' => $courier,
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['Royal Mail', 'FedEx', 'Evri', 'DPD'])],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
            'cost' => ['required', 'numeric', 'min:0.01'],
        ]);

        return DB::transaction(function () use ($validated, $courier) {
            $courier->update($validated);

            return redirect()->route('admin.couriers.index')
                ->with('success', "Courier '{$courier->name}' updated successfully!");
        });
    }

    public function destroy(Courier $courier)
    {
        $courier->delete();

        return redirect()->route('admin.couriers.index')
            ->with('success', "Courier '{$courier->name}' deleted successfully.");
    }
}
