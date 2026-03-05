<?php

namespace App\Actions\Admin\Meat\Queries;

use App\Http\Resources\Admin\Meat\MeatEditResource;
use App\Models\Meat;
use Illuminate\Support\Facades\Storage;

class GetMeatForEdit
{
    /**
     * @param Meat $meat
     * @return MeatEditResource
     */
    public function execute(Meat $meat): MeatEditResource
    {
        $meat->load('images');

        return new MeatEditResource($meat);
    }
}
