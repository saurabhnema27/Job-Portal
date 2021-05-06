<?php

use Illuminate\Database\Seeder;

class JobTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Job seeder
        for($i=0; $i<10; $i++)
        {
            \DB::table('jobs')->insert([
                'name' => str_random(6),
                'phone' => rand(1,10000000000),
                'role' => "HR",
                "employeer_location" => rand("Indore","Bhopal","Gwalior","jabalpur"),
                "company_name" => "9to6worked",
                "company_location"=>"Bhopal",
                "address1"=>"humko nhii ptaa bhaii",
                "address2"=>"bolaa na nhii pta",
                "state"=>"Madhya Pradesh",
                "city"=>rand("Indore","Bhopal","Gwalior","jabalpur"),
                "zipcode"=>"123456",
                "organization_type"=>"Public sector",
                "job_title"=> rand("laravel","UI/UX","Angular","React"),
                "type_of_role"=>"Full time",
                "job_location"=>rand("Indore","Bhopal","Gwalior","jabalpur"),
                "company_size"=>"[50-500]",
                "contract_type"=>rand("Freelancer","Fulltime","Remote","Contract"),
                "application_receive_type"=>"Email",
                "submit_resume"=>"1",
                "job_description"=>"This is the test requirement",
                "min_salary"=>"10000",
                "max_salary"=>"30000",
                "job_type" => "Free",
                "employeer_id" => "3",
                "find_candidate"=> '{
                    "experience" : [
                        {
                            "minimum" : "1",
                            "experience":"Total work exp",
                            "should":"Required"
                        },
                        {
                            "minimum" : "1",
                            "experience":"HTML/CSS",
                            "should":"Required"
                        }
                    ],
                    "Education":[
                        {
                            "minimum_level_of_education":"Master"
                        }
                    ],
                    "Site_Availability":{
                        "Available_to_Work":["Evening"]
                    }
                }'
            ]);
        }
    }
}
