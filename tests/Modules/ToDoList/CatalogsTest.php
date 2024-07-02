<?php

namespace Tests\ToDoList;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use App\Modules\ToDoList\Boards\Services\BoardService;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use App\Modules\ToDoList\Catalogs\Services\CatalogService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

class CatalogsTest extends TestCase
{
    private CatalogService $catalogService;
    private BoardService $boardService;
    private AuthService $authService;
    private array $data;
    private User $user1;
    private User $user2;


    protected function setUp(): void
    {
        parent::setUp();

        $this->catalogService = new CatalogService();
        $this->boardService = new BoardService();
        $this->authService = new AuthService();

        $this->user1 = User::first();
        $this->user2 = User::whereNot('id', $this->user1->id)->first();
    }

    public function test_store_catalog(): Catalog
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

        return $catalog;
    }

    #[Depends('test_store_catalog')]
    public function test_check_access_true(Catalog $catalog): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertTrue($this->catalogService->checkAccessToCatalog($catalog));
    }

    #[Depends('test_store_catalog')]
    public function test_check_access_false(Catalog $catalog): void
    {
        $this->authService->login(['email' => $this->user2->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->assertFalse($this->catalogService->checkAccessToCatalog($catalog));
    }

    #[Depends('test_store_catalog')]
    public function test_update(Catalog $catalog): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $validatedData = [
            'name' => 'test 123',
        ];

        $this->catalogService->update($validatedData, $catalog);

        $this->assertSame($catalog->name, $validatedData['name']);
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
    #[Depends('test_store_catalog')]
    public function test_update_position(
        int $newPosition,
        int $newPositionOfSecondCatalog,
        int $newPositionOfThirdCatalog,
        Catalog $catalog
    ): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $catalog2 = $catalog->board->catalogs->where('id', '<>', $catalog->id)->first();

        if (!$catalog2) {
            $validatedData = [
                'board_id' => $catalog->board->id,
                'name' => 'test',
            ];
            $catalog2 = $this->catalogService->store($validatedData);
        }

        $catalog3 = $catalog->board->catalogs->where('id', '<>', $catalog->id)->where('id', '<>', $catalog2->id)->first();

        if (!$catalog3) {
            $validatedData = [
                'board_id' => $catalog->board->id,
                'name' => 'test',
            ];
            $catalog3 = $this->catalogService->store($validatedData);
        }

        $catalog->update(['position' => 0]);
        $catalog2->update(['position' => 1]);
        $catalog3->update(['position' => 2]);
        

        $validatedData = [
            'position' => $newPosition,
        ];

        $this->catalogService->update($validatedData, $catalog);

        $this->assertSame($catalog->position, $newPosition);

        $catalog2->refresh();

        $this->assertSame($catalog2->position, $newPositionOfSecondCatalog);

        $catalog3->refresh();

        $this->assertSame($catalog3->position, $newPositionOfThirdCatalog);
    }

    #[Depends('test_store_catalog')]
    public function test_destroy(Catalog $catalog): void
    {
        $this->authService->login(['email' => $this->user1->email, 'password' => '1234']);
        $this->assertAuthenticatedAs(auth()->user());

        $this->catalogService->destroy($catalog);

        $this->assertNull(Catalog::where(['id' => $catalog->id])->first());
    }

    public function test_store_catalog_endpoint(): int
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

        $response = $this->actingAs($this->user1)->post('/api/catalogs', $validatedData);
        
        $response->assertStatus(201);

        return json_decode($response->getContent())->data->id;
    }

    #[Depends('test_store_catalog_endpoint')]
    public function test_show_catalog_endpoint(int $catalogId): void
    {
        $response = $this->actingAs($this->user1)->get('/api/catalogs/' . $catalogId);

        $response->assertStatus(200);
    }

    #[Depends('test_store_catalog_endpoint')]
    public function test_show_catalog_unauthorize_endpoint(int $catalogId): void
    {
        $response = $this->actingAs($this->user2)->get('/api/catalogs/' . $catalogId);

        $response->assertStatus(403);
    }

    #[Depends('test_store_catalog_endpoint')]
    public function test_update_catalog_endpoint(int $catalogId): void
    {
        $validatedData = [
            'name' => 'test zmiana nazwy',
        ];

        $response = $this->actingAs($this->user1)->patch('/api/catalogs/' . $catalogId, $validatedData);

        $response->assertStatus(200);

        $this->assertSame($validatedData['name'], json_decode($response->getContent())->data->name);
    }

    #[Depends('test_store_catalog_endpoint')]
    public function test_update_catalog_unauthorize_endpoint(int $catalogId): void
    {
        $validatedData = [
            'name' => 'test zmiana nazwy',
        ];

        $response = $this->actingAs($this->user2)->patch('/api/catalogs/' . $catalogId, $validatedData);

        $response->assertStatus(403);
    }

    #[Depends('test_store_catalog_endpoint')]
    public function test_destroy_final_catalog_endpoint(int $catalogId): void
    {
        $response = $this->actingAs($this->user1)->delete('/api/catalogs/' . $catalogId);

        $response->assertStatus(204);

        $this->assertEmpty(Catalog::where(['id' => $catalogId])->first());
    }
}
