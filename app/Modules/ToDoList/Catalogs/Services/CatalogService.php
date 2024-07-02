<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Catalogs\Services;

use App\Modules\ToDoList\Catalogs\Models\Catalog;
use Illuminate\Support\Carbon;

class CatalogService
{
    public function checkAccessToCatalog(Catalog $catalog): bool
    {
        return (bool)$catalog->board->users()->firstWhere('users.id', auth()->user()->getAuthIdentifier());
    }

    public function store(array $validatedData): Catalog
    {        
        $position = Catalog::select('position')
            ->where('board_id', $validatedData['board_id'])
            ->max('position');
        
        if (is_null($position)) {
            $position = 0;
        } else {
            $position++;
        }

        $validatedData['position'] = $position;
        $catalog = Catalog::create($validatedData);
        $catalog->board()->update(['updated_at' => Carbon::now()]);

        return $catalog;
    }

    public function update(array $validatedData, Catalog $catalog): Catalog
    {
        if (isset($validatedData['position'])) {
            if ($validatedData['position'] < $catalog->position) {
                Catalog::where('board_id', $catalog->board_id)
                    ->where('position', '>=', $validatedData['position'])
                    ->where('position', '<', $catalog->position)
                    ->increment('position');
            } else {
                Catalog::where('board_id', $catalog->board_id)
                    ->where('position', '<=', $validatedData['position'])
                    ->where('position', '>', $catalog->position)
                    ->decrement('position');
            }
        }

        $catalog->update($validatedData);
        $catalog->board()->update(['updated_at' => Carbon::now()]);

        return $catalog;
    }

    public function destroy(Catalog $catalog): void
    {        
        $boardId = $catalog->board_id;
        $position = $catalog->position;

        $catalog->board()->update(['updated_at' => Carbon::now()]);
        $catalog->delete();

        Catalog::where('board_id', $boardId)
            ->where('position', '>', $position)
            ->decrement('position');
    }
}