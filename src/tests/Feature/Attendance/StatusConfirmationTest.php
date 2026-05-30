<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;

class StatusConfirmationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_status_is_displayed_as_off_when_user_is_off_duty()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Off->value,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }
    public function test_status_is_displayed_as_working_when_user_is_working()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Working->value,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('出勤中');
    }
    public function test_status_is_displayed_as_resting_when_user_is_resting()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Resting->value,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }
    public function test_status_is_displayed_as_finished_when_user_has_clocked_out()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Off->value,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('退勤済');
    }
}
