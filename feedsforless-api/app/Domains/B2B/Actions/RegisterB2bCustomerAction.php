<?php

namespace App\Domains\B2B\Actions;

use App\Domains\B2B\DTOs\RegisterB2bCustomerDTO;
use App\Domains\B2B\Models\Company;
use App\Domains\B2B\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterB2bCustomerAction
{
    public function execute(RegisterB2bCustomerDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $company = Company::create([
                'name' => $dto->companyName,
                'tax_classification' => $dto->taxClassification,
                'tax_registration_number' => $dto->taxRegistrationNumber,
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'phone' => $dto->phone,
                'job_title' => $dto->jobTitle,
            ]);

            $user->assignRole('Customer');

            return $user;
        });
    }
}