<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function report(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['sometimes', 'date'],
            'to_date' => ['sometimes', 'date', 'after_or_equal:from_date'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'method' => ['sometimes', 'nullable', 'string', 'max:50'],
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:10', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 20;
        $query = AssetScan::query()
            ->leftJoin('assets', 'assets.id', '=', 'asset_scans.asset_id')
            ->leftJoin('users', 'users.id', '=', 'asset_scans.scanned_by')
            ->select([
                'asset_scans.id',
                'asset_scans.sku',
                'asset_scans.scanned_at',
                'asset_scans.method',
                'asset_scans.asset_id',
                'assets.name as asset_name',
                'assets.category as asset_category',
                'assets.location as asset_location',
                'users.name as scanned_by_name',
            ])
            ->when(isset($validated['from_date']), function ($query) use ($validated) {
                $query->whereDate('asset_scans.scanned_at', '>=', $validated['from_date']);
            })
            ->when(isset($validated['to_date']), function ($query) use ($validated) {
                $query->whereDate('asset_scans.scanned_at', '<=', $validated['to_date']);
            })
            ->when(array_key_exists('category', $validated), function ($query) use ($validated) {
                if (!$validated['category']) {
                    return;
                }
                $query->where('assets.category', $validated['category']);
            })
            ->when(array_key_exists('method', $validated), function ($query) use ($validated) {
                if (!$validated['method']) {
                    return;
                }
                $query->where('asset_scans.method', $validated['method']);
            })
            ->when(isset($validated['q']) && $validated['q'] !== '', function ($query) use ($validated) {
                $q = $validated['q'];
                $query->where(function ($query) use ($q) {
                    $query->where('asset_scans.sku', 'like', "%{$q}%")
                        ->orWhere('assets.name', 'like', "%{$q}%")
                        ->orWhere('users.name', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('asset_scans.scanned_at');

        $report = $query->paginate($perPage);

        return response()->json($report);
    }

    public function analytics(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['sometimes', 'date'],
            'to_date' => ['sometimes', 'date', 'after_or_equal:from_date'],
        ]);

        $scanBaseQuery = AssetScan::query()
            ->leftJoin('assets', 'assets.id', '=', 'asset_scans.asset_id')
            ->when(isset($validated['from_date']), function ($query) use ($validated) {
                $query->whereDate('asset_scans.scanned_at', '>=', $validated['from_date']);
            })
            ->when(isset($validated['to_date']), function ($query) use ($validated) {
                $query->whereDate('asset_scans.scanned_at', '<=', $validated['to_date']);
            });

        $summary = [
            'total_assets' => Asset::query()->count(),
            'total_scans' => (clone $scanBaseQuery)->count('asset_scans.id'),
            'matched_scans' => (clone $scanBaseQuery)->whereNotNull('asset_scans.asset_id')->count('asset_scans.id'),
            'unknown_scans' => (clone $scanBaseQuery)->whereNull('asset_scans.asset_id')->count('asset_scans.id'),
            'unique_sku_scanned' => (clone $scanBaseQuery)->distinct()->count('asset_scans.sku'),
        ];

        $scansPerCategory = (clone $scanBaseQuery)
            ->selectRaw("COALESCE(NULLIF(assets.category, ''), 'Tanpa Kategori') as category")
            ->selectRaw('COUNT(asset_scans.id) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(assets.category, ''), 'Tanpa Kategori')"))
            ->orderByDesc('total')
            ->get();

        $assetsPerCategory = Asset::query()
            ->selectRaw("COALESCE(NULLIF(category, ''), 'Tanpa Kategori') as category")
            ->selectRaw('COUNT(id) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(category, ''), 'Tanpa Kategori')"))
            ->orderByDesc('total')
            ->get();

        $scansPerDay = (clone $scanBaseQuery)
            ->selectRaw('DATE(asset_scans.scanned_at) as date')
            ->selectRaw('COUNT(asset_scans.id) as total')
            ->groupBy(DB::raw('DATE(asset_scans.scanned_at)'))
            ->orderBy('date')
            ->get();

        $scansPerMethod = (clone $scanBaseQuery)
            ->selectRaw("COALESCE(NULLIF(asset_scans.method, ''), 'unknown') as method")
            ->selectRaw('COUNT(asset_scans.id) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(asset_scans.method, ''), 'unknown')"))
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'summary' => $summary,
            'scans_per_category' => $scansPerCategory,
            'assets_per_category' => $assetsPerCategory,
            'scans_per_day' => $scansPerDay,
            'scans_per_method' => $scansPerMethod,
        ]);
    }
}
