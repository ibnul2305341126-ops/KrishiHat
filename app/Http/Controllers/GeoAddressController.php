<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use App\Models\PostOffice;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\UrbanArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoAddressController extends Controller
{
    /**
     * Get all divisions.
     */
    public function divisions(): JsonResponse
    {
        $divisions = Division::orderBy('name_en')
            ->get(['id', 'name_en', 'name_bn', 'bbs_code']);

        return response()->json($divisions);
    }

    /**
     * Get districts optionally filtered by division_id.
     */
    public function districts(Request $request): JsonResponse
    {
        $query = District::query()->orderBy('name_en');

        if ($request->filled('division_id')) {
            $divisionId = (int) $request->division_id;
            $query->where('division_id', $divisionId);
        }

        $districts = $query->get(['id', 'division_id', 'name_en', 'name_bn', 'bbs_code']);

        return response()->json($districts);
    }

    /**
     * Get upazilas / thanas filtered by district_id.
     */
    public function upazilas(Request $request): JsonResponse
    {
        $query = Upazila::query()->orderBy('name_en');

        if ($request->filled('district_id')) {
            $query->where('district_id', (int) $request->district_id);
        }

        $upazilas = $query->get(['id', 'district_id', 'name_en', 'name_bn', 'is_thana']);

        return response()->json($upazilas);
    }

    /**
     * Get unions filtered by upazila_id.
     */
    public function unions(Request $request): JsonResponse
    {
        $query = Union::query()->orderBy('name_en');

        if ($request->filled('upazila_id')) {
            $query->where('upazila_id', (int) $request->upazila_id);
        }

        $unions = $query->get(['id', 'upazila_id', 'name_en', 'name_bn']);

        return response()->json($unions);
    }

    /**
     * Get urban areas (Municipalities, City Corporations) filtered by district_id or upazila_id.
     */
    public function urbanAreas(Request $request): JsonResponse
    {
        $query = UrbanArea::query()->orderBy('name_en');

        if ($request->filled('district_id')) {
            $query->where('district_id', (int) $request->district_id);
        }

        if ($request->filled('upazila_id')) {
            $upazilaId = (int) $request->upazila_id;
            $query->where(function ($q) use ($upazilaId) {
                $q->where('upazila_id', $upazilaId)
                  ->orWhereNull('upazila_id');
            });
        }

        $urbanAreas = $query->get(['id', 'district_id', 'upazila_id', 'name_en', 'name_bn', 'type']);

        return response()->json($urbanAreas);
    }

    /**
     * Get post offices filtered by district_id or upazila_id.
     */
    public function postOffices(Request $request): JsonResponse
    {
        $query = PostOffice::query()->orderBy('name_en');

        if ($request->filled('district_id')) {
            $query->where('district_id', (int) $request->district_id);
        }

        if ($request->filled('upazila_id')) {
            $upazilaId = (int) $request->upazila_id;
            $query->where(function ($q) use ($upazilaId) {
                $q->where('upazila_id', $upazilaId)
                  ->orWhereNull('upazila_id');
            });
        }

        $postOffices = $query->get(['id', 'district_id', 'upazila_id', 'name_en', 'name_bn', 'post_code']);

        return response()->json($postOffices);
    }
}
