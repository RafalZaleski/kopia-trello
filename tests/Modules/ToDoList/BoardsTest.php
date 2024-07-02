<?php

namespace Tests\ToDoList;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Friends\Services\FriendService;
use App\Modules\ToDoList\Boards\Models\Board;
use App\Modules\ToDoList\Boards\Services\BoardService;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

class BoardsTest extends TestCase
{
    private BoardService $boardService;
    private FriendService $friendService;
    private AuthService $authService;
    private array $data;
    private User $user1;
    private User $user2;
    private ?User $user3;


    protected function setUp(): void
    {
        parent::setUp();

        $this->boardService = new BoardService();
        $this->friendService = new FriendService();
        $this->authService = new AuthService();

        $this->data = [
            'name' => '123',
            'email' => Str::random(8) . '@test.test',
            'password' => '1234',
        ];

        $this->user1 = User::first();
        $this->user2 = User::whereNot('id', $this->user1->id)->first();
        $this->user3 = User::whereNotIn('id', [$this->user1->id, $this->user2->id])->first();

        if (!$this->user3) {
            $this->user3 = $this->authService->register($this->data);
        }
    }

    public function test_store_board(): Board
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $validatedData = [
            'name' => 'test',
        ];

        $board = $this->boardService->store($validatedData);

        $this->assertNotNull($board);

        return $board;
    }

    #[Depends('test_store_board')]
    public function test_check_access_true(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertTrue($this->boardService->checkAccessToBoard($board));
    }

    #[Depends('test_store_board')]
    public function test_check_access_false(Board $board): void
    {
        $this->authService->login(['email' => $this->user2->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertFalse($this->boardService->checkAccessToBoard($board));
    }

    #[Depends('test_store_board')]
    public function test_copy(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $copyBoard = $this->boardService->copy($board);

        $this->assertNotEquals($copyBoard->id, $board->id);
        $this->assertNotEquals($copyBoard->name, $board->name);
        $this->assertEquals($copyBoard->catalogs()->count(), $board->catalogs()->count());

    }

    #[Depends('test_store_board')]
    public function test_share(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $friend = auth()->user()->friends()
            ->whereNot('friend_id', auth()->user()->getAuthIdentifier())
            ->wherePivot('is_accepted', true)->first();
        $this->assertNotNull($friend);

        $this->assertTrue($this->boardService->share($board, $friend->id));
    }

    #[Depends('test_store_board')]
    public function test_cant_share_if_not_friend(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->friendService->destroy($this->user3->id);

        $this->assertFalse($this->boardService->share($board, $this->user3->id));
    }

    #[Depends('test_store_board')]
    public function test_cant_share_to_myself(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());
        $this->assertFalse($this->boardService->share($board, auth()->user()->getAuthIdentifier()));
    }

    #[Depends('test_store_board')]
    public function test_not_destroy_when_some_users_attach(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->boardService->destroy($board);

        $this->assertNotNull(Board::where(['id' => $board->id])->first());
        $this->assertFalse($this->boardService->checkAccessToBoard($board));
    }

    #[Depends('test_store_board')]
    public function test_destroy(Board $board): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $friend = auth()->user()->friends()
            ->whereNot('friend_id', auth()->user()->getAuthIdentifier())
            ->wherePivot('is_accepted', true)->first();
        $this->assertNotNull($friend);

        $this->authService->login(['email' => $friend->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->boardService->destroy($board);

        $this->assertNull(Board::where(['id' => $board->id])->first());
    }

    public function test_store_board_endpoint(): int
    {
        $validatedData = [
            'name' => 'test',
        ];

        $response = $this->actingAs($this->user1)->post('/api/boards', $validatedData);
        
        $response->assertStatus(201);

        return json_decode($response->getContent())->data->id;
    }

    #[Depends('test_store_board_endpoint')]
    public function test_show_board_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user1)->get('/api/boards/' . $boardId);

        $response->assertStatus(200);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_show_board_unauthorize_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user2)->get('/api/boards/' . $boardId);

        $response->assertStatus(403);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_update_board_endpoint(int $boardId): void
    {
        $validatedData = [
            'name' => 'test zmiana nazwy',
        ];

        $response = $this->actingAs($this->user1)->patch('/api/boards/' . $boardId, $validatedData);

        $response->assertStatus(200);

        $this->assertSame($validatedData['name'], json_decode($response->getContent())->data->name);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_update_board_unauthorize_endpoint(int $boardId): void
    {
        $validatedData = [
            'name' => 'test zmiana nazwy',
        ];

        $response = $this->actingAs($this->user2)->patch('/api/boards/' . $boardId, $validatedData);

        $response->assertStatus(403);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_copy_board_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user1)->get('/api/boards/' . $boardId . '/copy');

        $response->assertStatus(201);

        $this->assertNotEquals($boardId, json_decode($response->getContent())->data->id);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_copy_board_unauthorize_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user2)->get('/api/boards/' . $boardId . '/copy');

        $response->assertStatus(403);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_share_board_unauthorize_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user2)->get('/api/boards/' . $boardId . '/share/' . $this->user1->id);

        $response->assertStatus(403);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_share_board_cant_to_myself_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user1)->get('/api/boards/' . $boardId . '/share/' . $this->user1->id);

        $response->assertStatus(403);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_share_board_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user1)->get('/api/boards/' . $boardId . '/share/' . $this->user2->id);

        $response->assertStatus(204);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_update_new_user_board_endpoint(int $boardId): void
    {
        $validatedData = [
            'name' => 'test zmiana nazwy 222',
        ];

        $response = $this->actingAs($this->user2)->patch('/api/boards/' . $boardId, $validatedData);

        $response->assertStatus(200);

        $this->assertSame($validatedData['name'], json_decode($response->getContent())->data->name);
    }

    #[Depends('test_store_board_endpoint')]
    public function test_destroy_new_user_board_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user2)->delete('/api/boards/' . $boardId);

        $response->assertStatus(204);

        $this->assertNotEmpty(Board::where(['id' => $boardId])->first());
    }

    #[Depends('test_store_board_endpoint')]
    public function test_destroy_final_board_endpoint(int $boardId): void
    {
        $response = $this->actingAs($this->user1)->delete('/api/boards/' . $boardId);

        $response->assertStatus(204);

        $this->assertEmpty(Board::where(['id' => $boardId])->first());
    }
}
