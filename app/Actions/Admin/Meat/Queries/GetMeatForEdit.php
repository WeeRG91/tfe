<?php

namespace App\Actions\Admin\Meat\Queries;

use App\Http\Resources\Admin\Meat\MeatEditResource;
use App\Models\Meat;

class GetMeatForEdit
{
    public function execute(Meat $meat): MeatEditResource
    {
        $meat->load([
            'translations',
            'images',
        ]);

        return new MeatEditResource($meat);
    }
}
