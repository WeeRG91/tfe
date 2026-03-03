<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteMeat
{
    /**
     * @param Meat $meat
     * @return void
     * @throws Throwable
     */
    public function execute(Meat $meat): void
    {
        DB::transaction(function () use ($meat) {
            $meat->delete();
        });
    }
}
