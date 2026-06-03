<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockItem;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Branch;

class StockController extends Controller
{
    /* ==================== STOCK ITEMS ==================== */

    public function index(Request $r)
    {
        $query = StockItem::with('branch');

        if ($r->filled('search')) {
            $s = $r->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%$s%")
                  ->orWhere('code', 'LIKE', "%$s%")
                  ->orWhere('description', 'LIKE', "%$s%");
            });
        }
        if ($r->filled('category')) $query->where('category', $r->category);
        if ($r->filled('branch_id')) $query->where('branch_id', $r->branch_id);
        if ($r->filled('stock_status')) {
            if ($r->stock_status === 'low') $query->whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0);
            elseif ($r->stock_status === 'out') $query->where('quantity', '<=', 0);
            elseif ($r->stock_status === 'available') $query->whereColumn('quantity', '>', 'minimum_stock');
        }
        if ($r->has('inactive')) {
            // show inactive too
        } else {
            $query->where('is_active', true);
        }

        $items = $query->orderBy('name')->paginate(20);
        $categories = StockItem::categoryOptions();
        $branches = Branch::orderBy('name')->get();
        $totalItems = StockItem::where('is_active', true)->count();
        $totalValue = StockItem::where('is_active', true)->sum('total_value');
        $lowStockCount = StockItem::where('is_active', true)->whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count();
        $outOfStockCount = StockItem::where('is_active', true)->where('quantity', '<=', 0)->count();

        return view('admin.stock.index', compact(
            'items', 'categories', 'branches', 'totalItems', 'totalValue', 'lowStockCount', 'outOfStockCount'
        ));
    }

    public function create()
    {
        $categories = StockItem::categoryOptions();
        $units = StockItem::unitOptions();
        $branches = Branch::orderBy('name')->get();
        return view('admin.stock.create', compact('categories', 'units', 'branches'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:stock_items,code',
            'category'      => 'required|in:' . implode(',', array_keys(StockItem::categoryOptions())),
            'description'   => 'nullable|string|max:500',
            'unit'          => 'required|in:' . implode(',', array_keys(StockItem::unitOptions())),
            'quantity'      => 'required|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'unit_price'    => 'nullable|numeric|min:0',
            'location'      => 'nullable|string|max:255',
            'branch_id'     => 'nullable|exists:branches,id',
        ]);

        $data = $r->only(['name', 'code', 'category', 'description', 'unit', 'quantity', 'minimum_stock', 'unit_price', 'location', 'branch_id']);
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['unit_price'] = $data['unit_price'] ?? 0;
        $data['total_value'] = $data['quantity'] * $data['unit_price'];
        $data['is_active'] = true;

        $item = StockItem::create($data);

        // If initial quantity > 0, create a stock-in transaction
        if ($item->quantity > 0) {
            StockTransaction::create([
                'stock_item_id' => $item->id,
                'type' => 'in',
                'reason' => 'purchase',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->quantity * $item->unit_price,
                'transaction_date' => now(),
                'reference_no' => 'INIT-' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'notes' => 'Initial stock on item creation',
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.stock.index')->with('success', __('Stock item created successfully'));
    }

    public function show(StockItem $stock)
    {
        $stock->load(['transactions.createdBy', 'transactions.recipient', 'branch']);
        $categories = StockItem::categoryOptions();
        return view('admin.stock.show', compact('stock', 'categories'));
    }

    public function edit(StockItem $stock)
    {
        $categories = StockItem::categoryOptions();
        $units = StockItem::unitOptions();
        $branches = Branch::orderBy('name')->get();
        return view('admin.stock.edit', compact('stock', 'categories', 'units', 'branches'));
    }

    public function update(Request $r, StockItem $stock)
    {
        $r->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50|unique:stock_items,code,' . $stock->id,
            'category'      => 'required|in:' . implode(',', array_keys(StockItem::categoryOptions())),
            'description'   => 'nullable|string|max:500',
            'unit'          => 'required|in:' . implode(',', array_keys(StockItem::unitOptions())),
            'minimum_stock' => 'nullable|integer|min:0',
            'unit_price'    => 'nullable|numeric|min:0',
            'location'      => 'nullable|string|max:255',
            'branch_id'     => 'nullable|exists:branches,id',
            'is_active'     => 'nullable|boolean',
        ]);

        $data = $r->only(['name', 'code', 'category', 'description', 'unit', 'minimum_stock', 'unit_price', 'location', 'branch_id']);
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['unit_price'] = $data['unit_price'] ?? 0;
        $data['is_active'] = $r->has('is_active');
        $data['total_value'] = $stock->quantity * $data['unit_price'];

        $stock->update($data);

        return redirect()->route('admin.stock.index')->with('success', __('Stock item updated successfully'));
    }

    public function destroy(StockItem $stock)
    {
        $stock->delete();
        return back()->with('success', __('Stock item deleted successfully'));
    }

    /* ==================== STOCK IN ==================== */

    public function stockIn()
    {
        $items = StockItem::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.stock.stock-in', compact('items', 'branches'));
    }

    public function storeStockIn(Request $r)
    {
        $r->validate([
            'stock_item_id'   => 'required|exists:stock_items,id',
            'quantity'        => 'required|integer|min:1',
            'unit_price'      => 'nullable|numeric|min:0',
            'transaction_date'=> 'required|date',
            'reference_no'    => 'nullable|string|max:100',
            'notes'           => 'nullable|string|max:500',
        ]);

        $item = StockItem::findOrFail($r->stock_item_id);
        $unitPrice = $r->unit_price ?? $item->unit_price;

        // Update stock quantity
        $item->quantity += $r->quantity;
        $item->unit_price = $unitPrice;
        $item->total_value = $item->quantity * $unitPrice;
        $item->save();

        // Create transaction
        StockTransaction::create([
            'stock_item_id' => $item->id,
            'type' => 'in',
            'reason' => 'purchase',
            'quantity' => $r->quantity,
            'unit_price' => $unitPrice,
            'total_price' => $r->quantity * $unitPrice,
            'transaction_date' => $r->transaction_date,
            'reference_no' => $r->reference_no,
            'notes' => $r->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.stock.index')->with('success', __('Stock added successfully. :qty :unit of :item', [
            'qty' => $r->quantity, 'unit' => $item->unit, 'item' => $item->name,
        ]));
    }

    /* ==================== STOCK OUT / ISSUE TO EMPLOYEE ==================== */

    public function stockOut()
    {
        $items = StockItem::where('is_active', true)->where('quantity', '>', 0)->orderBy('name')->get();
        $employees = User::whereIn('role', ['admin', 'teacher', 'staff'])->orderBy('name')->get();
        $classrooms = ClassRoom::with('sections')->orderBy('numeric_name')->orderBy('name')->get();
        $reasons = StockTransaction::reasonOptions();
        // Filter to only 'out' reasons
        $outReasons = array_intersect_key($reasons, array_flip(['issue_employee', 'issue_class', 'damaged', 'lost', 'adjustment', 'transfer']));
        return view('admin.stock.stock-out', compact('items', 'employees', 'classrooms', 'outReasons'));
    }

    public function storeStockOut(Request $r)
    {
        $r->validate([
            'stock_item_id'   => 'required|exists:stock_items,id',
            'reason'          => 'required|in:issue_employee,issue_class,damaged,lost,adjustment,transfer',
            'quantity'        => 'required|integer|min:1',
            'transaction_date'=> 'required|date',
            'recipient_id'    => 'nullable|integer',
            'notes'           => 'nullable|string|max:500',
        ]);

        $item = StockItem::findOrFail($r->stock_item_id);

        if ($r->quantity > $item->quantity) {
            return back()->withInput()->withErrors(['quantity' => __('Insufficient stock. Available: :avail', ['avail' => $item->quantity])]);
        }

        // Determine recipient type
        $recipientType = null;
        $recipientId = $r->recipient_id;
        if ($r->reason === 'issue_employee' && $recipientId) {
            $recipientType = User::class;
        } elseif ($r->reason === 'issue_class' && $recipientId) {
            $recipientType = ClassRoom::class;
        }

        // Deduct stock
        $item->quantity -= $r->quantity;
        $item->total_value = $item->quantity * $item->unit_price;
        $item->save();

        // Create transaction
        StockTransaction::create([
            'stock_item_id' => $item->id,
            'type' => 'out',
            'reason' => $r->reason,
            'quantity' => $r->quantity,
            'unit_price' => $item->unit_price,
            'total_price' => $r->quantity * $item->unit_price,
            'transaction_date' => $r->transaction_date,
            'recipient_id' => $recipientId,
            'recipient_type' => $recipientType,
            'notes' => $r->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.stock.index')->with('success', __('Stock issued successfully. :qty :unit of :item', [
            'qty' => $r->quantity, 'unit' => $item->unit, 'item' => $item->name,
        ]));
    }

    /* ==================== TRANSACTIONS LOG ==================== */

    public function transactions(Request $r)
    {
        $query = StockTransaction::with(['stockItem', 'createdBy', 'recipient']);

        if ($r->filled('stock_item_id')) $query->where('stock_item_id', $r->stock_item_id);
        if ($r->filled('type')) $query->where('type', $r->type);
        if ($r->filled('reason')) $query->where('reason', $r->reason);
        if ($r->filled('date_from')) $query->where('transaction_date', '>=', $r->date_from);
        if ($r->filled('date_to')) $query->where('transaction_date', '<=', $r->date_to);

        $transactions = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(25);
        $items = StockItem::where('is_active', true)->orderBy('name')->get();
        $reasons = StockTransaction::reasonOptions();
        $types = StockTransaction::typeOptions();

        return view('admin.stock.transactions', compact('transactions', 'items', 'reasons', 'types'));
    }

    /* ==================== REPORTS ==================== */

    public function report(Request $r)
    {
        $query = StockItem::with('branch');

        if ($r->filled('category')) $query->where('category', $r->category);
        if ($r->filled('branch_id')) $query->where('branch_id', $r->branch_id);

        $reportType = $r->get('report_type', 'summary');

        if ($reportType === 'low_stock') {
            $query->whereColumn('quantity', '<=', 'minimum_stock');
        } elseif ($reportType === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }

        $items = $query->orderBy('category')->orderBy('name')->get();
        $categories = StockItem::categoryOptions();
        $branches = Branch::orderBy('name')->get();

        // Summary stats
        $totalItems = $items->count();
        $totalQuantity = $items->sum('quantity');
        $totalValue = $items->sum('total_value');
        $lowStockItems = $items->filter(fn($i) => $i->isLowStock() && !$i->isOutOfStock());
        $outOfStockItems = $items->filter(fn($i) => $i->isOutOfStock());

        // Category breakdown
        $categoryBreakdown = $items->groupBy('category')->map(function ($group) {
            return [
                'count' => $group->count(),
                'quantity' => $group->sum('quantity'),
                'value' => $group->sum('total_value'),
            ];
        });

        // Employee issue summary (if requested)
        $employeeIssues = null;
        if ($reportType === 'employee_issues') {
            $issueQuery = StockTransaction::with(['stockItem', 'recipient'])
                ->where('type', 'out')
                ->where('reason', 'issue_employee');
            if ($r->filled('date_from')) $issueQuery->where('transaction_date', '>=', $r->date_from);
            if ($r->filled('date_to')) $issueQuery->where('transaction_date', '<=', $r->date_to);
            $employeeIssues = $issueQuery->orderBy('transaction_date', 'desc')->get();
        }

        return view('admin.stock.report', compact(
            'items', 'categories', 'branches', 'totalItems', 'totalQuantity', 'totalValue',
            'lowStockItems', 'outOfStockItems', 'categoryBreakdown', 'employeeIssues', 'reportType'
        ));
    }
}
