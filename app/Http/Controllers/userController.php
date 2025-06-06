<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class userController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())
            ->withCount(['unreadMessages as unread_messages_count' => function ($query) {
                $query->where('receiver_id', Auth::id());
            }])
            ->get();

        return view('dashboard', ['users' => $users]);
    }

    public function userChat(User $userId){
        return view ('user-chat', data: compact('userId'));
    }


    public function AddProfilePicture(User $userId)
    {
        if (request()->hasFile('profilePhoto')) {
            // Delete old photo if it exists
            if ($userId->profile_photo && Storage::disk('public')->exists($userId->profile_photo)) {
                Storage::disk('public')->delete($userId->profile_photo);
            }

            // Store new photo
            $path = request()->file('profilePhoto')->store('profile_photos', 'public');
            $userId->profile_photo = $path;
            $userId->save();
        }

        return back()->with('success', 'Profile picture updated!');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
