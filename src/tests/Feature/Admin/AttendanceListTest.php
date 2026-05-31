<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\RestTime;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;
use App\Enums\AttendanceRequestStatus;

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user1 = User::factory()->create([
            'name' => 'ユーザー1',
            'is_admin' => 0,
        ]);

        $user2 = User::factory()->create([
            'name' => 'ユーザー2',
            'is_admin' => 0,
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $attendance1->id,
            'rest_start' => '2026-05-10 12:00:00',
            'rest_end' => '2026-05-10 13:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-05-10',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $attendance2->id,
            'rest_start' => '2026-05-10 14:00:00',
            'rest_end' => '2026-05-10 15:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list?date=2026-05-10');

        $response->assertStatus(200);

        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('ユーザー2');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }
    public function test_admin_attendance_list_displays_current_date()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 10, 12, 0, 0, 'Asia/Tokyo'));

        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('2026年 5月10日');

        Carbon::setTestNow();
    }
    public function test_admin_can_see_previous_day_attendance()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '前日ユーザー',
            'is_admin' => 0,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-09',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list?date=2026-05-09');

        $response->assertStatus(200);

        $response->assertSee('2026年 5月9日');
        $response->assertSee('前日ユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
    public function test_admin_can_see_next_day_attendance()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '翌日ユーザー',
            'is_admin' => 0,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-11',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/list?date=2026-05-11');

        $response->assertStatus(200);

        $response->assertSee('2026年 5月11日');
        $response->assertSee('翌日ユーザー');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}
