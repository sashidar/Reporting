<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

/**
 * Class UserTableSeeder
 */
class UserTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create(
            [
                'first_name'   => 'Yipl',
                'last_name'    => 'Admin',
                'username'     => 'yipl_admin',
                'email'        => 'admin@aidstream.com.np',
                'password'     => bcrypt(env('SUPERADMIN_PASSWORD')),
                'role_id'      => 3,
                'time_zone_id' => 1,
                'time_zone'    => 'GMT'
            ]
        );
    }
}
