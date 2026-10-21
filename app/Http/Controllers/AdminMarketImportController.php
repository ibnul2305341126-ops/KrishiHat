<?php

namespace App\Http\Controllers;

use App\Models\MarketBenchmarkPrice;
use App\Services\DamPriceImportService;
use Illuminate\Http\Request;

class AdminMarketImportController extends Controller
{
    /**
     * Display market benchmark prices and import interface.
     */
    public function index(Request $request)
    {
        $query = MarketBenchmarkPrice::query();

        if ($request->filled('crop_name')) {
            $name = $request->crop_name;
            $query->where(function ($q) use ($name) {
                $q->where('crop_name_bn', 'like', "%{$name}%")
                  ->orWhere('crop_name_en', 'like', "%{$name}%");
            });
        }

        if ($request->filled('market')) {
            $query->where('market_name', 'like', '%' . $request->market . '%');
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
        }

        if ($request->filled('price_type')) {
            $query->where('price_type', $request->price_type);
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', (bool) $request->is_verified);
        }

        $benchmarks = $query->latest('observation_date')->paginate(20)->withQueryString();

        $stats = [
            'total' => MarketBenchmarkPrice::count(),
            'verified' => MarketBenchmarkPrice::where('is_verified', true)->count(),
            'unverified' => MarketBenchmarkPrice::where('is_verified', false)->count(),
            'grower' => MarketBenchmarkPrice::where('price_type', MarketBenchmarkPrice::TYPE_GROWER)->count(),
            'wholesale' => MarketBenchmarkPrice::where('price_type', MarketBenchmarkPrice::TYPE_WHOLESALE)->count(),
            'retail' => MarketBenchmarkPrice::where('price_type', MarketBenchmarkPrice::TYPE_RETAIL)->count(),
            'distinct_crops' => MarketBenchmarkPrice::distinct()->count('crop_name_bn'),
            'distinct_markets' => MarketBenchmarkPrice::distinct()->count('market_name'),
        ];

        return view('admin.market_imports.index', compact('benchmarks', 'stats'));
    }

    /**
     * Handle manual file upload or data import.
     */
    public function store(Request $request, DamPriceImportService $importService)
    {
        $request->validate([
            'import_file' => 'nullable|file|mimes:csv,txt,json|max:5120',
            'json_content' => 'nullable|string',
            'default_source_url' => 'nullable|url|max:255',
        ]);

        $rows = [];

        if ($request->hasFile('import_file')) {
            $file = $request->file('import_file');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'json') {
                $decoded = json_decode(file_get_contents($file->getRealPath()), true);
                if (is_array($decoded)) {
                    $rows = $decoded;
                }
            } else {
                // CSV parsing
                $handle = fopen($file->getRealPath(), 'r');
                $header = fgetcsv($handle);
                if ($header) {
                    while (($line = fgetcsv($handle)) !== false) {
                        if (count($line) === count($header)) {
                            $rows[] = array_combine($header, $line);
                        }
                    }
                }
                fclose($handle);
            }
        } elseif ($request->filled('json_content')) {
            $decoded = json_decode($request->json_content, true);
            if (is_array($decoded)) {
                $rows = $decoded;
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'কোনো বৈধ তথ্য পাওয়া যায়নি। অনুগ্রহ করে সঠিক CSV বা JSON ফাইল প্রদান করুন।');
        }

        $sourceUrl = $request->default_source_url ?: 'https://market.dam.gov.bd/';
        $result = $importService->importRecords($rows, $sourceUrl);

        return back()->with('success', "আমদানি ফলাফল: {$result['imported']}টি রেকর্ড সফলভাবে সংরক্ষিত, {$result['duplicated']}টি ডুপ্লিকেট এড়ানো হয়েছে, {$result['rejected']}টি ত্রুটিপূর্ণ রেকর্ড বাদ দেওয়া হয়েছে এবং {$result['review_needed']}টি পর্যালোচনার জন্য চিহ্নিত।");
    }
}
