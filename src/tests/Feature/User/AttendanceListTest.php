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

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_all_my_attendances_are_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $attendance1->id,
            'rest_start' => '2026-05-01 12:00:00',
            'rest_end' => '2026-05-01 13:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-02',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $attendance2->id,
            'rest_start' => '2026-05-02 14:00:00',
            'rest_end' => '2026-05-02 15:00:00',
        ]);

        $response = $this->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);

        $response->assertSee('05/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('05/02');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }
    public function test_current_month_is_displayed_on_attendance_list_page()
    {
        Carbon::setTestNow(
            Carbon::create(2026, 5, 15, 12, 0, 0, 'Asia/Tokyo')
        );

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('2026/05');

        Carbon::setTestNow();
    }
    public function test_previous_month_attendance_is_displayed_when_previous_month_button_is_clicked()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);

        $response->assertSee('2026/05');

        $response->assertSee('05/10');

        $response->assertSee('09:00');

        $response->assertSee('18:00');
    }
    public function test_next_month_data_is_displayed_when_next_month_button_is_clicked()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-06-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        
        $response = $this->get('/attendance/list?month=2026-06');

        $response->assertStatus(200);

        $response->assertSee('2026/06');

        $response->assertSee('06/10');

        $response->assertSee('09:00');

        $response->assertSee('18:00');
    }
    public function test_user_can_move_to_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('/attendance/detail/' . $attendance->id);

        $detailResponse = $this->get('/attendance/detail/' . $attendance->id);

        $detailResponse->assertStatus(200);

        $detailResponse->assertSee('勤怠詳細');
    }
}
