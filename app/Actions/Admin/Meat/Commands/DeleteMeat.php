<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;

class DeleteMeat
{
    public function execute(int $id): void
    {
        $meat = Meat::query()->findOrFail($id);

        $meat->delete();
    }
}
