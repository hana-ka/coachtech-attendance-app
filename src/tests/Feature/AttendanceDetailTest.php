<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BreakTime;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_name_is_displayed_on_attendance_detail()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
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

        $response = $this->get(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
    }

    public function test_selected_date_is_displayed_on_attendance_detail()
    {
        $user = User::factory()->create();

        $attendanceDate = today()->subDay();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $attendanceDate,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        );

        $response->assertStatus(200);

        $response->assertSee(
            $attendanceDate->format('Y年')
        );

        $response->assertSee(
            $attendanceDate->format('n月j日')
        );
    }

    public function test_clock_in_and_clock_out_times_are_displayed()
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

        $response = $this->get(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        );

        $response->assertStatus(200);

        $response->assertSee('09:00');

        $response->assertSee('18:00');
    }

    public function test_break_times_are_displayed()
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

        $response = $this->get(
            route(
                'attendance.detail',
                $attendanceDate->format('Y-m-d')
            )
        );

        $response->assertStatus(200);

        $response->assertSee('12:00');

        $response->assertSee('13:00');
    }
}