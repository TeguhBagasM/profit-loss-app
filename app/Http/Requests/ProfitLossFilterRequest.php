<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ProfitLossReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProfitLossFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m'],
            'to' => ['nullable', 'date_format:Y-m'],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $from = $this->month($this->input('from'));
                $to = $this->month($this->input('to'));

                if ($from === null || $to === null) {
                    return;
                }

                if ($to->lt($from)) {
                    $validator->errors()->add('to', 'Bulan akhir tidak boleh lebih awal dari bulan awal.');

                    return;
                }

                $months = (($to->year - $from->year) * 12 + ($to->month - $from->month)) + 1;

                if ($months > ProfitLossReportService::MAX_MONTHS) {
                    $validator->errors()->add(
                        'to',
                        'Rentang laporan maksimal '.ProfitLossReportService::MAX_MONTHS.' bulan.',
                    );
                }
            },
        ];
    }

    private function month(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return CarbonImmutable::createFromFormat('!Y-m', $value);
    }
}
