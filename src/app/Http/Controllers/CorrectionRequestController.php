<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestBreak;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceUpdateRequest;

class CorrectionRequestController extends Controller
{
    public function store(
        AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::firstOrCreate(

            [
                'user_id' => Auth::id(),
                'work_date' => $id,
            ],

            [
                'status' => 'off',
            ]
        );

        $correctionRequest = CorrectionRequest::create([

            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_in' =>
                $attendance->work_date->format('Y-m-d') . ' ' . $request->clock_in,
            'requested_clock_out' =>
                $attendance->work_date->format('Y-m-d') . ' ' . $request->clock_out,
            'note' => $request->note,

        ]);

        foreach ($request->break_start as $index => $breakStart) {

            $breakEnd = $request->break_end[$index] ?? null;

            if (!$breakStart && !$breakEnd) {
                continue;
            }

            CorrectionRequestBreak::create([

                'correction_request_id' => $correctionRequest->id,
                'break_start' =>
                    $attendance->work_date->format('Y-m-d') . ' ' . $breakStart,
                'break_end' =>
                    $attendance->work_date->format('Y-m-d') . ' ' . $breakEnd,

            ]);

        }

        return redirect('/attendance/list');

    }

    public function index(Request $request)
    {
        $status = $request->status ?? 'pending';

        $requests = CorrectionRequest::with([
                'user',
                'attendance',
            ])
            ->where('status', $status);

        if (auth()->user()->role === 'admin') {

            $requests = $requests->latest()->get();

            return view(
                'admin.correction_request.list',
                [
                    'requests' => $requests,
                    'status' => $status,
                ]

            );
        }

        $requests = $requests
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

        return view(
            'correction_request.list',
            [
                'requests' => $requests,
                'status' => $status,
            ]
        );
    }
}
