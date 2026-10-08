<?php

namespace App\DTO;

use App\Http\Requests\StoretaskRequest;
use App\Models\Task;
use Spatie\LaravelData\Data;

class TaskDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description = "",
        public string $priority,
        public string $status,
        public ?DateTime $due_date = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'title'=> $this->title(),
            'description' => $this->description(),
            'priority' => $this->priority(),
            'status' => $this->status,
            'due_date'=> $this->due_date,
        ];
    }

    public static function fromRequest(StoretaskRequest $request)
    {
        return new self(
            id: null,
            title: $request->validated('title'),
            description: $request->validated('description'),
            priority: $request->validated('priority'),
            status: $request->validated('status'),
            due_date: $request->validated('due_date'),
        );
    }
}
