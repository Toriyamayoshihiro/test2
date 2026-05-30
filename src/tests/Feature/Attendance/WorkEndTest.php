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

class WorkEndTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_clock_out_button_works_correctly()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Working->value,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Tokyo')->toDateString(),
            'start_time' => '09:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $attendance->id,
            'rest_start' => now('Asia/Tokyo')->setTime(12, 0),
            'rest_end' => now('Asia/Tokyo')->setTime(13, 0),
        ]);

        $this->actingAs($user);

        
        $response = $this->get('/attendance');

        $response->assertStatus(200);

        
        $response->assertSee('退勤');

        
        $this->post('/attendance', [
            'action' => 'out_attendance',
        ]);

        $user->refresh();

        
        $this->assertEquals(
            AttendanceStatus::Off->value,
            $user->attendance_status
        );

        
        $attendance->refresh();

        $this->assertNotNull($attendance->end_time);

        
        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }
    public function test_clock_out_time_is_displayed_in_attendance_list()
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

        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}
