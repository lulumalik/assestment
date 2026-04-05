<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255'],
            'method' => ['sometimes', 'nullable', 'string', 'max:50'],
            'image' => ['sometimes', 'nullable', 'string'],
        ]);

        $asset = Asset::query()->where('sku', $validated['sku'])->first();

        $imagePath = null;
        $image = $validated['image'] ?? null;
        if (is_string($image) && str_starts_with($image, 'data:image/')) {
            $parts = explode(',', $image, 2);
            if (count($parts) === 2) {
                $binary = base64_decode($parts[1], true);
                if ($binary !== false) {
                    $imagePath = 'scans/'.now()->format('Y/m').'/'.uniqid('scan_', true).'.jpg';
                    Storage::disk('public')->put($imagePath, $binary);
                }
            }
        }

        $scan = AssetScan::query()->create([
            'asset_id' => $asset?->id,
            'sku' => $validated['sku'],
            'scanned_by' => $request->user()->id,
            'scanned_at' => now(),
            'method' => $validated['method'] ?? null,
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'scan' => [
                ...$scan->toArray(),
                'image_url' => $this->imageUrl($scan),
            ],
            'asset' => $asset,
        ], 201);
    }

    public function image(Request $request, AssetScan $scan)
    {
        if (!$scan->image_path || !Storage::disk('public')->exists($scan->image_path)) {
            abort(404);
        }

        $binary = Storage::disk('public')->get($scan->image_path);
        $mime = Storage::disk('public')->mimeType($scan->image_path) ?: 'image/jpeg';

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function imageUrl(AssetScan $scan): ?string
    {
        if (!$scan->image_path) {
            return null;
        }

        return route('scans.image', ['scan' => $scan->id]);
    }
}

