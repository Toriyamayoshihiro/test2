<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;


class UserInformationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_see_all_general_users_name_and_email()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user1 = User::factory()->create([
            'name' => '一般ユーザー1',
            'email' => 'user1@example.com',
            'is_admin' => 0,
        ]);

        $user2 = User::factory()->create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'is_admin' => 0,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('一般ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('一般ユーザー2');
        $response->assertSee('user2@example.com');

        $response->assertDontSee($admin->email);
    }
    public function test_admin_can_see_selected_staff_attendance_list()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '対象ユーザー',
            'is_admin' => 0,
        ]);

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

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=2026-05');

        $response->assertStatus(200);

        $response->assertSee('対象ユーザーさんの勤怠');
        $response->assertSee('2026/05');
        $response->assertSee('05/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }
    public function test_admin_can_see_previous_month_staff_attendance()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '前月ユーザー',
            'is_admin' => 0,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        $response->assertStatus(200);

        $response->assertSee('2026/04');
        $response->assertSee('04/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
    public function test_admin_can_see_next_month_staff_attendance()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '翌月ユーザー',
            'is_admin' => 0,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-06-10',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?month=2026-06');

        $response->assertStatus(200);

        $response->assertSee('2026/06');
        $response->assertSee('06/10');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}
