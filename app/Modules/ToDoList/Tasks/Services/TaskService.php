<?php

declare(strict_types=1);

namespace App\Modules\ToDoList\Tasks\Services;

use App\Modules\Assets\Comments\Models\Comment;
use App\Modules\Assets\Media\Requests\MediaRequest;
use App\Modules\Assets\Media\Resources\MediaResource;
use App\Modules\ToDoList\Tasks\Models\Task;
use Illuminate\Support\Carbon;

class TaskService
{
    public function checkAccessToTask(Task $task): bool
    {
        return (bool)$task->catalog->board->users()->firstWhere('users.id', auth()->user()->getAuthIdentifier());
    }

    public function checkAccessToComment(Comment $comment): bool
    {
        return (bool)$comment->model->catalog->board->users()->firstWhere('users.id', auth()->user()->getAuthIdentifier());
    }

    public function store(array $validatedData): Task
    {
        $position = Task::select('position')
            ->where('catalog_id', $validatedData['catalog_id'])
            ->max('position');
        
        if (is_null($position)) {
            $position = 0;
        } else {
            $position++;
        }

        $validatedData['position'] = $position;

        $task = Task::create($validatedData);
        $task->catalog->board()->update(['updated_at' => Carbon::now()]);

        return $task;
    }

    public function update(array $validatedData, Task $task): Task
    {
        if (isset($validatedData['position'])) {
            if ($validatedData['catalog_id'] == $task->catalog_id) {
                if ($validatedData['position'] < $task->position) {
                    Task::where('catalog_id', $validatedData['catalog_id'])
                        ->where('position', '>=', $validatedData['position'])
                        ->where('position', '<', $task->position)
                        ->increment('position');
                } else {
                    Task::where('catalog_id', $validatedData['catalog_id'])
                        ->where('position', '<=', $validatedData['position'])
                        ->where('position', '>', $task->position)
                        ->decrement('position');
                }
            } else {
                Task::where('catalog_id', $validatedData['catalog_id'])
                    ->where('position', '>=', $validatedData['position'])
                    ->increment('position');
                
                Task::where('catalog_id', $task->catalog_id)
                    ->where('position', '>', $task->position)
                    ->decrement('position');
            }
        }
        
        $task->update($validatedData);
        $task->catalog->board()->update(['updated_at' => Carbon::now()]);

        return $task;
    }

    public function destroy(Task $task): void
    {
        $catalogId = $task->catalog_id;
        $position = $task->position;

        $task->catalog->board()->update(['updated_at' => Carbon::now()]);
        $task->delete();

        Task::where('catalog_id', $catalogId)
            ->where('position', '>', $position)
            ->decrement('position');
    }

    public function addComment(Task $task, array $validatedData): Comment
    {
        $comment = $task->comments()->create(
            [
                'description' => $validatedData['description'],
            ]
        );
        $task->catalog->board()->update(['updated_at' => Carbon::now()]);
        return $comment;
    }

    public function editComment(Comment $comment, array $validatedData): Comment
    {
        $comment->update($validatedData);
        $comment->model->catalog->board()->update(['updated_at' => Carbon::now()]);

        return $comment;
    }

    public function removeComment(Comment $comment): void
    {
        $comment->model->catalog->board()->update(['updated_at' => Carbon::now()]);
        $comment->delete();
    }

    public function addCommentAttachment(Comment $comment, MediaRequest $request): MediaResource|false
    {
        if($request->hasFile('file') && $request->file('file')->isValid()){
            $comment->model->catalog->board()->update(['updated_at' => Carbon::now()]);
            return new MediaResource($comment->addMediaFromRequest('file')->toMediaCollection('comment_attachments'));
        }

        return false;
    }

    public function removeCommentAttachment(Comment $comment, int $id): void
    {        
        $comment->deleteMedia($id);
        $comment->model->catalog->board()->update(['updated_at' => Carbon::now()]);
    }

    public function addAttachment(Task $task, MediaRequest $request): MediaResource|false
    {
        if($request->hasFile('file') && $request->file('file')->isValid()){
            $task->catalog->board()->update(['updated_at' => Carbon::now()]);
            return new MediaResource($task->addMediaFromRequest('file')->toMediaCollection('task_attachments'));
        }

        return false;
    }

    public function removeAttachment(Task $task, int $id): void
    {        
        $task->deleteMedia($id);
        $task->catalog->board()->update(['updated_at' => Carbon::now()]);
    }
}