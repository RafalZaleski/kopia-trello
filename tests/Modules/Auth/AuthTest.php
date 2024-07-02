<?php

namespace Tests\Auth;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private AuthService $authService;
    private array $data;
    private array $badData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = new AuthService();
        
        $this->data = [
            'name' => '123',
            'email' => Str::random(8) . '@test.test',
            'password' => '1234',
        ];

        $this->badData = [
            'name' => '123',
            'email' => Str::random(8) . '@test.test',
            'password' => '12345',
        ];
    }

    public function test_register(): User
    {
        $user = $this->authService->register($this->data);

        $this->assertTrue($user->email === $this->data['email']);

        return $user;
    }

    #[Depends('test_register')]
    public function test_login(User $user): void
    {
        $userData['email'] = $user->email;
        $userData['password'] = '1234';
        $this->authService->login($userData);

        $this->assertTrue(auth()->user()->email === $userData['email']);
        $this->assertAuthenticatedAs(auth()->user());
    }

    public function test_login_bad_data(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->login($this->badData);
    }
}
