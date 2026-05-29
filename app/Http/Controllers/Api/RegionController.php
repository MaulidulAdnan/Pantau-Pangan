<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::withCount('markets')->get();
        return response()->json(['regions' => $regions]);
    }

    public function markets($regionId)
    {
        $region = Region::with('markets')->findOrFail($regionId);
        return response()->json([
            'region' => $region->name,
            'markets' => $region->markets,
        ]);
    }
}
