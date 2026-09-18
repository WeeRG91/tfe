<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;

class RestoreMeat
{
    public function execute(int $id): void
    {
        $meat = Meat::onlyTrashed()->findOrFail($id);

        $meat->restore();
    }
}
