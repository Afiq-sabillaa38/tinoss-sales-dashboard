<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period;
        $product = $request->product;
        $productGroup = $request->product_group;

        // Filter dropdown data
        $periods = DB::table('invoices')
            ->select('period')
            ->distinct()
            ->orderBy('period')
            ->pluck('period');

        $products = DB::table('products')
            ->select('product_code', 'product')
            ->orderBy('product')
            ->get();

        $productGroups = DB::table('product_groups')
            ->select('product_group')
            ->distinct()
            ->orderBy('product_group')
            ->pluck('product_group');

        // Main sales table query
        $query = DB::table('invoices as i')
            ->leftJoin('products as p', 'i.product_code', '=', 'p.product_code')
            ->select(
                'i.year',
                'i.period',
                'i.product_code',
                'p.product',
                'i.product_group',
                DB::raw('SUM(i.qty) as total_qty'),
                DB::raw('SUM(i.foc) as total_foc'),
                DB::raw('SUM(i.sls) as total_sales')
            );

        if ($period) {
            $query->where('i.period', $period);
        }

        if ($product) {
            $query->where('i.product_code', $product);
        }

        if ($productGroup) {
            $query->where('i.product_group', $productGroup);
        }

        $salesData = $query
            ->groupBy('i.year','i.period','i.product_code','p.product','i.product_group')
            ->orderByDesc('total_sales')
            ->limit(100)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Chart Section
        |--------------------------------------------------------------------------
        */

        // Top 10 product groups
        $topGroups = DB::table('invoices')
            ->select('product_group', DB::raw('SUM(sls) as total_sales'))
            ->groupBy('product_group')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->pluck('product_group');

        // Chart data
        $chartQuery = DB::table('invoices')
            ->select(
                'year',
                'period',
                'product_group',
                DB::raw('SUM(sls) as total_sales')
            )
            ->whereIn('product_group', $topGroups);

        if ($period) {
            $chartQuery->where('period', $period);
        }

        if ($product) {
            $chartQuery->where('product_code', $product);
        }

        if ($productGroup) {
            $chartQuery->where('product_group', $productGroup);
        }

        $chartRows = $chartQuery
            ->groupBy('year','period','product_group')
            ->orderBy('year')
            ->orderBy('period')
            ->get();

        // Chart labels (product groups)
        $chartLabels = $chartRows->pluck('product_group')->unique()->values();

        // Unique period-year combinations
        $seriesKeys = $chartRows->map(function ($row) {
            return $row->period . ' - ' . $row->year;
        })->unique()->values();

        // Chart colors
        $colors = [
            '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b',
            '#6f42c1','#fd7e14','#20c997','#17a2b8','#858796',
            '#6610f2','#198754','#ff6384','#ff9f40','#00a8ff'
        ];

        $chartDatasets = [];

        foreach ($seriesKeys as $index => $seriesKey) {

            $datasetData = [];

            foreach ($chartLabels as $label) {

                $matchingRow = $chartRows->first(function ($row) use ($seriesKey,$label) {
                    return ($row->period . ' - ' . $row->year) === $seriesKey
                        && $row->product_group === $label;
                });

                $datasetData[] = $matchingRow
                    ? (float)$matchingRow->total_sales
                    : 0;
            }

            $chartDatasets[] = [
                'label' => $seriesKey,
                'data' => $datasetData,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderWidth' => 1
            ];
        }

        return view('dashboard', compact(
            'periods',
            'products',
            'productGroups',
            'salesData',
            'chartLabels',
            'chartDatasets',
            'period',
            'product',
            'productGroup'
        ));
    }
}