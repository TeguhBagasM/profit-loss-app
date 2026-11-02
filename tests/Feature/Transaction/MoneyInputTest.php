<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyInputTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ChartOfAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'name' => 'Transport Expense',
            'type' => Category::TYPE_EXPENSE,
        ]);

        $this->account = ChartOfAccount::create([
            'code' => '603',
            'name' => 'Transport Expense',
            'category_id' => $category->id,
        ]);
    }

    public function test_form_create_memakai_hidden_input_untuk_debit_dan_credit(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('transactions.create'))
            ->assertOk()
            ->getContent();

        foreach (['debit', 'credit'] as $field) {
            $this->assertSame(1, substr_count($html, 'name="'.$field.'"'), "Input {$field} harus tepat satu.");
            $this->assertSame(1, substr_count($html, 'for="'.$field.'"'), "Label {$field} harus terhubung.");
            $this->assertSame(1, substr_count($html, 'id="'.$field.'"'), "Input terlihat {$field} harus punya id.");

            preg_match_all('/<input[^>]*name="'.$field.'"[^>]*>/', $html, $matches);

            $this->assertStringContainsString('type="hidden"', $matches[0][0]);
        }

        // Hanya debit dan credit yang memakai input nominal berformat.
        $this->assertSame(2, substr_count($html, 'inputmode="decimal"'));
        $this->assertSame(2, substr_count($html, 'moneyInput('));
    }

    public function test_form_edit_memakai_hidden_input_dengan_nilai_mentah(): void
    {
        $transaction = Transaction::create([
            'date' => '2022-01-02',
            'chart_of_account_id' => $this->account->id,
            'description' => 'Transport Expense',
            'debit' => '25000.00',
            'credit' => '0.00',
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('transactions.edit', $transaction))
            ->assertOk()
            ->getContent();

        foreach (['debit', 'credit'] as $field) {
            $this->assertSame(1, substr_count($html, 'name="'.$field.'"'));
        }

        $this->assertSame(2, substr_count($html, 'inputmode="decimal"'));

        preg_match_all('/<input[^>]*name="debit"[^>]*>/', $html, $matches);

        $this->assertStringContainsString('type="hidden"', $matches[0][0]);
        $this->assertStringContainsString('value="25000.00"', $matches[0][0]);
    }

    public function test_nilai_mentah_dari_input_tersembunyi_tetap_tersimpan_benar(): void
    {
        $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'date' => '2022-01-10',
                'chart_of_account_id' => $this->account->id,
                'description' => 'Bensin',
                'debit' => '1000.50',
                'credit' => '',
            ])
            ->assertRedirect(route('transactions.index'));

        $transaction = Transaction::query()->firstOrFail();

        $this->assertSame('1000.50', $transaction->debit);
        $this->assertSame('0.00', $transaction->credit);
    }

    public function test_nilai_tidak_valid_tetap_ditolak(): void
    {
        $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'date' => '2022-01-10',
                'chart_of_account_id' => $this->account->id,
                'description' => 'Bensin',
                'debit' => '0',
                'credit' => '0',
            ])
            ->assertSessionHasErrors(['debit', 'credit']);

        $this->assertSame(0, Transaction::query()->count());
    }
}
