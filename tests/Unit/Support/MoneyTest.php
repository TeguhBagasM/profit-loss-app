<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

class MoneyTest extends TestCase
{
    public function test_add_memakai_bcmath_dan_akurat_untuk_desimal(): void
    {
        // 0.1 + 0.2 gagal pada float, harus tepat dengan BCMath.
        $this->assertSame('0.30', Money::add('0.10', '0.20'));
        $this->assertSame('1500.00', Money::add('1000.00', '500'));
        $this->assertSame('-250.75', Money::add('100.00', '-350.75'));
    }

    public function test_sub(): void
    {
        $this->assertSame('0.20', Money::sub('0.50', '0.30'));
        $this->assertSame('-100.00', Money::sub('400.00', '500.00'));
    }

    public function test_compare(): void
    {
        $this->assertSame(0, Money::compare('10.00', '10'));
        $this->assertSame(1, Money::compare('10.01', '10.00'));
        $this->assertSame(-1, Money::compare('-0.01', '0.00'));
    }

    public function test_format_memakai_pemisah_lokal_indonesia(): void
    {
        $this->assertSame('0,00', Money::format('0'));
        $this->assertSame('1.234.567,50', Money::format('1234567.5'));
        $this->assertSame('-999,99', Money::format('-999.99'));
        $this->assertSame('1.000,00', Money::format(1000));
    }

    public function test_float_ditolak(): void
    {
        $this->expectException(TypeError::class);

        Money::add('1.00', 1.5);
    }

    public function test_nilai_bukan_angka_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::format('1e5');
    }
}
