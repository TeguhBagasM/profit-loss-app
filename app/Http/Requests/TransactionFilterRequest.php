<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Parameter query daftar transaksi divalidasi, tidak dibaca mentah dari request().
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'chart_of_account_id' => ['nullable', 'integer', Rule::exists('chart_of_accounts', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'max:20'],
            'direction' => ['nullable', 'in:asc,desc'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_from.date_format' => 'Tanggal awal harus berformat Y-m-d.',
            'date_to.date_format' => 'Tanggal akhir harus berformat Y-m-d.',
            'chart_of_account_id.exists' => 'COA yang dipilih tidak ditemukan.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'direction.in' => 'Arah urutan harus asc atau desc.',
        ];
    }
}
