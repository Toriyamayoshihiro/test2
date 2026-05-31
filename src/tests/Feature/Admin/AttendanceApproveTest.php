<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;
use App\Models\RestTime;
use App\Enums\AttendanceRequestStatus;

class AttendanceApproveTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_admin_can_see_all_pending_stamp_correction_requests()
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

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-05-11',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'request_start_time' => '2026-05-10 09:30:00',
            'request_end_time' => '2026-05-10 18:30:00',
            'memo' => '承認待ち申請1',
            'status' => AttendanceRequestStatus::Pending->value,
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'request_start_time' => '2026-05-11 10:30:00',
            'request_end_time' => '2026-05-11 19:30:00',
            'memo' => '承認待ち申請2',
            'status' => AttendanceRequestStatus::Pending->value,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('承認待ち申請1');
        $response->assertSee('ユーザー2');
        $response->assertSee('承認待ち申請2');
    }
    public function test_admin_can_see_all_approved_stamp_correction_requests()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user1 = User::factory()->create([
            'name' => '承認済みユーザー1',
            'is_admin' => 0,
        ]);

        $user2 = User::factory()->create([
            'name' => '承認済みユーザー2',
            'is_admin' => 0,
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-05-11',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'request_start_time' => '2026-05-10 09:30:00',
            'request_end_time' => '2026-05-10 18:30:00',
            'memo' => '承認済み申請1',
            'status' => AttendanceRequestStatus::Approved,
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'request_start_time' => '2026-05-11 10:30:00',
            'request_end_time' => '2026-05-11 19:30:00',
            'memo' => '承認済み申請2',
            'status' => AttendanceRequestStatus::Approved,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みユーザー1');
        $response->assertSee('承認済み申請1');
        $response->assertSee('承認済みユーザー2');
        $response->assertSee('承認済み申請2');
    }
    public function test_admin_can_see_stamp_correction_request_detail()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '申請者',
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
            'memo' => '申請詳細確認',
            'status' => AttendanceRequestStatus::Pending->value,
        ]);

        RestStampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'stamp_correction_request_id' => $stamp->id,
            'request_rest_start' => '2026-05-10 13:00:00',
            'request_rest_end' => '2026-05-10 14:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/approve/' . $stamp->id);

        $response->assertStatus(200);
        $response->assertSee('申請者');
        $response->assertSee('2026年');
        $response->assertSee('5月10日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('13:00');
        $response->assertSee('14:00');
        $response->assertSee('申請詳細確認');
    }
    public function test_admin_can_approve_stamp_correction_request()
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

        RestTime::create([
            'attendance_id' => $attendance->id,
            'rest_start' => '2026-05-10 12:00:00',
            'rest_end' => '2026-05-10 13:00:00',
        ]);

        $stamp = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'request_start_time' => '2026-05-10 10:00:00',
            'request_end_time' => '2026-05-10 19:00:00',
            'memo' => '承認処理確認',
            'status' => AttendanceRequestStatus::Pending->value,
        ]);

        RestStampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'stamp_correction_request_id' => $stamp->id,
            'request_rest_start' => '2026-05-10 13:00:00',
            'request_rest_end' => '2026-05-10 14:00:00',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/stamp_correction_request/approve/' . $stamp->id);

        $response->assertRedirect('/admin/stamp_correction_request/approve/' . $stamp->id);

        $stamp->refresh();
        $attendance->refresh();

        $this->assertEquals(
            AttendanceRequestStatus::Approved->value,
            $stamp->status->value
        );

        $this->assertEquals('10:00', $attendance->start_time->format('H:i'));
        $this->assertEquals('19:00', $attendance->end_time->format('H:i'));

        $this->assertDatabaseHas('rest_times', [
            'attendance_id' => $attendance->id,
            'rest_start' => '2026-05-10 13:00:00',
            'rest_end' => '2026-05-10 14:00:00',
        ]);

        $this->assertDatabaseMissing('rest_times', [
            'attendance_id' => $attendance->id,
            'rest_start' => '2026-05-10 12:00:00',
            'rest_end' => '2026-05-10 13:00:00',
        ]);
    }

}
