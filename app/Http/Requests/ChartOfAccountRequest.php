<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Satu FormRequest dipakai untuk store dan update.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $chartOfAccount = $this->route('chartOfAccount');

        return [
            'code' => [
                'required',
                'string',
                'regex:/^[A-Za-z0-9.\-]{1,20}$/',
                Rule::unique('chart_of_accounts', 'code')->ignore($chartOfAccount),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode COA wajib diisi.',
            'code.regex' => 'Kode COA hanya boleh berisi huruf, angka, titik, dan strip (1-20 karakter).',
            'code.unique' => 'Kode COA ini sudah dipakai.',
            'name.required' => 'Nama COA wajib diisi.',
            'name.max' => 'Nama COA maksimal 150 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
        ];
    }
}
