<?php

namespace App\DTO;

use App\Http\Requests\StoretaskRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdatetaskRequest;
use App\Models\Task;
use Spatie\LaravelData\Data;
use DateTime;

class TaskStatusDTO
{
    public function __construct(
        public string $status
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
        ];
    }

    public static function fromRequest(StoretaskRequest|UpdatetaskRequest|UpdateStatusRequest $request)
    {
        return new self(
            status: $request->validated('status'),
        );
    }
}
