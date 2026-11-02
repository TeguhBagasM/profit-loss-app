<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransactionRequest extends FormRequest
{
    private const MAX_AMOUNT = '9999999999999.99';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Input nominal kosong dinormalisasi menjadi 0 sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'debit' => $this->normalizeAmount($this->input('debit')),
            'credit' => $this->normalizeAmount($this->input('credit')),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $amountRules = ['required', 'numeric', 'min:0', 'max:'.self::MAX_AMOUNT, 'decimal:0,2'];

        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'chart_of_account_id' => ['required', 'integer', Rule::exists('chart_of_accounts', 'id')],
            'description' => ['nullable', 'string', 'max:255'],
            'debit' => $amountRules,
            'credit' => $amountRules,
        ];
    }

    /**
     * Tepat salah satu dari debit/credit harus lebih besar dari 0.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $hasDebit = Money::compare((string) $this->input('debit'), '0.00') > 0;
                $hasCredit = Money::compare((string) $this->input('credit'), '0.00') > 0;

                if ($hasDebit && $hasCredit) {
                    foreach (['debit', 'credit'] as $field) {
                        $validator->errors()->add(
                            $field,
                            'Debit dan credit tidak boleh sama-sama terisi. Isi salah satu saja: debit untuk pengeluaran, credit untuk pemasukan.',
                        );
                    }

                    return;
                }

                if (! $hasDebit && ! $hasCredit) {
                    foreach (['debit', 'credit'] as $field) {
                        $validator->errors()->add(
                            $field,
                            'Jumlah transaksi tidak boleh 0. Isi debit (pengeluaran) atau credit (pemasukan) lebih dari 0.',
                        );
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date_format' => 'Tanggal harus berformat Y-m-d (contoh: 2022-01-31).',
            'chart_of_account_id.required' => 'COA wajib dipilih.',
            'chart_of_account_id.exists' => 'COA yang dipilih tidak ditemukan.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'debit.required' => 'Debit wajib diisi (boleh 0).',
            'debit.numeric' => 'Debit harus berupa angka.',
            'debit.min' => 'Debit tidak boleh negatif.',
            'debit.max' => 'Debit maksimal 9.999.999.999.999,99.',
            'debit.decimal' => 'Debit maksimal 2 angka di belakang koma.',
            'credit.required' => 'Credit wajib diisi (boleh 0).',
            'credit.numeric' => 'Credit harus berupa angka.',
            'credit.min' => 'Credit tidak boleh negatif.',
            'credit.max' => 'Credit maksimal 9.999.999.999.999,99.',
            'credit.decimal' => 'Credit maksimal 2 angka di belakang koma.',
        ];
    }

    private function normalizeAmount(mixed $value): mixed
    {
        if ($value === null) {
            return '0';
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? '0' : $trimmed;
        }

        return $value;
    }
}
