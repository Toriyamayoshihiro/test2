<?php

namespace Tests\Feature\Mailhog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class MailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }
    public function test_verify_email_page_has_link_to_mailhog()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertStatus(200);

        $response->assertSee('認証はこちらから');
        $response->assertSee('http://localhost:8025', false);
    }
    public function test_user_is_redirected_to_attendance_after_email_verification()
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);

        Event::assertDispatched(Verified::class);
    }
}
