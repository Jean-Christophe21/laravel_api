<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoretaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'max:50'],
            'description' => ['nullable', 'max:250'],
            'priority' => ['required', Rule::in("low", "medium", "high")],
            'status' => ['required', Rule::in("todo", "in_progress", "done")],
            'due_date' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'priority.required' => "la :attribute est requise",
            'priority.in' => "la :attribute doit contenir 'todo', 'in_progress', 'done' ",
            'title.max' => 'le champ :attribute est limité à 50 caractères',
            'title.required' => 'le champ :attribute est requis',
            'status.in' => 'le champ :attribute doit appartenir à "todo", "in_progress", "done"',
            'status.required' => 'le champ :attribute est obligatoire'
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'titre',
            'priority' => 'priorité',
            'description' => 'description',
            'status' => 'statut',
            'due_date' => 'date de fin'
        ];
    }
}
