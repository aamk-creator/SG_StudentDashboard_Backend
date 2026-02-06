<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Branch;
use Faker\Factory as Faker;

class SeedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:students {count=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed random students into the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $faker = Faker::create();
        $count = (int) $this->argument('count');

        $userIds = User::pluck('id')->toArray();
        $courseIds = Course::pluck('id')->toArray();
        $branchIds = Branch::pluck('id')->toArray();

        if (empty($userIds) || empty($courseIds) || empty($branchIds)) {
            $this->warn("Please ensure Users, Courses, and Branches exist before running this command.");
            return;
        }

        for ($i = 1; $i <= $count; $i++) {
            Student::create([
                'code' => 'SG' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'status' => $faker->randomElement(['active', 'inactive']),
                'user_id' => $faker->randomElement($userIds),
                'course_id' => $faker->randomElement($courseIds),
                'branch_id' => $faker->randomElement($branchIds),
            ]);
        }

        $this->info("$count Students seeded successfully.");
    }
}
