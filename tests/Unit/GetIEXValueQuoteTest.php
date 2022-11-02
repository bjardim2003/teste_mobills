<?php

namespace Tests\Unit;

use App\Http\Controllers\AcoesController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetIEXValueQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected $token, $symbol;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = "sk_ac16f96d969f44c29bcef70c2868396d";
        $this->symbol = "AAPL";
    }


    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_get_value_quote()
    {
        $quoteData = new AcoesController();
        $quote = $quoteData->getResponseIEX($this->token, $this->symbol);
        $symbolReturn = $quote['data']->symbol;
        $this->assertEquals($this->symbol, $symbolReturn);
    }
}
