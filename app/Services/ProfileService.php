<?php

namespace App\Services;

use App\Models\User;
use App\Traits\Uploadable;
use Exception;

class ProfileService
{
    use Uploadable;

    public function getProfile(User $user): User
    {
        return $user->load([
            'posts' => function ($query) {
                $query->latest();
            },
            'posts.comments' => function ($query) {
                $query->whereNull('parent_id')->latest();
            },
            'posts.comments.replies' => function ($query) {
                $query->latest();
            },
            'posts.comments.user',
            'posts.comments.replies.user',
        ]);
    }

    public function update(array $data, User $user): User
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $images = [
            'avatar' => ['oldPath' => $user->avatar, 'newPath' => null, 'directory' => 'avatars'],
            'banner' => ['oldPath' => $user->banner, 'newPath' => null, 'directory' => 'banners'],
        ];
        try {
            foreach ($images as $key => &$image) {
                if (isset($data[$key])) {
                    $image['newPath'] = $this->uploadImage($data[$key], $image['directory']);
                    $data[$key] = $image['newPath'];
                }
            }
            $user->update($data);
            foreach ($images as $img) {
                if ($img['newPath'] && $img['oldPath']) {
                    $this->deleteImage($img['oldPath']);
                }
            }

            return $user;

        } catch (Exception $e) {
            foreach ($images as $img) {
                if ($img['newPath']) {
                    $this->deleteImage($img['newPath']);
                }
            }
            throw $e;
        }
    }
}
