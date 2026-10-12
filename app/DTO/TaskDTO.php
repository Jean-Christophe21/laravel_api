<?php

namespace App\DTO;

use App\Http\Requests\StoretaskRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdatetaskRequest;
use App\Models\Task;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use DateTime;

class TaskDTO
{
    public function __construct(
        public string $title,
        public ?string $description = "",
        public string $priority,
        public string $status,
        public ?DateTime $due_date = null,
    ) {}

    public function toArray(): array
    {
        return [
            'title'=> $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date'=> $this->due_date,
        ];
    }

    public static function fromRequest(StoretaskRequest|UpdatetaskRequest|UpdateStatusRequest $request)
    {
        return new self(
            title: $request->validated('title'),
            description: $request->validated('description'),
            priority: $request->validated('priority'),
            status: $request->validated('status'),
            due_date: Carbon::parse($request->validated('due_date')),
        );
    }
}
