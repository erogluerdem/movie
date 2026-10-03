<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ContentRequest::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('user_notes', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $counts = [
            'all' => ContentRequest::count(),
            'pending' => ContentRequest::where('status', 'pending')->count(),
            'in_review' => ContentRequest::where('status', 'in_review')->count(),
            'available' => ContentRequest::where('status', 'available')->count(),
            'rejected' => ContentRequest::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Requests/Index', [
            'requests' => $requests,
            'filters' => $request->only(['search', 'status']),
            'counts' => $counts,
        ]);
    }

    public function update(Request $request, ContentRequest $contentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_review,available,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $contentRequest->update($validated);

        return back()->with('success', 'Request status updated successfully!');
    }

    public function destroy(ContentRequest $contentRequest): RedirectResponse
    {
        $contentRequest->delete();

        return back()->with('success', 'Request deleted.');
    }
}
