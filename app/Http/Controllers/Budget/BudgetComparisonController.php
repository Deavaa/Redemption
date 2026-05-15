<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Branch;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class BudgetComparisonController extends Controller
{
    /**
     * Display branch-to-branch budget comparison.
     * Calculates total allocated and spent per branch with comparison percentages.
     * Supports filtering by academic_year_id.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $selectedAcademicYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        // Build the base query for budgets
        $budgetQuery = Budget::with(['branch', 'academicYear']);

        if ($selectedAcademicYear) {
            $budgetQuery->where('academic_year_id', $selectedAcademicYear->id);
        }

        if ($request->filled('branch_id')) {
            $budgetQuery->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $budgetQuery->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $budgetQuery->where('category', 'LIKE', '%' . $request->category . '%');
        }

        $budgets = $budgetQuery->get();

        // Get all active branches for comparison
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Calculate budget data per branch
        $branchComparison = [];
        $totalAllocatedAll = 0;
        $totalSpentAll = 0;

        foreach ($branches as $branch) {
            $branchBudgets = $budgets->where('branch_id', $branch->id);

            $totalAllocated = $branchBudgets->sum('allocated_amount');
            $totalSpent = $branchBudgets->sum('spent_amount');
            $budgetCount = $branchBudgets->count();
            $utilizationRate = $totalAllocated > 0 ? round(($totalSpent / $totalAllocated) * 100, 2) : 0;
            $remaining = $totalAllocated - $totalSpent;

            // Category-wise breakdown
            $categoryBreakdown = $branchBudgets->groupBy('category')->map(function ($items, $category) {
                $allocated = $items->sum('allocated_amount');
                $spent = $items->sum('spent_amount');
                return [
                    'category' => $category,
                    'allocated' => round($allocated, 2),
                    'spent' => round($spent, 2),
                    'remaining' => round($allocated - $spent, 2),
                    'utilization' => $allocated > 0 ? round(($spent / $allocated) * 100, 2) : 0,
                    'count' => $items->count(),
                ];
            })->values()->toArray();

            // Status-wise breakdown
            $statusBreakdown = $branchBudgets->groupBy('status')->map(function ($items, $status) {
                return [
                    'status' => $status,
                    'count' => $items->count(),
                    'allocated' => round($items->sum('allocated_amount'), 2),
                    'spent' => round($items->sum('spent_amount'), 2),
                ];
            })->values()->toArray();

            $branchComparison[] = [
                'branch' => $branch,
                'total_allocated' => round($totalAllocated, 2),
                'total_spent' => round($totalSpent, 2),
                'remaining' => round($remaining, 2),
                'budget_count' => $budgetCount,
                'utilization_rate' => $utilizationRate,
                'category_breakdown' => $categoryBreakdown,
                'status_breakdown' => $statusBreakdown,
            ];

            $totalAllocatedAll += $totalAllocated;
            $totalSpentAll += $totalSpent;
        }

        // Calculate comparison percentages (each branch's share of the total)
        foreach ($branchComparison as &$comparison) {
            $comparison['allocation_percentage'] = $totalAllocatedAll > 0
                ? round(($comparison['total_allocated'] / $totalAllocatedAll) * 100, 2) : 0;
            $comparison['spending_percentage'] = $totalSpentAll > 0
                ? round(($comparison['total_spent'] / $totalSpentAll) * 100, 2) : 0;
        }
        unset($comparison);

        // Overall summary
        $overallSummary = [
            'total_branches' => count($branchComparison),
            'total_allocated' => round($totalAllocatedAll, 2),
            'total_spent' => round($totalSpentAll, 2),
            'total_remaining' => round($totalAllocatedAll - $totalSpentAll, 2),
            'overall_utilization' => $totalAllocatedAll > 0
                ? round(($totalSpentAll / $totalAllocatedAll) * 100, 2) : 0,
        ];

        // Highest and lowest spenders
        $highestSpender = collect($branchComparison)->sortByDesc('total_spent')->first();
        $lowestSpender = collect($branchComparison)->sortBy('total_spent')->first();
        $highestUtilization = collect($branchComparison)->sortByDesc('utilization_rate')->first();
        $lowestUtilization = collect($branchComparison)->sortBy('utilization_rate')->first();

        return view('admin.budget-comparison.index', compact(
            'branchComparison',
            'overallSummary',
            'academicYears',
            'selectedAcademicYear',
            'branches',
            'highestSpender',
            'lowestSpender',
            'highestUtilization',
            'lowestUtilization'
        ));
    }
}
