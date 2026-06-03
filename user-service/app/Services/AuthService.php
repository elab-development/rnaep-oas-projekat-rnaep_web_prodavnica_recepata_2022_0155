<?php

namespace App\Services;

use App\Infrastructure\PasswordHasher;
use App\Infrastructure\TokenService;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordHasher $passwordHasher,
        private readonly TokenService   $tokenService,
    ) {}

    public function register(array $data): array
    {
        // XSS zaštita - sanitizacija
        $data['email'] = htmlspecialchars(strip_tags($data['email']));

        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $this->passwordHasher->hash($data['password']),
            'role'     => $data['role'] ?? 'user',
        ]);

        $token = $this->tokenService->createToken($user);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$this->passwordHasher->check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Pogrešni kredencijali.'],
            ]);
        }

        $token = $this->tokenService->createToken($user);

        return [
            'user'  => $this->formatUser($user),
            'token' => $token,
        ];
    }

    private function formatUser(User $user): array
    {
        return [
            'user_id' => $user->user_id,
            'email'   => $user->email,
            'role'    => $user->role,
        ];
    }
}