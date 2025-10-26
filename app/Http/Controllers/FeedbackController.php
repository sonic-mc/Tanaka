<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // ensure only authenticated users can access
    }

    // Show feedback submission form
    public function create()
    {
        return view('feedback.create');
    }

    // Store feedback in DB
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        DB::table('feedback')->insert([
            'user_id'    => Auth::id(),
            'subject'    => $data['subject'],
            'message'    => $data['message'],
            'rating'     => $data['rating'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('feedback.create')
            ->with('success', 'Thank you! Your feedback has been submitted.');
    }

    // Admin: view all feedback
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $query = DB::table('feedback')
            ->leftJoin('users', 'users.id', '=', 'feedback.user_id')
            ->select('feedback.*', 'users.name as user_name')
            ->orderByDesc('feedback.created_at');

        // Optional filters
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('feedback.subject', 'like', $search)
                  ->orWhere('feedback.message', 'like', $search)
                  ->orWhere('users.name', 'like', $search);
            });
        }

        $feedback = $query->paginate(10)->withQueryString();

        return view('feedback.index', compact('feedback'));
    }
}
