<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_staff_information()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'role' => 'user',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'role' => 'user',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.staff.list')
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('yamada@example.com');

        $response->assertSee('佐藤花子');

        $response->assertSee('sato@example.com');
    }

    public function test_admin_can_view_selected_user_attendance()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
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
            route('admin.staff.list')
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('詳細');

        $response = $this->get(
            route(
                'admin.staff.attendance',
                $user->id
            )
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee(
            today()->format('m/d')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertSee('01:00');

        $response->assertSee('08:00');
    }

    public function test_previous_month_attendance_is_displayed_for_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

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

        $this->actingAs($admin);

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->get(
            route(
                'admin.staff.attendance',
                $user->id
            ) . '?month=' . $previousMonth
        );

        $response->assertStatus(200);

        $response->assertSee(
            now()->subMonth()->format('Y/m')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertDontSee('10:00');

        $response->assertDontSee('19:00');
    }

    public function test_next_month_attendance_is_displayed_for_admin()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

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

        $this->actingAs($admin);

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->get(
            route(
                'admin.staff.attendance',
                $user->id
            ) . '?month=' . $nextMonth
        );

        $response->assertStatus(200);

        $response->assertSee(
            now()->addMonth()->format('Y/m')
        );

        $response->assertSee('09:00');

        $response->assertSee('18:00');

        $response->assertDontSee('10:00');

        $response->assertDontSee('19:00');
    }

    public function test_admin_can_view_attendance_detail_from_staff_attendance_list()
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

        $this->actingAs($admin);

        $response = $this->get(
            route(
                'admin.staff.attendance',
                $user->id
            )
        );

        $response->assertStatus(200);

        $response->assertSee('詳細');

        $response = $this->get(
            route(
                'admin.attendance.detail',
                $user->id
            ) . '?date=' . $attendanceDate->format('Y-m-d')
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('09:00');

        $response->assertSee('18:00');
    }
}