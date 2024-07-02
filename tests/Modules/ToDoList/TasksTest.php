<?php

namespace Tests\ToDoList;

use App\Modules\Assets\Comments\Models\Comment;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use App\Modules\ToDoList\Boards\Services\BoardService;
use App\Modules\ToDoList\Catalogs\Services\CatalogService;
use App\Modules\ToDoList\Tasks\Models\Task;
use App\Modules\ToDoList\Tasks\Services\TaskService;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

class TasksTest extends TestCase
{
    private TaskService $taskService;
    private CatalogService $catalogService;
    private BoardService $boardService;
    private AuthService $authService;
    private array $data;
    private User $user1;
    private User $user2;


    protected function setUp(): void
    {
        parent::setUp();

        $this->taskService = new TaskService();
        $this->catalogService = new CatalogService();
        $this->boardService = new BoardService();
        $this->authService = new AuthService();

        $this->user1 = User::first();
        $this->user2 = User::whereNot('id', $this->user1->id)->first();
    }

    public function test_store_task(): Task
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $validatedData = [
            'name' => 'test',
        ];

        $board = $this->boardService->store($validatedData);

        $this->assertNotNull($board);

        $validatedData = [
            'board_id' => $board->id,
            'name' => 'test',
        ];

        $catalog = $this->catalogService->store($validatedData);

        $this->assertNotNull($catalog);

        $validatedData = [
            'catalog_id' => $catalog->id,
            'name' => 'test',
        ];

        $task = $this->taskService->store($validatedData);

        return $task;
    }

    #[Depends('test_store_task')]
    public function test_check_access_true(Task $task): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertTrue($this->taskService->checkAccessToTask($task));
    }

    #[Depends('test_store_task')]
    public function test_check_access_false(Task $task): void
    {
        $this->authService->login(['email' => $this->user2->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertFalse($this->taskService->checkAccessToTask($task));
    }

    #[Depends('test_store_task')]
    public function test_add_comment_to_task(Task $task): Comment
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $comment = $this->taskService->addComment($task, ['description' => 'opis']);

        $this->assertNotNull($comment);
        
        $task->refresh();
        $this->assertNotEmpty($task->comments);

        return $comment;
    }

    #[Depends('test_add_comment_to_task')]
    public function test_check_access_to_comment_true(Comment $comment): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertTrue($this->taskService->checkAccessToComment($comment));
    }

    #[Depends('test_add_comment_to_task')]
    public function test_check_access_to_comment_false(Comment $comment): void
    {
        $this->authService->login(['email' => $this->user2->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertFalse($this->taskService->checkAccessToComment($comment));
    }

    #[Depends('test_add_comment_to_task')]
    public function test_destroy_comment(Comment $comment): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $task = $comment->model;

        $this->taskService->removeComment($comment);

        $task->refresh();

        $this->assertEmpty($task->comments);
    }

    #[Depends('test_store_task')]
    public function test_update(Task $task): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $validatedData = [
            'catalog_id' => $task->catalog->id,
            'name' => 'test 123',
        ];

        $this->taskService->update($validatedData, $task);

        $this->assertSame($task->name, $validatedData['name']);
    }

    public static function update_position_provider(): array
    {
        return [
            [1, 0, 2],
            [2, 0, 1],
            [0, 1, 2],
        ];
    }

    #[DataProvider('update_position_provider')]
    #[Depends('test_store_task')]
    public function test_update_position(
        int $newPosition,
        int $newPositionOfSecondCatalog,
        int $newPositionOfThirdCatalog,
        Task $task
    ): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $task2 = $task->catalog->tasks()->where('id', '<>', $task->id)->first();

        if (!$task2) {
            $validatedData = [
                'catalog_id' => $task->catalog->id,
                'name' => 'test',
            ];
            $task2 = $this->taskService->store($validatedData);
        }

        $task3 = $task->catalog->tasks()->where('id', '<>', $task->id)->where('id', '<>', $task2->id)->first();

        if (!$task3) {
            $validatedData = [
                'catalog_id' => $task->catalog->id,
                'name' => 'test',
            ];
            $task3 = $this->taskService->store($validatedData);
        }

        $task->update(['position' => 0]);
        $task2->update(['position' => 1]);
        $task3->update(['position' => 2]);
        

        $validatedData = [
            'catalog_id' => $task->catalog->id,
            'position' => $newPosition,
        ];

        $this->taskService->update($validatedData, $task);

        $this->assertSame($task->position, $newPosition);

        $task2->refresh();

        $this->assertSame($task2->position, $newPositionOfSecondCatalog);

        $task3->refresh();

        $this->assertSame($task3->position, $newPositionOfThirdCatalog);
    }

    public static function update_position_and_catalog_provider(): array
    {
        return [
            [1, 0, 1, 2, 0, 1],
            [1, 1, 0, 2, 0, 1],
            [1, 2, 0, 1, 0, 1],
            [2, 0, 0, 1, 1, 2],
            [2, 1, 0, 1, 0, 2],
            [2, 2, 0, 1, 0, 1],
        ];
    }

    #[DataProvider('update_position_and_catalog_provider')]
    #[Depends('test_store_task')]
    public function test_update_position_and_catalog_when_task_on_first_place(
        int $catalogNo,
        int $newPosition,
        int $newPositionOfSecondTaskFirstCatalog,
        int $newPositionOfThirdTaskFirstCatalog,
        int $newPositionOfFirstTaskSecondCatalog,
        int $newPositionOfSecondTaskSecondCatalog,
        Task $task
    ): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        // ustawienie domyślnych wartości dla tasków (1,2,3 w jednym katalogu w kolejności rosnącej, zawsze tak samo na starcie)
        // ustawienie domyślnych wartości dla tasków (4,5 w drugin katalogu w kolejności rosnącej, zawsze tak samo na starcie)
        $catalog = $task->catalog->board->catalogs()->first();
        $task->update(['catalog_id' => $catalog->id]);

        $task2 = $catalog->tasks()->where('id', '<>', $task->id)->first();

        if (!$task2) {
            $validatedData = [
                'catalog_id' => $catalog->id,
                'name' => 'test',
            ];
            $task2 = $this->taskService->store($validatedData);
        }

        $task3 = $catalog->tasks()->where('id', '<>', $task->id)->where('id', '<>', $task2->id)->first();

        if (!$task3) {
            $validatedData = [
                'catalog_id' => $catalog->id,
                'name' => 'test',
            ];
            $task3 = $this->taskService->store($validatedData);
        }

        $task->update(['position' => 0]);
        $task2->update(['position' => 1]);
        $task3->update(['position' => 2]);

        $catalog2 = $catalog->board->catalogs()->firstWhere('id', '<>', $catalog->id);
        if (!$catalog2) {
            $validatedData = [
                'board_id' => $catalog->board->id,
                'name' => 'test',
            ];
            $catalog2 = $this->catalogService->store($validatedData);
        }

        $task4 = $catalog2->tasks()->first();

        if (!$task4) {
            $validatedData = [
                'catalog_id' => $catalog2->id,
                'name' => 'test',
            ];
            $task4 = $this->taskService->store($validatedData);
        }

        $task5 = $catalog2->tasks()->where('id', '<>', $task4->id)->first();

        if (!$task5) {
            $validatedData = [
                'catalog_id' => $catalog2->id,
                'name' => 'test',
            ];
            $task5 = $this->taskService->store($validatedData);
        }

        $task4->update(['position' => 0]);
        $task5->update(['position' => 1]);
        

        $validatedData = [
            'catalog_id' => $catalogNo === 1 ? $catalog->id : $catalog2->id,
            'position' => $newPosition,
        ];

        $this->taskService->update($validatedData, $task);

        $this->assertSame($task->position, $newPosition);
        $this->assertSame($task->catalog_id, $catalogNo === 1 ? $catalog->id : $catalog2->id);

        $task2->refresh();

        $this->assertSame($task2->position, $newPositionOfSecondTaskFirstCatalog);

        $task3->refresh();

        $this->assertSame($task3->position, $newPositionOfThirdTaskFirstCatalog);

        $task4->refresh();

        $this->assertSame($task4->position, $newPositionOfFirstTaskSecondCatalog);

        $task5->refresh();

        $this->assertSame($task5->position, $newPositionOfSecondTaskSecondCatalog);
    }

    #[DataProvider('update_position_and_catalog_provider')]
    #[Depends('test_store_task')]
    public function test_update_position_and_catalog_when_task_position_in_the_middle(
        int $catalogNo,
        int $newPosition,
        int $newPositionOfSecondTaskFirstCatalog,
        int $newPositionOfThirdTaskFirstCatalog,
        int $newPositionOfFirstTaskSecondCatalog,
        int $newPositionOfSecondTaskSecondCatalog,
        Task $task
    ): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        // ustawienie domyślnych wartości dla tasków (1,2,3 w jednym katalogu w kolejności rosnącej, zawsze tak samo na starcie)
        // ustawienie domyślnych wartości dla tasków (4,5 w drugin katalogu w kolejności rosnącej, zawsze tak samo na starcie)
        $catalog = $task->catalog->board->catalogs()->first();
        $task->update(['catalog_id' => $catalog->id]);

        $task2 = $catalog->tasks()->where('id', '<>', $task->id)->first();

        if (!$task2) {
            $validatedData = [
                'catalog_id' => $catalog->id,
                'name' => 'test',
            ];
            $task2 = $this->taskService->store($validatedData);
        }

        $task3 = $catalog->tasks()->where('id', '<>', $task->id)->where('id', '<>', $task2->id)->first();

        if (!$task3) {
            $validatedData = [
                'catalog_id' => $catalog->id,
                'name' => 'test',
            ];
            $task3 = $this->taskService->store($validatedData);
        }

        $task->update(['position' => 1]);
        $task2->update(['position' => 0]);
        $task3->update(['position' => 2]);

        $catalog2 = $catalog->board->catalogs()->firstWhere('id', '<>', $catalog->id);
        if (!$catalog2) {
            $validatedData = [
                'board_id' => $catalog->board->id,
                'name' => 'test',
            ];
            $catalog2 = $this->catalogService->store($validatedData);
        }

        $task4 = $catalog2->tasks()->first();

        if (!$task4) {
            $validatedData = [
                'catalog_id' => $catalog2->id,
                'name' => 'test',
            ];
            $task4 = $this->taskService->store($validatedData);
        }

        $task5 = $catalog2->tasks()->where('id', '<>', $task4->id)->first();

        if (!$task5) {
            $validatedData = [
                'catalog_id' => $catalog2->id,
                'name' => 'test',
            ];
            $task5 = $this->taskService->store($validatedData);
        }

        $task4->update(['position' => 0]);
        $task5->update(['position' => 1]);
        

        $validatedData = [
            'catalog_id' => $catalogNo === 1 ? $catalog->id : $catalog2->id,
            'position' => $newPosition,
        ];

        $this->taskService->update($validatedData, $task);

        $this->assertSame($task->position, $newPosition);
        $this->assertSame($task->catalog_id, $catalogNo === 1 ? $catalog->id : $catalog2->id);

        $task2->refresh();

        $this->assertSame($task2->position, $newPositionOfSecondTaskFirstCatalog);

        $task3->refresh();

        $this->assertSame($task3->position, $newPositionOfThirdTaskFirstCatalog);

        $task4->refresh();

        $this->assertSame($task4->position, $newPositionOfFirstTaskSecondCatalog);

        $task5->refresh();

        $this->assertSame($task5->position, $newPositionOfSecondTaskSecondCatalog);
    }

    #[DataProvider('update_position_and_catalog_provider')]
    #[Depends('test_store_task')]
    public function test_update_position_and_catalog_when_task_at_the_end(
        int $catalogNo,
        int $newPosition,
        int $newPositionOfSecondTaskFirstCatalog,
        int $newPositionOfThirdTaskFirstCatalog,
        int $newPositionOfFirstTaskSecondCatalog,
        int $newPositionOfSecondTaskSecondCatalog,
        Task $task
    ): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        // ustawienie domyślnych wartości dla tasków (1,2,3 w jednym katalogu w kolejności rosnącej, zawsze tak samo na starcie)
        // ustawienie domyślnych wartości dla tasków (4,5 w drugin katalogu w kolejności rosnącej, zawsze tak samo na starcie)
        $catalog = $task->catalog->board->catalogs()->first();
        $task->update(['catalog_id' => $catalog->id]);

        $task2 = $catalog->tasks()->where('id', '<>', $task->id)->first();

        if (!$task2) {
            $validatedData = [
                'catalog_id' => $catalog->id,
                'name' => 'test',
            ];
            $task2 = $this->taskService->store($validatedData);
        }

        $task3 = $catalog->tasks()->where('id', '<>', $task->id)->where('id', '<>', $task2->id)->first();

        if (!$task3) {
            $validatedData = [
                'catalog_id' => $catalog->id,
                'name' => 'test',
            ];
            $task3 = $this->taskService->store($validatedData);
        }

        $task->update(['position' => 2]);
        $task2->update(['position' => 0]);
        $task3->update(['position' => 1]);

        $catalog2 = $catalog->board->catalogs()->firstWhere('id', '<>', $catalog->id);
        if (!$catalog2) {
            $validatedData = [
                'board_id' => $catalog->board->id,
                'name' => 'test',
            ];
            $catalog2 = $this->catalogService->store($validatedData);
        }

        $task4 = $catalog2->tasks()->first();

        if (!$task4) {
            $validatedData = [
                'catalog_id' => $catalog2->id,
                'name' => 'test',
            ];
            $task4 = $this->taskService->store($validatedData);
        }

        $task5 = $catalog2->tasks()->where('id', '<>', $task4->id)->first();

        if (!$task5) {
            $validatedData = [
                'catalog_id' => $catalog2->id,
                'name' => 'test',
            ];
            $task5 = $this->taskService->store($validatedData);
        }

        $task4->update(['position' => 0]);
        $task5->update(['position' => 1]);
        

        $validatedData = [
            'catalog_id' => $catalogNo === 1 ? $catalog->id : $catalog2->id,
            'position' => $newPosition,
        ];

        $this->taskService->update($validatedData, $task);

        $this->assertSame($task->position, $newPosition);
        $this->assertSame($task->catalog_id, $catalogNo === 1 ? $catalog->id : $catalog2->id);

        $task2->refresh();

        $this->assertSame($task2->position, $newPositionOfSecondTaskFirstCatalog);

        $task3->refresh();

        $this->assertSame($task3->position, $newPositionOfThirdTaskFirstCatalog);

        $task4->refresh();

        $this->assertSame($task4->position, $newPositionOfFirstTaskSecondCatalog);

        $task5->refresh();

        $this->assertSame($task5->position, $newPositionOfSecondTaskSecondCatalog);
    }

    #[Depends('test_store_task')]
    public function test_destroy(Task $task): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->taskService->destroy($task);

        $this->assertNull(Task::where(['id' => $task->id])->first());
    }

    public function test_store_task_endpoint(): int
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $validatedData = [
            'name' => Str::random(16),
        ];

        $board = $this->boardService->store($validatedData);

        $this->assertNotNull($board);

        $validatedData = [
            'board_id' => $board->id,
            'name' => Str::random(16),
        ];

        $catalog = $this->catalogService->store($validatedData);

        $validatedData = [
            'catalog_id' => $catalog->id,
            'name' => Str::random(16),
        ];

        $response = $this->actingAs($this->user1)->post('/api/tasks', $validatedData);
        
        $response->assertStatus(201);

        return json_decode($response->getContent())->data->id;
    }

    #[Depends('test_store_task_endpoint')]
    public function test_show_task_endpoint(int $taskId): void
    {
        $response = $this->actingAs($this->user1)->get('/api/tasks/' . $taskId);

        $response->assertStatus(200);
    }

    #[Depends('test_store_task_endpoint')]
    public function test_show_task_unauthorize_endpoint(int $taskId): void
    {
        $response = $this->actingAs($this->user2)->get('/api/tasks/' . $taskId);

        $response->assertStatus(403);
    }

    #[Depends('test_store_task_endpoint')]
    public function test_update_task_endpoint(int $taskId): void
    {
        $validatedData = [
            'catalog_id' => Task::where('id', $taskId)->first()->catalog_id,
            'name' => 'test zmiana nazwy',
        ];

        $response = $this->actingAs($this->user1)->patch('/api/tasks/' . $taskId, $validatedData);

        $response->assertStatus(200);

        $this->assertSame($validatedData['name'], json_decode($response->getContent())->data->name);
    }

    #[Depends('test_store_task_endpoint')]
    public function test_update_task_unauthorize_endpoint(int $taskId): void
    {
        $validatedData = [
            'name' => 'test zmiana nazwy',
        ];

        $response = $this->actingAs($this->user2)->patch('/api/tasks/' . $taskId, $validatedData);

        $response->assertStatus(403);
    }

    #[Depends('test_store_task_endpoint')]
    public function test_destroy_final_task_endpoint(int $taskId): void
    {
        $response = $this->actingAs($this->user1)->delete('/api/tasks/' . $taskId);

        $response->assertStatus(204);

        $this->assertEmpty(Task::where(['id' => $taskId])->first());
    }

}
