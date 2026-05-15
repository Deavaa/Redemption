<?php

namespace App\Http\Controllers\FinanceStatement;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Models\FinanceStatement;
use App\Models\Branch;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class FinancialComparisonController extends Controller
{
    /**
     * Compare financial data across branches.
     * Calculates total income, total expense, net balance per branch.
     * Supports filtering by academic_year_id and date range.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $selectedAcademicYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        // Date range filters
        $dateFrom = $request->filled('date_from') ? $request->date_from : null;
        $dateTo = $request->filled('date_to') ? $request->date_to : null;

        // Build Income/Expense query
        $incomeExpenseQuery = IncomeExpense::with(['branch', 'academicYear']);

        if ($selectedAcademicYear) {
            $incomeExpenseQuery->where('academic_year_id', $selectedAcademicYear->id);
        }

        if ($dateFrom) {
            $incomeExpenseQuery->where('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $incomeExpenseQuery->where('date', '<=', $dateTo);
        }

        if ($request->filled('branch_id')) {
            $incomeExpenseQuery->where('branch_id', $request->branch_id);
        }

        $incomeExpenses = $incomeExpenseQuery->get();

        // Build Finance Statement query
        $statementQuery = FinanceStatement::with(['branch', 'academicYear']);

        if ($selectedAcademicYear) {
            $statementQuery->where('academic_year_id', $selectedAcademicYear->id);
        }

        if ($dateFrom) {
            $statementQuery->where('period_from', '>=', $dateFrom);
        }

        if ($dateTo) {
            $statementQuery->where('period_to', '<=', $dateTo);
        }

        if ($request->filled('branch_id')) {
            $statementQuery->where('branch_id', $request->branch_id);
        }

        $financeStatements = $statementQuery->get();

        // Calculate financial data per branch
        $branchComparison = [];
        $totalIncomeAll = 0;
        $totalExpenseAll = 0;

        foreach ($branches as $branch) {
            // Income/Expense data for this branch
            $branchRecords = $incomeExpenses->where('branch_id', $branch->id);
            $incomeRecords = $branchRecords->where('type', 'income');
            $expenseRecords = $branchRecords->where('type', 'expense');

            $totalIncome = $incomeRecords->sum('amount');
            $totalExpense = $expenseRecords->sum('amount');
            $netBalance = $totalIncome - $totalExpense;

            // Income by category
            $incomeByCategory = $incomeRecords->groupBy('category')->map(function ($items, $category) {
                return [
                    'category' => $category,
                    'total' => round($items->sum('amount'), 2),
                    'count' => $items->count(),
                ];
            })->values()->toArray();

            // Expense by category
            $expenseByCategory = $expenseRecords->groupBy('category')->map(function ($items, $category) {
                return [
                    'category' => $category,
                    'total' => round($items->sum('amount'), 2),
                    'count' => $items->count(),
                ];
            })->values()->toArray();

            // Finance Statement data for this branch
            $branchStatements = $financeStatements->where('branch_id', $branch->id);
            $statementTotalIncome = $branchStatements->sum('total_income');
            $statementTotalExpense = $branchStatements->sum('total_expense');
            $statementNetBalance = $branchStatements->sum('net_balance');

            // Monthly trend (group by month)
            $monthlyTrend = $branchRecords->groupBy(function ($item) {
                return $item->date ? $item->date->format('Y-m') : 'unknown';
            })->map(function ($items, $month) {
                $income = $items->where('type', 'income')->sum('amount');
                $expense = $items->where('type', 'expense')->sum('amount');
                return [
                    'month' => $month,
                    'income' => round($income, 2),
                    'expense' => round($expense, 2),
                    'net' => round($income - $expense, 2),
                ];
            })->sortBy('month')->values()->toArray();

            $branchComparison[] = [
                'branch' => $branch,
                'total_income' => round($totalIncome, 2),
                'total_expense' => round($totalExpense, 2),
                'net_balance' => round($netBalance, 2),
                'income_count' => $incomeRecords->count(),
                'expense_count' => $expenseRecords->count(),
                'income_by_category' => $incomeByCategory,
                'expense_by_category' => $expenseByCategory,
                'statement_total_income' => round($statementTotalIncome, 2),
                'statement_total_expense' => round($statementTotalExpense, 2),
                'statement_net_balance' => round($statementNetBalance, 2),
                'statement_count' => $branchStatements->count(),
                'monthly_trend' => $monthlyTrend,
                'income_expense_ratio' => $totalExpense > 0
                    ? round($totalIncome / $totalExpense, 2) : null,
            ];

            $totalIncomeAll += $totalIncome;
            $totalExpenseAll += $totalExpense;
        }

        // Calculate comparison percentages
        foreach ($branchComparison as &$comparison) {
            $comparison['income_percentage'] = $totalIncomeAll > 0
                ? round(($comparison['total_income'] / $totalIncomeAll) * 100, 2) : 0;
            $comparison['expense_percentage'] = $totalExpenseAll > 0
                ? round(($comparison['total_expense'] / $totalExpenseAll) * 100, 2) : 0;
        }
        unset($comparison);

        // Overall summary
        $overallSummary = [
            'total_branches' => count($branchComparison),
            'total_income' => round($totalIncomeAll, 2),
            'total_expense' => round($totalExpenseAll, 2),
            'total_net_balance' => round($totalIncomeAll - $totalExpenseAll, 2),
            'avg_income_per_branch' => count($branchComparison) > 0
                ? round($totalIncomeAll / count($branchComparison), 2) : 0,
            'avg_expense_per_branch' => count($branchComparison) > 0
                ? round($totalExpenseAll / count($branchComparison), 2) : 0,
        ];

        // Rankings
        $highestIncome = collect($branchComparison)->sortByDesc('total_income')->first();
        $lowestIncome = collect($branchComparison)->sortBy('total_income')->first();
        $highestExpense = collect($branchComparison)->sortByDesc('total_expense')->first();
        $lowestExpense = collect($branchComparison)->sortBy('total_expense')->first();
        $bestBalance = collect($branchComparison)->sortByDesc('net_balance')->first();
        $worstBalance = collect($branchComparison)->sortBy('net_balance')->first();

        return view('admin.financial-comparison.index', compact(
            'branchComparison',
            'overallSummary',
            'academicYears',
            'selectedAcademicYear',
            'branches',
            'dateFrom',
            'dateTo',
            'highestIncome',
            'lowestIncome',
            'highestExpense',
            'lowestExpense',
            'bestBalance',
            'worstBalance'
        ));
    }
}
