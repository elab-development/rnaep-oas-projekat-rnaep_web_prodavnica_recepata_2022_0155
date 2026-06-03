<?php
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\AuthService;
 
class AuthController extends Controller
{
   public function __construct(
        private readonly AuthService $authService
    ) {}
 
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'sometimes|in:user,admin',
        ]);
 
        $result = $this->authService->register($validated);
 
        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $result['user'],
            'token'   => $result['token'],
        ], 201);
    }
 
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
 
        try {
            $result = $this->authService->login($validated['email'], $validated['password']);
        } catch (ValidationException $e) {
            throw $e;
        }
 
        return response()->json([
            'message' => 'Login successful',
            'user'    => $result['user'],
            'token'   => $result['token'],
        ]);
    }
    
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
 
        return response()->json(['message' => 'Logged out']);
    }
 
    public function verify(Request $request)
    {
        $user = $request->user();
 
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
 
        return $this->authService->verify($user);
    }
}  