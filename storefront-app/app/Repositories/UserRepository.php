<?php
namespace App\Repositories;

use app\Exceptions\DuplicateEmailException;
use app\Exceptions\UserInUseException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UserRepository {
    protected const string USER_TABLE = 'customer';
    protected const string USER_TYPE = 'CUSTOMER';

    public function insert_user(string $email, string $password_hash,
        string $first_name, string $last_name):int{
        try {
            return DB::transaction(function () use ($email, $password_hash, $first_name, $last_name) {
                $user_id = DB::table('app_user')
                    ->insertGetId(['user_type' => static::USER_TYPE], 'user_id');
                DB::table(static::USER_TABLE)
                    ->insert([
                        'user_id' => $user_id,
                        'email'=> $email,
                        'password_hash'=> $password_hash,
                        'user_type' => static::USER_TYPE,
                        'first_name'=> $first_name,
                        'last_name'=> $last_name
                    ]);
                return $user_id;
            });
        } catch (QueryException $e) {
            if ($this->is_unique_violation($e)) {
                throw new DuplicateEmailException('Email already in use.', 0, $e);
            }
            throw $e;
        }
    }

    public function find_user_by_email(string $email):?object{
        return DB::table(static::USER_TABLE)
            ->where('email', $email)->first();
    }

    public function get_user_id_by_email(string $email):?int{
        $user = DB::table(static::USER_TABLE)
            ->where('email', $email)->first(['user_id']);
        return $user? (int) $user->user_id: null;
    }

    public function get_name_by_id(int $user_id):string{
        $name = DB::table(static::USER_TABLE)
            ->where('user_id', $user_id)->first(['first_name', 'last_name']);
        return $name? $name->first_name . ' ' . $name->last_name: 'name not found';
    }

    public function update_user_email(int $user_id, string $email):bool{
        try {
            return DB::table(static::USER_TABLE)
                ->where('user_id', $user_id)
                ->update(['email'=> $email]) > 0;
        } catch (QueryException $e) {
            if ($this->is_unique_violation($e)) {
                throw new DuplicateEmailException('Email already in use.', 0, $e);
            }
            throw $e;
        }
    }

    public function update_user_name(int $user_id, string $first_name, string $last_name):bool{
        return DB::table(static::USER_TABLE)
            ->where('user_id', $user_id)
            ->update(['first_name'=> $first_name,'last_name'=> $last_name]) > 0;
    }

    public function update_user_password(int $user_id, string $password_hash):bool{
        return DB::table(static::USER_TABLE)
            ->where('user_id', $user_id)
            ->update(['password_hash'=> $password_hash]) > 0;
    }

    public function delete_user(int $user_id):bool{
        try {
            return DB::transaction(function () use ($user_id) {
                $deleted = DB::table(static::USER_TABLE)
                    ->where('user_id', $user_id)
                    ->delete() > 0;
                DB::table('app_user')
                    ->where('user_id', $user_id)
                    ->delete();
                return $deleted;
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23503') {
                throw new UserInUseException('User is referenced by other records.', 0, $e);
            }
            throw $e;
        }
    }

    private function is_unique_violation(QueryException $e):bool{
        return ($e->errorInfo[0] ?? null) === '23505';
    }
}