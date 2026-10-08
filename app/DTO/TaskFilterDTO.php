<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class TaskFilterDTO
{
    public function __construct (
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $title = null,
        public ?int $page = null,
        public ?int $per_page = 10,
    ) { }

    public static function fromRequest(Request $request)
    {
        return new self(
            status: $request->validated('status'),
            priority: $request->validated('priority'),
            title: $request->validated('title'),
            page: $request->validated('page'),
            per_page: $request->validated('per_page'),
        );
    }

    public function toArray(): array
    {
        return (array_filter([
            'status' => $this->status,
            'priority' => $this->priority,
            'title' => $this->title,
            'page' => $this->page,
            'per_page' => $this->per_page,
        ], fn($value)=> $value != null ));
    }
}
