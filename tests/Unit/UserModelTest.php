<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_model_has_correct_db_first_configuration(): void
    {
        $user = new User;

        $this->assertSame('wc_users_tb', $user->getTable());
        $this->assertSame('PER_USER_CODE', $user->getKeyName());
        $this->assertFalse($user->getIncrementing());
        $this->assertSame('string', $user->getKeyType());
        $this->assertFalse($user->timestamps);
        $this->assertContains('PER_USER_USERNAME', $user->getFillable());
        $this->assertNotContains('PER_USER_PASSWORD', $user->getFillable());
    }

    public function test_employee_model_has_correct_db_first_configuration(): void
    {
        $employee = new Employee;

        $this->assertSame('EMPLOYEE_TB', $employee->getTable());
        $this->assertSame('EMP_SERIAL', $employee->getKeyName());
        $this->assertFalse($employee->getIncrementing());
        $this->assertSame('int', $employee->getKeyType());
        $this->assertFalse($employee->timestamps);
    }

    public function test_user_and_employee_relationships_are_defined_correctly(): void
    {
        $user = new User;
        $employee = new Employee;

        $this->assertInstanceOf(BelongsTo::class, $user->employee());
        $this->assertInstanceOf(HasOne::class, $employee->user());
        // The link resolves through linked_employee_code: PER_USER_EMP_CD, only when PER_USER_HOS_NO is this hospital.
        $this->assertSame('linked_employee_code', $user->employee()->getForeignKeyName());
        $this->assertSame('EMP_SERIAL', $user->employee()->getOwnerKeyName());
        $this->assertSame('PER_USER_EMP_CD', $employee->user()->getForeignKeyName());
    }

    public function test_user_role_helper_methods(): void
    {
        $admin = new User(['PER_USER_ROLE' => 'ADMIN']);
        $nurse = new User(['PER_USER_ROLE' => 'NURSE']);
        $pendingUser = new User(['PER_USER_ROLE' => null]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isNurse());

        $this->assertTrue($nurse->isNurse());
        $this->assertFalse($nurse->isAdmin());

        $this->assertFalse($pendingUser->isAdmin());
        $this->assertFalse($pendingUser->isNurse());
    }
}
