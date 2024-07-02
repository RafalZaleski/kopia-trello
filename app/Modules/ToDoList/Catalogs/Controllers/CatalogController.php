<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Catalogs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ToDoList\Boards\Models\Board;
use App\Modules\ToDoList\Boards\Services\BoardService;
use App\Modules\ToDoList\Catalogs\Requests\StoreCatalogRequest;
use App\Modules\ToDoList\Catalogs\Requests\UpdateCatalogRequest;
use App\Modules\ToDoList\Catalogs\Resources\ApiCatalogResource;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use App\Modules\ToDoList\Catalogs\Services\CatalogService;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalogService,
        private readonly BoardService $boardService,
    ) {
    }

    public function store(StoreCatalogRequest $request): ApiCatalogResource|JsonResponse
    {
        $validatedData = $request->validated();
        
        $board = Board::where(['id' => $validatedData['board_id']])->firstOrFail();

        if (!$this->boardService->checkAccessToBoard($board)) {
            return response()->json(null, 403);
        }
        
        $catalog = $this->catalogService->store($validatedData);

        return new ApiCatalogResource($catalog);
    }

    public function show(Catalog $catalog): ApiCatalogResource|JsonResponse
    {
        if (!$this->catalogService->checkAccessToCatalog($catalog)) {
            return response()->json(null, 403);
        }

        return new ApiCatalogResource($catalog);
    }

    public function update(UpdateCatalogRequest $request, Catalog $catalog): ApiCatalogResource|JsonResponse
    {
        $validatedData = $request->validated();

        if (!$this->catalogService->checkAccessToCatalog($catalog)) {
            return response()->json(null, 403);
        }
           
        $catalog = $this->catalogService->update($validatedData, $catalog);

        return new ApiCatalogResource($catalog);
    }

    public function destroy(Catalog $catalog): JsonResponse
    {
        if (!$this->catalogService->checkAccessToCatalog($catalog)) {
            return response()->json(null, 403);
        }
        
        $this->catalogService->destroy($catalog);

        return response()->json(null, 204);
    }
}