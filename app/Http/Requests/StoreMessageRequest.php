<?php

namespace App\Http\Requests;


use App\Models\Message;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');
        Gate::authorize('send', [Message::class, $conversation]);
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
            'body' => ['required_without:attachments', 'string'],
            'attachments' => ['required_without:body', 'array'],
            'attachments.*' => ['file', 'mimes:jpeg,jpg,png,gif,pdf,doc,docx', 'max:10240'],
        ];
    }
}
