<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_date_time_is_displayed()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee(now()->isoFormat('YYYY年M月D日(ddd)'));
    }

    public function test_status_is_off()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'status' => 'off',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_status_is_working()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_status_is_break()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'break',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_status_is_done()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }

    public function test_user_can_clock_in()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay(),
            'status' => 'off',
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_clock_in_button_is_not_displayed_after_work_done()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'done',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    public function test_clock_in_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $response = $this->get('/attendance/list');

        $response->assertSee(now()->format('H:i'));
    }

    public function test_user_can_start_break()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('休憩入');

        $response = $this->post('/break/start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'break',
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_user_can_take_break_multiple_times()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $this->post('/break/start');

        $this->post('/break/end');

        $response = $this->get('/attendance');

        $response->assertSee('休憩入');
    }

    public function test_user_can_end_break()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $this->post('/break/start');

        $response = $this->get('/attendance');

        $response->assertSee('休憩戻');

        $response = $this->post('/break/end');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_user_can_end_break_multiple_times()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $this->post('/break/start');

        $this->post('/break/end');

        $this->post('/break/start');

        $response = $this->get('/attendance');

        $response->assertSee('休憩戻');
    }

    public function test_break_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $this->post('/break/start');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $break = $attendance->breakTimes->first();

        $break->update([
            'break_start' => now()->subMinutes(30),
            'break_end' => now(),
        ]);

        $this->post('/break/end');

        $response = $this->get('/attendance/list');

        $response->assertSee('00:30');
    }

    public function test_user_can_clock_out()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('退勤');

        $response = $this->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'done',
        ]);

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }

    public function test_clock_out_time_is_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay()->toDateString(),
            'status' => 'off',
        ]);

        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $this->post('/attendance/clock-out');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $response = $this->get('/attendance/list');

        $response->assertSee(
            $attendance->clock_out->format('H:i')
        );
    }
}