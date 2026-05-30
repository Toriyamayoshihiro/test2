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
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;
use App\Enums\AttendanceRequestStatus;


class AttendanceDetailModifyTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_error_when_start_time_is_after_end_time()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/' . $attendance->id)
            ->post('/attendance/detail/' . $attendance->id, [
                'start_time' => '19:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '12:00',
                        'rest_end' => '13:00',
                    ],
                ],
                'note' => '修正申請です',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'start_time',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('出勤時間が不適切な値です');
    }
    public function test_error_when_rest_start_time_is_after_end_time()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/' . $attendance->id)
            ->post('/attendance/detail/' . $attendance->id, [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '19:00',
                        'rest_end' => '20:00',
                    ],
                ],
                'note' => '修正申請です',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'rests.0.rest_start',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('休憩時間が不適切な値です');
    }
    public function test_error_when_rest_end_time_is_after_end_time()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/' . $attendance->id)
            ->post('/attendance/detail/' . $attendance->id, [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'rests' => [
                    [
                        'rest_start' => '17:00',
                        'rest_end' => '19:00',
                    ],
                ],
                'note' => '修正申請です',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'rests.0.rest_end',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('休憩時間もしくは退勤時間が不適切な値です');
    }
    public function test_error_when_note_is_empty()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/' . $attendance->id)
            ->post('/attendance/detail/' . $attendance->id, [
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

        $response->assertRedirect('/attendance/detail/' . $attendance->id);

        $response->assertSessionHasErrors([
            'note',
        ]);

        $this->followRedirects($response)
            ->assertSeeText('備考を記入してください');
    }
    public function test_stamp_correction_request_is_created_and_displayed_for_admin()
    {
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'is_admin' => 0,
        ]);

        $admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $this->post('/attendance/detail/' . $attendance->id, [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'memo' => '電車遅延のため',
            'status' => 0,
        ]);

        $stamp = StampCorrectionRequest::where('attendance_id', $attendance->id)->first();

        $this->actingAs($admin);

        $approveResponse = $this->get('/admin/stamp_correction_request/approve/' . $stamp->id);
        $approveResponse->assertStatus(200);
        $approveResponse->assertSee('電車遅延のため');

        $listResponse = $this->get('/admin/stamp_correction_request/list');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('電車遅延のため');
    }
    public function test_pending_request_list_displays_all_my_requests()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-11',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $this->post('/attendance/detail/' . $attendance1->id, [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],
            'note' => '申請理由1',
        ]);

        $this->post('/attendance/detail/' . $attendance2->id, [
            'start_time' => '10:00',
            'end_time' => '19:00',
            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],
            'note' => '申請理由2',
        ]);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('申請理由1');
        $response->assertSee('申請理由2');
    }
    public function test_approved_request_list_displays_approved_requests()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $stamp = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'request_start_time' => '2026-05-10 10:00:00',
            'request_end_time' => '2026-05-10 19:00:00',
            'memo' => '承認済み申請',
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }
    public function test_request_detail_link_navigates_to_attendance_detail_page()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $stamp = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'request_start_time' => '2026-05-10 10:00:00',
            'request_end_time' => '2026-05-10 19:00:00',
            'memo' => '詳細確認用申請',
            'status' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('/attendance/detail/' . $attendance->id);

        $detailResponse = $this->get('/attendance/detail/' . $attendance->id);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
        $detailResponse->assertSee('詳細確認用申請');
    }
}
