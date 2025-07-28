<?php

namespace App\Http\Requests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $requiredOrNot = $isUpdate ? '' : 'required';

        $allowedLinkableTypes = array_keys(Relation::morphMap());
        return [
            'title' => [$requiredOrNot, 'string', 'max:255'],
            'title_ar' => [$requiredOrNot, 'string', 'max:255'],
            'description' => [$requiredOrNot, 'string', 'max:1000'],
            'description_ar' => [$requiredOrNot, 'string', 'max:1000'],
            'setting_id' => [$requiredOrNot, 'uuid', 'exists:settings,id'],
            'linkable_type' => [$requiredOrNot, 'string', 'in:' . implode(',', $allowedLinkableTypes)],
            'linkable_id' => [
                $requiredOrNot,
                'uuid',
                function ($attribute, $value, $fail) {
                    $linkableType = $this->input('linkable_type');

                    if (!$linkableType) {
                        return;
                    }

                    $morphMap = Relation::morphMap();

                    if (!isset($morphMap[$linkableType])) {
                        $fail('Invalid linkable_type provided.');
                        return;
                    }

                    $modelClass = $morphMap[$linkableType];

                    if (!$modelClass::where('id', $value)->exists()) {
                        $fail("The selected {$attribute} is invalid for the given linkable_type.");
                    }
                }
            ],
        ];
    }
}
