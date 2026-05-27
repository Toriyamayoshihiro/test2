<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class DateGetTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_current_datetime_is_displayed_on_attendance_page()
    {
        $user = User::factory()->create([
            'attendance_status' => 0,
        ]);

        $this->actingAs($user);

        $now = \Carbon\Carbon::now('Asia/Tokyo');

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee(
            $now->locale('ja')->isoFormat('YYYY年M月D日(ddd)')
        );

        $response->assertSee(
            $now->format('H:i')
        );
    }
}
