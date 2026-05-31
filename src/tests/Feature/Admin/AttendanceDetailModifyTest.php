<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;

class AttendanceDetailModifyTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_attendance_detail_displays_selected_attendance_data()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
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

        $response = $this->get('/admin/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('2026年');
        $response->assertSee('5月10日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
    public function test_admin_error_when_start_time_is_after_end_time()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->from('/admin/attendance/detail/' . $attendance->id)
            ->post('/admin/attendance/detail/' . $attendance->id, [
                'start_time' => '19:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '12:00',
                        'rest_end' => '13:00',
                    ],
                ],
                'note' => '管理者修正',
            ]);

        $response->assertRedirect('/admin/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'start_time',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('出勤時間が不適切な値です');
    }
    public function test_admin_error_when_rest_start_time_is_after_end_time()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->from('/admin/attendance/detail/' . $attendance->id)
            ->post('/admin/attendance/detail/' . $attendance->id, [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '19:00',
                        'rest_end' => '20:00',
                    ],
                ],
                'note' => '管理者修正',
            ]);

        $response->assertRedirect('/admin/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'rests.0.rest_start',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('休憩時間が不適切な値です');
    }
    public function test_admin_error_when_rest_end_time_is_after_end_time()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->from('/admin/attendance/detail/' . $attendance->id)
            ->post('/admin/attendance/detail/' . $attendance->id, [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '17:00',
                        'rest_end' => '19:00',
                    ],
                ],
                'note' => '管理者修正',
            ]);

        $response->assertRedirect('/admin/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'rests.0.rest_end',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('休憩時間もしくは退勤時間が不適切な値です');
    }
    public function test_admin_error_when_note_is_empty()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->from('/admin/attendance/detail/' . $attendance->id)
            ->post('/admin/attendance/detail/' . $attendance->id, [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '12:00',
                        'rest_end' => '13:00',
                    ],
                ],
                'note' => '',
            ]);

        $response->assertRedirect('/admin/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'note',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('備考を記入してください');
    }
}
