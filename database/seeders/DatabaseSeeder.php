<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Account;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Symfony\Component\HttpKernel\Controller\TraceableArgumentResolver;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Client::factory(3)
            ->has(
                Account::factory()
                    ->count(4)
                    /*->has(
                        Transaction::factory(5)
                            ->create(),
                    )*/
                    ,
            )
            ->create();
    }
}
