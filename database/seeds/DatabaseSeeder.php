<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        for($i=0; $i<10; $i++)
        {
            \DB::table('users')->insert([
                'first_name' => str_random(6),
                'last_name' => str_random(6),
                'email' => strtolower(str_random(6)).'@gmail.com',
                'mobile' => rand(1,10000000000),
                'image' => "default.jpg",
                'user_type' => rand(1,3),
                'user_status' => "1",
                'block_unblock' => "1",
                'password' => bcrypt('Funbase1#'),
                'email_notification' => "1"
            ]);
        }
        
    }
}
