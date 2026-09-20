<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    public function searchUsers(string $query, User $currentUser): LengthAwarePaginator
    {
        return User::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "{$query}%")
                ->orWhere('username', 'LIKE', "{$query}%");
        })->where('id', '!=', $currentUser->id)->paginate(10);
    }
}
