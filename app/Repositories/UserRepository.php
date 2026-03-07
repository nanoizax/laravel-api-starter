<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Exceptions\Domain\EmailAlreadyInUseException;
use App\Exceptions\Domain\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): User
    {
        $user = User::find($id);

        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = User::query();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): User
    {
        try {
            return User::create($data);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyInUseException($data['email'] ?? '');
        }
    }

    public function update(User $user, array $data): User
    {
        try {
            $user->update($data);

            return $user->fresh();
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyInUseException($data['email'] ?? '');
        }
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
