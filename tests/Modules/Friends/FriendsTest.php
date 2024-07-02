<?php

namespace Tests\Friends;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Friends\Services\FriendService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

class FriendsTest extends TestCase
{
    private FriendService $friendService;
    private AuthService $authService;
    private array $data;
    private User $user1;
    private ?User $user2;
    private ?User $user3;


    protected function setUp(): void
    {
        parent::setUp();

        $this->friendService = new FriendService();
        $this->authService = new AuthService();

        $this->data = [
            'name' => '123',
            'email' => Str::random(8) . '@test.test',
            'password' => '1234',
        ];

        $this->user1 = User::first();
        $this->user2 = User::whereNot('id', $this->user1->id)->first();
        if (!$this->user2) {
            $this->user2 = $this->authService->register($this->data);
        }

        $this->user3 = User::whereNotIn('id', [$this->user1->id, $this->user2->id])->first();

        if (!$this->user3) {
            $this->data['email'] = Str::random(8) . '@test.test';
            $this->user3 = $this->authService->register($this->data);
        }
    }

    public function test_invite(): User
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        if (auth()->user()->isFriend($this->user2->id)) {
            $this->friendService->destroy($this->user2->id);
        }

        $userId = $this->friendService->invite(['email' => $this->user2->email]);

        $this->assertTrue($userId === $this->user2->id);

        return $this->user2;
    }

    #[Depends('test_invite')]
    public function test_invite_already_send(User $user): void
    {
        $this->expectException(\RuntimeException::class);
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());
        $this->friendService->invite(['email' => $user->email]);
    }

    public function test_invite_cant_send_to_myself(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());
        $this->friendService->invite(['email' => $this->user1->email]);
    }

    public function test_confirm_bad_user(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->authService->login(['email' => $this->user3->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());
        $this->friendService->confirm($this->user1->id);
    }

    #[Depends('test_invite')]
    public function test_confirm(User $user): void
    {
        $this->authService->login(['email' => $user->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());
        $this->friendService->confirm($this->user1->id);

        $this->assertTrue(
            (bool)auth()->user()->friends()
                ->where(['friend_id' => $this->user1->id])
                ->first()->pivot->is_accepted
        );
    }
}
