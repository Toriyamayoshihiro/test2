<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_register_fails_when_name_is_empty()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'name',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('お名前を入力してください');
    }
    public function test_register_fails_when_email_is_empty()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'testuser',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('メールアドレスを入力してください');
    }
    public function test_register_fails_when_password_is_less_than_8_characters()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'password',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('パスワードは8文字以上で入力してください');
    }
    public function test_register_fails_when_password_confirmation_does_not_match()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'password',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('パスワードと一致しません');
    }
    public function test_register_fails_when_password_is_empty()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'password',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('パスワードを入力してください');
    }
    

    public function test_user_can_register_successfully()
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }
}
