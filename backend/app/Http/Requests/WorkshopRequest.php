<?php

namespace App\Http\Requests;

use App\Enums\WorkshopStatusTypeEnum;
use App\Models\Workshop;
use Illuminate\Foundation\Http\FormRequest;

class WorkshopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Workshop::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $requiredOrNot = $isUpdate ? '' : 'required';
        return [
            'title' => [$requiredOrNot, 'string', 'max:255'],
            'description' => [$requiredOrNot, 'string', 'max:1024'],
            'start_at' => [$requiredOrNot, 'date:date_format:Y-m-d', 'after_or_equal:today'],
            'end_at' => [$requiredOrNot, 'date:date_format:Y-m-d', 'after:start_at'],
            'pin_code' => ['sometimes', 'string', 'max:6', 'regex:/^[0-9]{6}$/', 'unique:workshops,pin_code'],
            'qr_status' => [$requiredOrNot, 'boolean'],
            'status' => [$requiredOrNot, 'in:'. implode(',', WorkshopStatusTypeEnum::values())],
        ];
    }
}
