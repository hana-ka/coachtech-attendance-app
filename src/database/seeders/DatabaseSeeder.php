<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestBreak;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user2 = User::create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user3 = User::create([
            'name' => '一般ユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->subDays(1)->toDateString(),
            'clock_in' => now()->subDays(1)->setHour(9),
            'clock_out' => now()->subDays(1)->setHour(18),
            'status' => 'off',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->subDays(1)->setHour(12),
            'break_end' => now()->subDays(1)->setHour(13),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->subDays(1)->setHour(15),
            'break_end' => now()->subDays(1)->setHour(15)->addMinutes(30),
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3),
            'clock_out' => null,
            'status' => 'working',
        ]);

        $attendance3 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->subDays(2)->toDateString(),
            'clock_in' => now()->subDays(2)->setHour(9),
            'clock_out' => null,
            'status' => 'break',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance3->id,
            'break_start' => now()->subDays(2)->setHour(12),
            'break_end' => null,
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'status' => 'pending',
            'requested_clock_in' => now()->subDays(1)->setHour(8),
            'requested_clock_out' => now()->subDays(1)->setHour(18),
            'note' => '打刻忘れのため修正',
        ]);

        CorrectionRequestBreak::create([
            'correction_request_id' => $request->id,
            'break_start' => now()->subDays(1)->setHour(11),
            'break_end' => now()->subDays(1)->setHour(12),
        ]);

        $attendance4 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->subMonth()->setDay(15)->toDateString(),
            'clock_in' => now()->subMonth()->setDay(15)->setHour(9),
            'clock_out' => now()->subMonth()->setDay(15)->setHour(18),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance4->id,
            'break_start' => now()->subMonth()->setDay(15)->setHour(14),
            'break_end' => now()->subMonth()->setDay(15)->setHour(15),
        ]);

        $attendance5 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->addMonth()->setDay(10)->toDateString(),
            'clock_in' => now()->addMonth()->setDay(10)->setHour(9),
            'clock_out' => now()->addMonth()->setDay(10)->setHour(18),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance5->id,
            'break_start' => now()->addMonth()->setDay(10)->setHour(14),
            'break_end' => now()->addMonth()->setDay(10)->setHour(15),
        ]);

        $attendance6 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => now()->subDays(3)->toDateString(),
            'clock_in' => now()->subDays(3)->setHour(9),
            'clock_out' => now()->subDays(3)->setHour(18),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance6->id,
            'break_start' => now()->subDays(3)->setHour(12),
            'break_end' => now()->subDays(3)->setHour(13),
        ]);

        $attendance7 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => now()->subDays(4)->toDateString(),
            'clock_in' => now()->subDays(4)->setHour(8),
            'clock_out' => now()->subDays(4)->setHour(17),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance7->id,
            'break_start' => now()->subDays(4)->setHour(12),
            'break_end' => now()->subDays(4)->setHour(13),
        ]);

        $attendance8 = Attendance::create([
            'user_id' => $user3->id,
            'work_date' => now()->subDays(5)->toDateString(),
            'clock_in' => now()->subDays(5)->setHour(10),
            'clock_out' => now()->subDays(5)->setHour(19),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance8->id,
            'break_start' => now()->subDays(5)->setHour(13),
            'break_end' => now()->subDays(5)->setHour(14),
        ]);

        $attendance9 = Attendance::create([
            'user_id' => $user3->id,
            'work_date' => now()->subDays(6)->toDateString(),
            'clock_in' => now()->subDays(6)->setHour(9),
            'clock_out' => now()->subDays(6)->setHour(18),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance9->id,
            'break_start' => now()->subDays(6)->setHour(12),
            'break_end' => now()->subDays(6)->setHour(13),
        ]);

        $request2 = CorrectionRequest::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance6->id,
            'status' => 'approved',
            'requested_clock_in' =>
                now()->subDays(3)->setHour(8),
            'requested_clock_out' =>
                now()->subDays(3)->setHour(17),
            'note' => '退勤時刻修正',
        ]);

        CorrectionRequestBreak::create([
            'correction_request_id' => $request2->id,
            'break_start' => now()->subDays(3)->setHour(12),
            'break_end' => now()->subDays(3)->setHour(13),
        ]);
    }
}
