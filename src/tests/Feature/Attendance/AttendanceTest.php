<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_user_can_clock_in()
    {
        $user = User::factory()->create([
            'attendance_status' => AttendanceStatus::Off->value,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');

        
        $this->post('/attendance', [
            'action' => 'in_attendance',
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
    public function test_user_cannot_clock_in_twice_in_one_day()
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

        
        $response->assertDontSee('出勤');
    }


public function test_clock_in_time_is_displayed_in_attendance_list()
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

        $response->assertSee('09:00');
    }
}
