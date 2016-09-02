<?php

use App\Models\Flight;
use Illuminate\Database\Seeder;

class SeedFlights extends Seeder {

    public function run()
    {
        $Flight = new Flight;
        $Flight->name = "SaSa";
        $Flight->airline = "MAS";
        $Flight->save();
    }
}
