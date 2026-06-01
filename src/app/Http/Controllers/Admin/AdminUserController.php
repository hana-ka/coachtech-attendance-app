<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('admin.user.list', compact('users'));
    }

    public function attendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : now();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear(
                'work_date',
                $currentMonth->year
            )
            ->whereMonth(
                'work_date',
                $currentMonth->month
            )
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {

                return $attendance->work_date
                    ->format('Y-m-d');

            });

        $startDate = $currentMonth
            ->copy()
            ->startOfMonth();

        $endDate = $currentMonth
            ->copy()
            ->endOfMonth();

        $days = [];

        for (
            $date = $startDate->copy();
            $date <= $endDate;
            $date->addDay()
        ) {

            $days[] = $date->copy();

        }

        return view(
            'admin.attendance.user_list',
            compact(
                'user',
                'attendances',
                'currentMonth',
                'days'
            )
        );
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->month
            ? Carbon::parse($request->month)
            : now();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear(
                'work_date',
                $currentMonth->year
            )
            ->whereMonth(
                'work_date',
                $currentMonth->month
            )
            ->orderBy('work_date')
            ->get();

        $response = new StreamedResponse(function () use ($attendances) {

            $handle = fopen('php://output', 'w');


            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($attendances as $attendance) {

                $breakMinutes = $attendance->breakTimes
                    ->sum(function ($break) {

                        return Carbon::parse($break->break_start)
                            ->diffInMinutes($break->break_end);
                    });

                $workMinutes =
                    Carbon::parse($attendance->clock_in)
                        ->diffInMinutes($attendance->clock_out)
                    - $breakMinutes;

                fputcsv($handle, [

                    $attendance->work_date->format('Y/m/d'),

                    optional($attendance->clock_in)
                        ->format('H:i'),

                    optional($attendance->clock_out)
                        ->format('H:i'),

                    sprintf(
                        '%02d:%02d',
                        floor($breakMinutes / 60),
                        $breakMinutes % 60
                    ),

                    sprintf(
                        '%02d:%02d',
                        floor($workMinutes / 60),
                        $workMinutes % 60
                    ),
                ]);
            }

            fclose($handle);
        });

        $fileName =
            $user->name
            . '_'
            . $currentMonth->format('Y_m')
            . '_attendance.csv';

        $response->headers->set(
            'Content-Type',
            'text/csv'
        );

        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.$fileName.'"'
        );

        return $response;
    }
}
