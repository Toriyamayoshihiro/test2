<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\RestTime;

class RestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_rest_start_button_works_correctly()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Working->value,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'start_time' => now('Asia/Tokyo')->format('H:i:s'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩入');

        $this->post('/attendance', [
            'action' => 'in_rest',
        ]);

        $user->refresh();

        $this->assertEquals(
            AttendanceStatus::Resting->value,
            $user->attendance_status
        );

        $this->assertDatabaseHas('rest_times', [
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }
    public function test_user_can_take_multiple_breaks_in_a_day()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Working->value,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'start_time' => now('Asia/Tokyo')->format('H:i:s'),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'in_rest',
        ]);

        $this->post('/attendance', [
            'action' => 'out_rest',
        ]);

        $user->refresh();

        $this->assertEquals(
            AttendanceStatus::Working->value,
            $user->attendance_status
        );

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩入');
    }
    public function test_rest_end_button_works_correctly()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Working->value,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'start_time' => now('Asia/Tokyo')->format('H:i:s'),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'in_rest',
        ]);

        $user->refresh();

        $this->assertEquals(
            AttendanceStatus::Resting->value,
            $user->attendance_status
        );

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        $this->post('/attendance', [
            'action' => 'out_rest',
        ]);

        $user->refresh();

        $this->assertEquals(
            AttendanceStatus::Working->value,
            $user->attendance_status
        );

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }
    public function test_user_can_return_from_break_multiple_times_in_a_day()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Working->value,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'start_time' => now('Asia/Tokyo')->format('H:i:s'),
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'in_rest',
        ]);

        $this->post('/attendance', [
            'action' => 'out_rest',
        ]);

        $this->post('/attendance', [
            'action' => 'in_rest',
        ]);

        $user->refresh();

        $this->assertEquals(
            AttendanceStatus::Resting->value,
            $user->attendance_status
        );

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('休憩戻');
    }
    public function test_rest_time_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 9, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Off->value,
        ]);

        $this->actingAs($user);

        $this->post('/attendance', [
            'action' => 'in_attendance',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 5, 1, 12, 0, 0, 'Asia/Tokyo'));

        $this->post('/attendance', [
            'action' => 'in_rest',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 5, 1, 13, 0, 0, 'Asia/Tokyo'));

        $this->post('/attendance', [
            'action' => 'out_rest',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 5, 1, 18, 0, 0, 'Asia/Tokyo'));

        $this->post('/attendance', [
            'action' => 'out_attendance',
        ]);

        $response = $this->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);

        $response->assertSee('1:00');

        Carbon::setTestNow();
    }
}
