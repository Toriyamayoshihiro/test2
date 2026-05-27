<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;


class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_login_fails_when_email_is_empty()
    {
        User::create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('メールアドレスを入力してください');
    }
    public function test_login_fails_when_password_is_empty()
    {
        User::create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors([
            'password',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('パスワードを入力してください');
    }
    public function test_login_fails_when_email_is_incorrect()
    {
        User::create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');

        $this->followRedirects($response)
            ->assertSeeText('ログイン情報が登録されていません');
    }
}
