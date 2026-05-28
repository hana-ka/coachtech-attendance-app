<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_selected_attendance_detail()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

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

        $this->actingAs($admin);

        $response = $this->get(
            route(
                'admin.attendance.detail',
                $user->id
            ) . '?date=' . $attendanceDate->format('Y-m-d')
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee(
            $attendanceDate->format('Y年')
        );

        $response->assertSee(
            $attendanceDate->format('n月j日')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertSee('12:00');

        $response->assertSee('13:00');
    }

    public function test_clock_in_cannot_be_after_clock_out_for_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendanceDate = today();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $this->actingAs($admin);

        $response = $this->from(
            route(
                'admin.attendance.detail',
                $user->id
            ) . '?date=' . $attendanceDate->format('Y-m-d')
        )->post(
            route(
                'admin.attendance.update',
                $user->id
            ),
            [
                'date' => $attendanceDate->format('Y-m-d'),
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_break_start_cannot_be_after_clock_out_for_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

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

        $this->actingAs($admin);

        $response = $this->from(
            route(
                'admin.attendance.detail',
                $user->id
            ) . '?date=' . $attendanceDate->format('Y-m-d')
        )->post(
            route(
                'admin.attendance.update',
                $user->id
            ),
            [
                'date' => $attendanceDate->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['20:00'],
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_break_end_cannot_be_after_clock_out_for_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

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

        $this->actingAs($admin);

        $response = $this->from(
            route(
                'admin.attendance.detail',
                $user->id
            ) . '?date=' . $attendanceDate->format('Y-m-d')
        )->post(
            route(
                'admin.attendance.update',
                $user->id
            ),
            [
                'date' => $attendanceDate->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['17:00'],
                'break_end' => ['19:00'],
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_note_is_required_for_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

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

        $this->actingAs($admin);

        $response = $this->from(
            route(
                'admin.attendance.detail',
                $user->id
            ) . '?date=' . $attendanceDate->format('Y-m-d')
        )->post(
            route(
                'admin.attendance.update',
                $user->id
            ),
            [
                'date' => $attendanceDate->format('Y-m-d'),
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
}