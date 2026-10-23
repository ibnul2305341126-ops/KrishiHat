<?php

namespace Tests\Feature;

use Tests\TestCase;

class PreviewScreensTest extends TestCase
{
    public function test_forecast_screen_explains_which_live_data_is_unavailable(): void
    {
        $this->get(route('forecast.index'))
            ->assertOk()
            ->assertSee('PRICE FORECAST PREVIEW')
            ->assertSee('historical market data')
            ->assertSee('Unavailable');
    }

    public function test_proposed_subscription_prices_are_not_actionable(): void
    {
        $this->get(route('plans.index'))
            ->assertOk()
            ->assertSee('PROPOSED PLAN PRICING')
            ->assertSee('Free Farmer')
            ->assertSee('Growth Agro')
            ->assertSee('AgriEnterprise')
            ->assertSee('৳499')
            ->assertSee('৳1,299')
            ->assertSee('No payment will be collected')
            ->assertSee('disabled', false);
    }
}
