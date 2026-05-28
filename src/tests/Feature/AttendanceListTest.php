<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_attendance_records_are_displayed()
    {
        $user = User::factory()->create();

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDays(2),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->setTime(12, 0),
            'break_end' => now()->setTime(13, 0),
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => now()->setTime(14, 0),
            'break_end' => now()->setTime(15, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee(
            today()->subDays(2)->format('m/d')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertSee('01:00');

        $response->assertSee('08:00');

        $response->assertSee(
            today()->subDay()->format('m/d')
        );

        $response->assertSee('10:00');

        $response->assertSee('19:00');
    }

    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee(
            now()->format('Y/m')
        );
    }

    public function test_previous_month_attendance_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->subMonth()->startOfMonth(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->startOfMonth(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->get("/attendance/list?month={$previousMonth}");

        $response->assertStatus(200);

        $response->assertSee(
            now()->subMonth()->format('Y/m')
        );

        $response->assertSee('09:00');

        $response->assertDontSee('10:00');
    }

    public function test_next_month_attendance_is_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->addMonth()->startOfMonth(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->startOfMonth(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->get("/attendance/list?month={$nextMonth}");

        $response->assertStatus(200);

        $response->assertSee(
            now()->addMonth()->format('Y/m')
        );

        $response->assertSee('09:00');

        $response->assertDontSee('10:00');
    }

    public function test_user_can_view_attendance_detail()
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
            $attendanceDate->format('n月j日')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');
    }
}