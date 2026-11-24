<?php
namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShippingInfoRepository {
    protected const string TABLE = 'shipping_info';

    public function insert_address(int $user_id, ?string $address_name,
        string $street_name, string $street_num, ?string $apt_number,
        string $city, string $state, string $postal_code):int{
        return DB::table(static::TABLE)->insertGetId([
            'user_id' => $user_id,
            'address_name' => $address_name,
            'street_name' => $street_name,
            'street_num' => $street_num,
            'apt_number' => $apt_number,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postal_code
        ], 'address_id');
    }

    public function get_address_by_id(int $address_id, int $user_id):?object{
        return DB::table(static::TABLE)
            ->where('address_id', $address_id)
            ->where('user_id', $user_id)
            ->first();
    }

    public function get_addresses_by_user_id(int $user_id):Collection{
        return DB::table(static::TABLE)
            ->where('user_id', $user_id)
            ->orderBy('address_id')
            ->get();
    }

    public function update_address(int $address_id, int $user_id, ?string $address_name,
        string $street_name, string $street_num, ?string $apt_number,
        string $city, string $state, string $postal_code):bool{
        return DB::table(static::TABLE)
            ->where('address_id', $address_id)
            ->where('user_id', $user_id)
            ->update([
                'address_name' => $address_name,
                'street_name' => $street_name,
                'street_num' => $street_num,
                'apt_number' => $apt_number,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postal_code
            ]) > 0;
    }

    public function delete_address(int $address_id, int $user_id):bool{
        return DB::table(static::TABLE)
            ->where('address_id', $address_id)
            ->where('user_id', $user_id)
            ->delete() > 0;
    }
}