<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function updateProfileDetails($request, $user) {
        $data = $request->validated();
        if($request->hasFile('avatar')) {
            if($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if($request->hasFile('banner')) {
            if($user->banner) {
                Storage::disk('public')->delete($user->banner);
            }
            $data['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $user->update($data);
        return $user;
    }
}
