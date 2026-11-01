<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserAccessRequest;
use App\Models\Employee;
use App\Models\User;
use App\Support\Hospital;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display listing of WCCMS users and employee linkages.
     */
    public function index(Request $request)
    {
        $users = User::with('employee.job')->get();
        $unlinkedEmployees = Employee::query()->active()->whereDoesntHave('user')->orderedByName()->with('job')->get();

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

        $attributes = [
            'PER_USER_ROLE' => $request->input('PER_USER_ROLE'),
            'PER_USER_IS_ACTIVE' => $request->boolean('PER_USER_IS_ACTIVE'),
        ];

        if ($request->has('PER_EMP_CODE') && $request->input('PER_EMP_CODE')) {
            $employeeCode = (int) $request->input('PER_EMP_CODE');

            // An employee belongs to one account (UNIQUE on code + hospital): take it from any other account first
            User::where('PER_USER_EMP_CD', $employeeCode)
                ->where('PER_USER_HOS_NO', Hospital::number())
                ->where('PER_USER_CODE', '<>', $user->PER_USER_CODE)
                ->update(['PER_USER_EMP_CD' => null]);

            $attributes['PER_USER_EMP_CD'] = $employeeCode;
            $attributes['PER_USER_HOS_NO'] = Hospital::number();
        }

        $user->update($attributes);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'تم تحديث بيانات وصول المستخدم بنجاح',
                'user' => $user->fresh('employee'),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات وصول المستخدم بنجاح');
    }
}
