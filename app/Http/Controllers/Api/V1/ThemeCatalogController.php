<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RestaurantThemeService;
use App\Services\ThemeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;

class ThemeCatalogController extends Controller
{
    /**
     * @throws JsonException
     */
    public function __invoke(
        ThemeRegistry $themes,
        RestaurantThemeService $restaurantThemes,
    ): JsonResponse
    {
        return response()->json([
            'data' => [
                'default_theme_key' => $restaurantThemes->defaultClientThemeKey(),
                'themes' => $themes->allPublishedThemes(),
            ],
        ]);
    }
}
