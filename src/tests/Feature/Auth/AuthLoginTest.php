<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;


class AuthLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_login_fails_when_email_is_empty()
    {
        User::create([
            'name' => 'adminuser',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('メールアドレスを入力してください');
    }
    public function test_admin_login_fails_when_password_is_empty()
{
        User::create([
            'name' => 'adminuser',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');

        $response->assertSessionHasErrors([
            'password',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('パスワードを入力してください');
    }
    public function test_admin_login_fails_when_credentials_are_incorrect()
    {
        User::create([
            'name' => 'adminuser',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');

        $this->followRedirects($response)
            ->assertSeeText('ログイン情報が登録されていません');
    }
}
