<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PresenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $presences = Presence::with('outWaktu')
            ->where('userId', $userId)
            ->whereIn('type', ['IN', 'OUT'])
            ->orderBy('waktu')
            ->get();

        $data = [];
        $inData = [];

        foreach ($presences as $presence) {
            $tanggal = Carbon::parse($presence->waktu);
            $waktu = Carbon::parse($presence->waktu)->format('H:i:s');
            $status = $presence->is_approve === true ? 'APPROVE' : ($presence->is_approve === false ? 'REJECT' : null);

            if ($presence->type === 'IN') {
                $inData[] = [
                    'id_user' => $userId,
                    'nama_user' => $user->name,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'waktu_masuk' => $waktu,
                    'waktu_pulang' => null,
                    'status_masuk' => $status,
                    'status_pulang' => null,
                ];
            } elseif ($presence->type === 'OUT') {
                $matchingIn = collect($inData)->where('tanggal', $tanggal->format('Y-m-d'))->first();

                if ($matchingIn) {
                    $data[] = [
                        'id_user' => $userId,
                        'nama_user' => $user->name,
                        'tanggal' => $tanggal->format('Y-m-d'),
                        'waktu_masuk' => $matchingIn['waktu_masuk'],
                        'waktu_pulang' => $waktu,
                        'status_masuk' => $matchingIn['status_masuk'],
                        'status_pulang' => $status,
                    ];

                    $inData = collect($inData)->reject(function ($item) use ($tanggal) {
                        return $item['tanggal'] === $tanggal->format('Y-m-d');
                    })->values()->toArray();
                }
            }
        }

        $data = array_merge($data, $inData);

        return response()->json([
            'meta' => [
                'message' => 'Success get data!',
                'status' => 'success',
                'code' => 200,
            ],
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:IN,OUT',
            'waktu' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'message' => 'Something went wrong!',
                    'status' => 'error',
                    'code' => 400,
                ],
                'data' => $validator->errors()
            ], 400);
        }

        $user = Auth::user();
        $userId = $user->id;
        $type = $request->input('type');
        $waktu = $request->input('waktu');

        $data = Presence::create([
            'userId' => $userId,
            'type' => $type,
            'is_approve' => null,
            'waktu' => $waktu,
        ]);

        return response()->json([
            'meta' => [
                'message' => 'Success insert presence!',
                'status' => 'success',
                'code' => 201,
            ],
            'data' => $data
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
