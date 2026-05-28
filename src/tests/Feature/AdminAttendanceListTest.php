<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users_attendance()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'work_date' => today(),
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
            'user_id' => $user2->id,
            'work_date' => today(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => now()->setTime(14, 0),
            'break_end' => now()->setTime(15, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.list')
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertSee('01:00');

        $response->assertSee('08:00');

        $response->assertSee('佐藤花子');

        $response->assertSee('10:00');

        $response->assertSee('19:00');
    }

    public function test_current_date_is_displayed_on_admin_attendance_list()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.list')
        );

        $response->assertStatus(200);

        $response->assertSee(
            today()->format('Y/m/d')
        );
    }

    public function test_previous_day_attendance_is_displayed()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.list', [
                'date' => today()->subDay()->format('Y-m-d')
            ])
        );

        $response->assertStatus(200);

        $response->assertSee(
            today()->subDay()->format('Y/m/d')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertDontSee('10:00');

        $response->assertDontSee('19:00');
    }

    public function test_next_day_attendance_is_displayed()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->addDay(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
            'status' => 'done',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.list', [
                'date' => today()->addDay()->format('Y-m-d')
            ])
        );

        $response->assertStatus(200);

        $response->assertSee(
            today()->addDay()->format('Y/m/d')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertDontSee('10:00');

        $response->assertDontSee('19:00');
    }
}