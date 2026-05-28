<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_correction_requests_are_displayed()
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
            'status' => 'done',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => today(),
            'status' => 'done',
        ]);

        CorrectionRequest::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'note' => '修正申請1',
            'status' => 'pending',
        ]);

        CorrectionRequest::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'note' => '修正申請2',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('request.list')
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('修正申請1');

        $response->assertSee('佐藤花子');

        $response->assertSee('修正申請2');
    }

    public function test_approved_correction_requests_are_displayed()
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
            'status' => 'done',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => today(),
            'status' => 'done',
        ]);

        CorrectionRequest::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'note' => '承認済み申請1',
            'status' => 'approved',
        ]);

        CorrectionRequest::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'note' => '承認済み申請2',
            'status' => 'approved',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route('request.list', [
                'status' => 'approved',
            ])
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee('承認済み申請1');

        $response->assertSee('佐藤花子');

        $response->assertSee('承認済み申請2');
    }

    public function test_admin_can_view_correction_request_detail()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => now()->setTime(10, 0),
            'requested_clock_out' => now()->setTime(19, 0),
            'note' => '電車遅延のため修正',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route(
                'admin.request.approve',
                $request->id
            )
        );

        $response->assertStatus(200);

        $response->assertSee('山田太郎');

        $response->assertSee(
            today()->format('Y年')
        );

        $response->assertSee(
            today()->format('n月j日')
        );

        $response->assertSee('10:00');

        $response->assertSee('19:00');

        $response->assertSee('電車遅延のため修正');
    }

    public function test_admin_can_approve_correction_request()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => now()->setTime(10, 0),
            'requested_clock_out' => now()->setTime(19, 0),
            'note' => '電車遅延のため修正',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->get(
            route(
                'admin.request.approve',
                $request->id
            )
        );

        $response->assertStatus(200);

        $response->assertSee('承認');

        $response = $this->post(
            route(
                'admin.request.approve.update',
                $request->id
            )
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => now()->setTime(10, 0),
            'clock_out' => now()->setTime(19, 0),
        ]);
    }
}