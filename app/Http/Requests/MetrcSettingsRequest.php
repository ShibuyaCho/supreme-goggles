<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MetrcSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-compliance'); // define your Gate/Policy
    }

    public function rules(): array
    {
        return [
            'environment'        => 'required|in:sandbox,production',
            'base_url'           => 'required|url',
            'integrator_key'     => 'required|string',
            'user_key'           => 'required|string',
            'facility_license'   => 'nullable|string',
            'is_active'          => 'sometimes|boolean',
            'enabled_live_sync'  => 'sometimes|boolean',
        ];
    }
}
?>