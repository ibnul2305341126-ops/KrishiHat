<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_uses_same_origin_post_route_and_includes_csrf_token(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('action="/register" method="POST"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_buyer_and_farmer_can_register_through_the_post_route(): void
    {
        foreach (['buyer', 'farmer'] as $role) {
            $response = $this->post(route('register.store'), [
                'name' => ucfirst($role) . ' Account',
                'email' => $role . '@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => $role,
            ]);

            $response->assertRedirect(route($role === 'farmer' ? 'farmer.dashboard' : 'home'));
            $this->assertDatabaseHas('users', ['email' => $role . '@example.test', 'role' => $role]);
            $this->post(route('logout'));
        }
    }

    public function test_buyer_can_log_in(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'email' => 'buyer@example.test',
            'password' => 'password123',
        ]);

        $this->post(route('login.store'), [
            'email' => $buyer->email,
            'password' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($buyer);
    }

    public function test_one_browser_session_has_one_current_user_and_role_checks_follow_that_user(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'email' => 'switch-buyer@example.test', 'password' => 'password123']);
        $farmer = User::factory()->create(['role' => 'farmer', 'email' => 'switch-farmer@example.test', 'password' => 'password123']);

        $this->post(route('login.store'), ['email' => $buyer->email, 'password' => 'password123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($buyer);
        $this->get(route('buyer.dashboard'))->assertOk();

        $this->post(route('login.store'), ['email' => $farmer->email, 'password' => 'password123'])
            ->assertRedirect(route('farmer.dashboard'));
        $this->assertAuthenticatedAs($farmer);
        $this->get(route('farmer.dashboard'))->assertOk();
        $this->get(route('buyer.dashboard'))->assertForbidden();

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
