<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserAccessRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display listing of WCCMS users and employee linkages.
     */
    public function index(Request $request)
    {
        $users = User::with('employee')->get();
        $unlinkedEmployees = Employee::whereNull('PER_EMP_USER_CD')
            ->orWhere('PER_EMP_USER_CD', '')
            ->get();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'users' => $users,
                'unlinked_employees' => $unlinkedEmployees,
            ]);
        }

        return view('admin.users.index', compact('users', 'unlinkedEmployees'));
    }

    /**
     * Update user role, active access flag, and employee linkage.
     */
    public function update(UpdateUserAccessRequest $request, string $userCode)
    {
        $user = User::where('PER_USER_CODE', $userCode)->firstOrFail();

        $user->update([
            'PER_USER_ROLE' => $request->input('PER_USER_ROLE'),
            'PER_USER_IS_ACTIVE' => $request->boolean('PER_USER_IS_ACTIVE'),
        ]);

        if ($request->has('PER_EMP_CODE') && $request->input('PER_EMP_CODE')) {
            // Remove previous link for this user
            Employee::where('PER_EMP_USER_CD', $user->PER_USER_CODE)->update(['PER_EMP_USER_CD' => null]);

            // Assign new link
            Employee::where('PER_EMP_CODE', $request->input('PER_EMP_CODE'))->update(['PER_EMP_USER_CD' => $user->PER_USER_CODE]);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'تم تحديث بيانات وصول المستخدم بنجاح',
                'user' => $user->fresh('employee'),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات وصول المستخدم بنجاح');
    }
}
