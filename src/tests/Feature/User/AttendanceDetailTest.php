<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\RestTime;

class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_detail_displays_logged_in_user_name()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }
    public function test_attendance_detail_displays_selected_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('2026 5月10日');
    }
    public function test_attendance_detail_displays_clock_in_and_clock_out_time()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
    public function test_attendance_detail_displays_rest_time()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $attendance->id,
            'rest_start' => '2026-05-10 12:00:00',
            'rest_end' => '2026-05-10 13:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
