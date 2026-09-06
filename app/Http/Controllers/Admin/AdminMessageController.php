<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminMessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->select('id', 'name', 'email', 'subject', 'message', 'is_read', 'created_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('admin/message/Index', [
            'messages' => $messages,
        ]);
    }

    public function show(ContactMessage $message)
    {
        return Inertia::render('admin/message/Show', [
            'message' => $message,
        ]);
    }
}
