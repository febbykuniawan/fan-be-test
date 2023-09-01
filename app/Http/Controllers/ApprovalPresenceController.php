<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApprovalPresenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $supervisorNPP = $user->npp;

        $subordinateUsers = User::where('npp_supervisor', $supervisorNPP)->pluck('id');

        $presenceData = Presence::whereIn('userId', $subordinateUsers)->get();

        return response()->json([
            'meta' => [
                'message' => 'Success get presence data!',
                'status' => 'success',
                'code' => 200,
            ],
            'data' => $presenceData
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        $validator = Validator::make($request->all(), [
            'is_approve' => 'boolean', // Memastikan nilai is_approved adalah boolean (true/false)
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

        $presence = Presence::find($id);

        if (!$presence) {
            return response()->json([
                'meta' => [
                    'message' => 'Something went wrong!',
                    'status' => 'error',
                    'code' => 400,
                ],
                'data' => 'Presence not found'
            ], 400);
        }

        $userToApprove = User::find($presence->userId);

        if ($user->npp === $userToApprove->npp_supervisor) {
            $presence->is_approve = $request->input('is_approve');
            $presence->save();
            return response()->json([
                'meta' => [
                    'message' => 'Approval status updated successfully!',
                    'status' => 'success',
                    'code' => 200,
                ],
                'data' => $presence
            ]);
        } else {
            return response()->json([
                'meta' => [
                    'message' => 'You are not authorized to approve presence for this user!',
                    'status' => 'error',
                    'code' => 403,
                ],
                'data' => 'Presence not found'
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
