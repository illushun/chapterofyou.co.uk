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
    public function index(Request $request)
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
     * Display the specified courier.
     */
    public function show(Courier $courier)
    {
        return Inertia::render('admin/courier/Show', [
            'courier' => $courier,
        ]);
    }
}
