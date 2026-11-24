<?php
namespace App\Repositories;
use Illuminate\Support\Facades\DB;

class AdminRepository extends UserRepository{
    protected const string USER_TABLE = "app_admin";
    protected const string USER_TYPE = "ADMIN";

    public function get_employee_id_by_id(int $user_id):?int{
        $admin = DB::table(static::USER_TABLE)
            ->where('user_id', $user_id)->first(['employee_id']);
        return $admin? (int) $admin->employee_id: null;
    }
}