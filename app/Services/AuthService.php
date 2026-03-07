<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Exceptions\Domain\AccountDisabledException;
use App\Exceptions\Domain\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function register(array $data): array
    {
        // EmailAlreadyInUseException propagates from the repository
        // if the unique constraint is violated at the DB level.
        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return compact('user', 'token');
    }

    public function login(array $credentials): array
    {
        $email = strtolower(trim($credentials['email']));

        $user = $this->userRepository->findByEmail($email);

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            // Intentionally vague: do not reveal whether the email exists.
            throw new InvalidCredentialsException();
        }

        if (! $user->is_active) {
            throw new AccountDisabledException();
        }

        $token = $user->createToken('api')->plainTextToken;

        return compact('user', 'token');
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
