<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Catalogs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ToDoList\Catalogs\Requests\StoreCatalogRequest;
use App\Modules\ToDoList\Catalogs\Requests\UpdateCatalogRequest;
use App\Modules\ToDoList\Sync\Requests\SyncRequest;
use App\Modules\ToDoList\Catalogs\Resources\ApiCatalogResource;
use App\Modules\ToDoList\Catalogs\Models\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class CatalogController extends Controller
{
    public function syncUpdated(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiCatalogResource::collection(
            Catalog::select(['id', 'name'])
                ->where('updated_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->get()
        );
    }

    public function syncDeleted(SyncRequest $request): AnonymousResourceCollection
    {
        return ApiCatalogResource::collection(
            Catalog::select(['id', 'name'])
                ->where('deleted_at', '>=', Carbon::createFromTimestamp($request->get('date')))
                ->onlyTrashed()
                ->get()
        );
    }

    public function indexAll(): AnonymousResourceCollection
    {
        return ApiCatalogResource::collection(Catalog::select(['id', 'name'])->get());
    }

    public function index(): AnonymousResourceCollection
    {
        $catalogs = Catalog::select(['id', 'name'])->paginate();

        return ApiCatalogResource::collection($catalogs);
    }

    public function store(StoreCatalogRequest $request): ApiCatalogResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));

        return new ApiCatalogResource(Catalog::create($data));
    }

    public function show(Catalog $catalog): ApiCatalogResource
    {
        return new ApiCatalogResource($catalog);
    }

    public function update(UpdateCatalogRequest $request, Catalog $catalog): ApiCatalogResource
    {
        $data = $request->validated();
        $data['name'] = mb_strtolower(trim($data['name']));
        
        $catalog->update($data);

        return new ApiCatalogResource($catalog);
    }

    public function destroy(Catalog $catalog): JsonResponse
    {
        $catalog->delete();

        return response()->json(null, 204);
    }
}