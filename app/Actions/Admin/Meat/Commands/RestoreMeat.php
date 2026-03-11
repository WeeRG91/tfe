<?php

namespace App\Actions\Admin\Meat\Commands;

use App\Models\Meat;

class RestoreMeat
{
    /**
     * @param int $id
     * @return void
     */
    public function execute(int $id): void
    {
        $meat = Meat::onlyTrashed()->findOrFail($id);

        $meat->restore();
    }
}
