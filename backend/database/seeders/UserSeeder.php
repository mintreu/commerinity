<?php

namespace Database\Seeders;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Models\User;
use App\Services\UserServices\MembershipSubscriptionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->createDemoUser();

        $this->createDemoApplicant();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'demouser@example.com',
//            'status'    => AuthStatusCast::DRAFT
//        ]);






    }


    private function createDemoUser()
    {
        $demoUser = User::factory()
            ->create([
            'name' => 'Demo User',
            'email' => 'test@example.com',
            //'mobile' => '9800555600',
            'status'    => AuthStatusCast::DRAFT,
            'type'  => AuthTypeCast::REGULAR
        ]);

        // Add Address
        $address = Address::factory()->raw([
           'type'           =>  AddressTypeCast::HOME,
           'postal_code'    =>  '711401'
        ]);
        $demoUser->addresses()->create($address);

        // Add Kyc
//        $demoUser->kyc()->create([
//            'aadhaar' => rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999),
//            'pan' => strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5)) .
//                rand(1000, 9999) .
//                strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 1))
//        ]);



        // User Subscription
//        $memberSubscription = MembershipSubscriptionService::make($demoUser);
//        $memberSubscription->ensureSubscription();
//        $subscription = $memberSubscription->getSubscription();
//
//        if ($subscription)
//        {
//            $demoUser->update([
//                'level_id' => $subscription->level_id
//            ]);
//        }


       User::factory(5)->create(['parent_id' => $demoUser->id])->each(fn($user) => User::factory(5)->create(['parent_id' => $user->id]));

    }


    protected function createDemoApplicant()
    {
        $demoApplicant =User::factory()->create([
            'name' => 'Test User',
            'email' => 'applicant@example.com',
            'status'    => AuthStatusCast::DRAFT,
            'type'  => AuthTypeCast::APPLICANT
        ]);


        // Add Address
        $address = Address::factory()->raw([
            'type'           =>  AddressTypeCast::HOME,
            'postal_code'    =>  '711401'
        ]);
        $demoApplicant->addresses()->create($address);


    }




}
