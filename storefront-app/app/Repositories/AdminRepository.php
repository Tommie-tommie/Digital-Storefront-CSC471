<?php
namespace App\Repositories;
use App\Exceptions\DuplicateEmailException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;

class AdminRepository extends UserRepository{
    protected const string USER_TABLE = "app_admin";
    protected const string USER_TYPE = "ADMIN";

    public function get_employee_id_by_id(int $user_id):?int{
        $admin = DB::table(static::USER_TABLE)
            ->where('user_id', $user_id)->first(['employee_id']);
        return $admin? (int) $admin->employee_id: null;
    }

    # same as insert_user but admins also need an employee_id
    public function insert_admin(string $email, string $password_hash,
        string $first_name, string $last_name, int $employee_id):int{
        try {
            return DB::transaction(function () use ($email, $password_hash, $first_name, $last_name, $employee_id) {
                $user_id = DB::table('app_user')
                    ->insertGetId(['user_type' => static::USER_TYPE], 'user_id');
                DB::table(static::USER_TABLE)
                    ->insert([
                        'user_id' => $user_id,
                        'email'=> $email,
                        'password_hash'=> $password_hash,
                        'user_type' => static::USER_TYPE,
                        'first_name'=> $first_name,
                        'last_name'=> $last_name,
                        'employee_id' => $employee_id
                    ]);
                return $user_id;
            });
        } catch (QueryException $e) {
            if ($this->is_unique_violation($e)) {
                throw new DuplicateEmailException('Email or employee id already in use.', 0, $e);
            }
            throw $e;
        }
    }

    # admins need an employee_id, use insert_admin instead
    public function insert_user(string $email, string $password_hash,
        string $first_name, string $last_name):int{
        throw new LogicException('Use insert_admin() for admins.');
    }
}
