<?php

namespace App\Http\Controllers\Admin\Label;

use App\Http\Controllers\Controller;
use App\Models\Oil;
use App\Models\SDSDocument;
use App\Services\CLP\CLPCalculator;
use App\Services\CLP\SdsParser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Label\CLP as CLPLabel;

class OilController extends Controller
{
    public function index()
    {
        $oils = Oil::with('sdsDocuments')->latest()->get();
        return Inertia::render('admin/label/oil/Index', compact('oils'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'supplier'    => 'nullable|string',
            'cas_primary' => 'nullable|string',
        ]);

        Oil::create($request->only('name', 'supplier', 'cas_primary'));
        return back()->with('success', 'Oil created.');
    }

    public function uploadSds(Request $request, Oil $oil, SdsParser $parser)
    {
        $request->validate(['sds' => 'required|file|mimes:pdf|max:10240']);

        $file = $request->file('sds');
        $hash = md5_file($file->getRealPath());

        // Avoid duplicate uploads
        if (SDSDocument::where('oil_id', $oil->id)->where('document_hash', $hash)->exists()) {
            return back()->with('info', 'This SDS version is already uploaded.');
        }

        $path = $file->store("sds/{$oil->id}", 'local');

        $doc = SDSDocument::create([
            'oil_id'        => $oil->id,
            'file_path'     => $path,
            'document_hash' => $hash,
            'parsed'        => false,
        ]);

        // Parse immediately (in production, dispatch a job instead)
        try {
            $parser->parse($doc);
        } catch (\Exception $e) {
            logger()->error('SDS parse failed: ' . $e->getMessage());
        }

        return back()->with('success', 'SDS uploaded and parsed.');
    }
}
