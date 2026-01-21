<?php

namespace App;

trait Registrations
{
    public function registerWithoutPassword($data)
    {
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => fake()->password(10),
        ]);

        $user->sendEmailVerificationNotification();

        return $user;
    }


}
