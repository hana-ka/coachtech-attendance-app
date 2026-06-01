<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_cannot_be_after_clock_out()
    {
        $user = User::factory()->create();

        $attendanceDate = today();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $response = $this->from(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        )->post(
            route(
                'correction.store',
                $attendanceDate->format('Y-m-d')
            ),
            [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'break_start' => [],
                'break_end' => [],
                'note' => '修正申請',
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_break_start_cannot_be_after_clock_out()
    {
        $user = User::factory()->create();

        $attendanceDate = today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setTime(12, 0),
            'break_end' => now()->setTime(13, 0),
        ]);

        $this->actingAs($user);

        $response = $this->from(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        )->post(
            route(
                'correction.store',
                $attendanceDate->format('Y-m-d')
            ),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['20:00'],
                'note' => '修正申請',
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_break_end_cannot_be_after_clock_out()
    {
        $user = User::factory()->create();

        $attendanceDate = today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setTime(12, 0),
            'break_end' => now()->setTime(13, 0),
        ]);

        $this->actingAs($user);

        $response = $this->from(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        )->post(
            route(
                'correction.store',
                $attendanceDate->format('Y-m-d')
            ),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['17:00'],
                'break_end' => ['19:00'],
                'note' => '修正申請',
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_note_is_required()
    {
        $user = User::factory()->create();

        $attendanceDate = today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setTime(12, 0),
            'break_end' => now()->setTime(13, 0),
        ]);

        $this->actingAs($user);

        $response = $this->from(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        )->post(
            route(
                'correction.store',
                $attendanceDate->format('Y-m-d')
            ),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => '',
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    public function test_correction_request_is_created_and_displayed_for_admin()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendanceDate = today();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $this->post(
            route(
                'correction.store',
                $attendanceDate->format('Y-m-d')
            ),
            [
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => '修正申請テスト',
            ]
        );

        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $user->id,
            'note' => '修正申請テスト',
        ]);

        $request = CorrectionRequest::first();

        $this->actingAs($admin);

        $response = $this->get(
            route('request.list')
        );

        $response->assertStatus(200);

        $response->assertSee('修正申請テスト');

        $response = $this->get(
            route(
                'admin.request.approve',
                $request->id
            )
        );

        $response->assertStatus(200);

        $response->assertSee('修正申請テスト');

        $response->assertSee('10:00');

        $response->assertSee('19:00');
    }

    public function test_all_pending_requests_are_displayed()
    {
        $user = User::factory()->create();

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDays(2),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'note' => '遅延修正',
            'status' => 'pending',
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'requested_clock_in' => '11:00',
            'requested_clock_out' => '20:00',
            'note' => '勤務時間修正',
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('request.list')
        );

        $response->assertStatus(200);

        $response->assertSee('遅延修正');

        $response->assertSee('勤務時間修正');

        $response->assertSee(
            today()->subDays(2)->format('Y/m/d')
        );

        $response->assertSee(
            today()->subDay()->format('Y/m/d')
        );
    }

    public function test_approved_requests_are_displayed()
    {
        $user = User::factory()->create();

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDays(2),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'note' => '承認済み修正1',
            'status' => 'approved',
        ]);

        CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'requested_clock_in' => '11:00',
            'requested_clock_out' => '20:00',
            'note' => '承認済み修正2',
            'status' => 'approved',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('request.list', ['status' => 'approved'])
        );

        $response->assertStatus(200);

        $response->assertSee('承認済み修正1');

        $response->assertSee('承認済み修正2');

        $response->assertSee(
            today()->subDays(2)->format('Y/m/d')
        );

        $response->assertSee(
            today()->subDay()->format('Y/m/d')
        );
    }

    public function test_user_can_view_correction_request_detail()
    {
        $user = User::factory()->create();

        $attendanceDate = today();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $this->post(
            route(
                'correction.store',
                $attendanceDate->format('Y-m-d')
            ),
            [
                'clock_in' => '10:00',
                'clock_out' => '19:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'note' => '修正申請テスト',
            ]
        );

        $response = $this->get(
            route('request.list')
        );

        $response->assertStatus(200);

        $response->assertSee('詳細');

        $response = $this->get(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        );

        $response->assertStatus(200);

        $response->assertSee('10:00');

        $response->assertSee('19:00');

        $response->assertSee('修正申請テスト');
    }
}