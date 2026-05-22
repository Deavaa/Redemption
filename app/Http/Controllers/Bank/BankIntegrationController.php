<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Models\BankIntegration;
use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\Student;
use App\Models\FeePayment;
use App\Models\Fee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BankIntegrationController extends Controller
{
    public function index(Request $request)
    {
        $query = BankTransaction::with(['bankIntegration.branch', 'student', 'feePayment', 'matchedByUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('bank_integration_id')) {
            $query->where('bank_integration_id', $request->bank_integration_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sender_name', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_reference', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(25);

        $bankIntegrations = BankIntegration::with('branch')->get();

        $pendingCount = BankTransaction::where('status', 'pending')->count();
        $matchedCount = BankTransaction::where('status', 'matched')->count();
        $unmatchedCount = BankTransaction::where('status', 'unmatched')->count();
        $totalAmount = BankTransaction::where('status', 'matched')->sum('amount');

        return view('admin.bank-integration.index', compact(
            'transactions', 'bankIntegrations', 'pendingCount',
            'matchedCount', 'unmatchedCount', 'totalAmount'
        ));
    }

    public function settings()
    {
        $bankIntegrations = BankIntegration::with('branch')->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.bank-integration.settings', compact('bankIntegrations', 'branches'));
    }

    public function storeSettings(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'bank_name' => 'required|string|max:255',
            'bank_code' => 'required|string|max:50',
            'account_number' => 'required|string|max:100',
            'account_name' => 'required|string|max:255',
            'integration_type' => 'required|in:csv_upload,api,manual',
            'api_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'merchant_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data = $request->only([
            'branch_id', 'bank_name', 'bank_code', 'account_number',
            'account_name', 'integration_type', 'api_url', 'merchant_id', 'notes'
        ]);

        if ($request->filled('api_key')) {
            $data['api_key'] = Crypt::encryptString($request->api_key);
        }
        if ($request->filled('api_secret')) {
            $data['api_secret'] = Crypt::encryptString($request->api_secret);
        }

        BankIntegration::create($data);

        return back()->with('success', 'Bank integration configured successfully.');
    }

    public function destroySettings(BankIntegration $bankIntegration)
    {
        $bankIntegration->delete();
        return back()->with('success', 'Bank integration deleted.');
    }

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'bank_integration_id' => 'required|exists:bank_integrations,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $bankIntegration = BankIntegration::findOrFail($request->bank_integration_id);
        $file = $request->file('csv_file');

        // Store the CSV file
        $filename = 'bank_statements/' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('', $filename, 'local');

        // Parse CSV
        $filePath = storage_path('app/' . $filename);
        $handle = fopen($filePath, 'r');

        // Skip header row
        $header = fgetcsv($handle);

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Try to detect CSV format based on bank
            $data = $this->parseCsvRow($row, $header, $bankIntegration->bank_code);

            if (!$data || empty($data['transaction_reference'])) {
                $skipped++;
                continue;
            }

            // Check for duplicates
            if (BankTransaction::where('transaction_reference', $data['transaction_reference'])->exists()) {
                $skipped++;
                continue;
            }

            BankTransaction::create([
                'bank_integration_id' => $bankIntegration->id,
                'transaction_reference' => $data['transaction_reference'],
                'bank_transaction_id' => $data['bank_transaction_id'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'ETB',
                'transaction_date' => $data['transaction_date'],
                'sender_name' => $data['sender_name'] ?? null,
                'sender_account' => $data['sender_account'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'pending',
                'source_file' => $filename,
            ]);

            $imported++;
        }

        fclose($handle);

        // Auto-match transactions
        $autoMatched = $this->autoMatchTransactions($bankIntegration);

        return back()->with('success', "Imported {$imported} transaction(s). Skipped {$skipped}. Auto-matched {$autoMatched}.");
    }

    private function parseCsvRow(array $row, array $header, string $bankCode): ?array
    {
        // Normalize header names
        $normalizedHeaders = array_map(function ($h) {
            return strtolower(trim(str_replace([' ', '-', '_'], '', $h)));
        }, $header);

        $data = [];
        foreach ($normalizedHeaders as $index => $colName) {
            $data[$colName] = $row[$index] ?? null;
        }

        // Map common field names
        $result = [];

        // Transaction reference
        foreach (['transactionid', 'reference', 'txnref', 'transactionref', 'ref', 'referenceid'] as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $result['transaction_reference'] = trim($data[$key]);
                break;
            }
        }
        if (empty($result['transaction_reference'])) {
            $result['transaction_reference'] = 'BANK-' . uniqid();
        }

        // Amount
        foreach (['amount', 'credit', 'deposit', 'amountetb', 'creditamount'] as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $result['amount'] = floatval(preg_replace('/[^0-9.-]/', '', $data[$key]));
                break;
            }
        }
        if (empty($result['amount']) || $result['amount'] <= 0) {
            return null;
        }

        // Date
        foreach (['date', 'transactiondate', 'valuedate', 'postingdate', 'datedd/mm/yyyy', 'dateyyyy/mm/dd'] as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                try {
                    $result['transaction_date'] = Carbon::parse($data[$key])->format('Y-m-d');
                } catch (\Exception $e) {
                    $result['transaction_date'] = now()->format('Y-m-d');
                }
                break;
            }
        }
        if (empty($result['transaction_date'])) {
            $result['transaction_date'] = now()->format('Y-m-d');
        }

        // Sender name
        foreach (['sendername', 'fromname', 'sender', 'name', 'accountname', 'depositorname', 'payername'] as $key) {
            if (isset($data[$key])) {
                $result['sender_name'] = trim($data[$key]);
                break;
            }
        }

        // Sender account
        foreach (['senderaccount', 'fromaccount', 'accountnumber', 'senderaccountno'] as $key) {
            if (isset($data[$key])) {
                $result['sender_account'] = trim($data[$key]);
                break;
            }
        }

        // Description
        foreach (['description', 'narration', 'remarks', 'detail', 'particulars'] as $key) {
            if (isset($data[$key])) {
                $result['description'] = trim($data[$key]);
                break;
            }
        }

        // Bank transaction ID
        foreach (['banktransactionid', 'bankref', 'bankreference'] as $key) {
            if (isset($data[$key])) {
                $result['bank_transaction_id'] = trim($data[$key]);
                break;
            }
        }

        $result['currency'] = 'ETB';

        return $result;
    }

    private function autoMatchTransactions(BankIntegration $bankIntegration): int
    {
        $matched = 0;
        $pendingTransactions = BankTransaction::where('bank_integration_id', $bankIntegration->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingTransactions as $transaction) {
            // Try to match by admission number in description
            $student = $this->findStudentByReference($transaction);

            if ($student) {
                $transaction->update([
                    'status' => 'matched',
                    'student_id' => $student->id,
                    'matched_by' => Auth::id(),
                    'matched_at' => now(),
                    'match_notes' => 'Auto-matched by system',
                ]);
                $matched++;
            }
        }

        return $matched;
    }

    private function findStudentByReference(BankTransaction $transaction): ?Student
    {
        $description = strtoupper($transaction->description ?? '');
        $senderName = strtoupper($transaction->sender_name ?? '');

        // Try matching by admission number format in description
        $patterns = [
            '/ADM[_\-]?(\d+)/i',
            '/ADMISSION[_\-]?(\d+)/i',
            '/STU[_\-]?(\d+)/i',
            '/ROLL[_\-]?(\d+)/i',
            '/ID[_\-]?(\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                $student = Student::where('admission_number', $matches[1])
                    ->orWhere('roll_number', $matches[1])
                    ->first();
                if ($student) return $student;
            }
        }

        // Try matching by student name
        if (!empty($transaction->sender_name)) {
            $nameParts = explode(' ', $transaction->sender_name);
            if (count($nameParts) >= 2) {
                $student = Student::where('full_name', 'LIKE', '%' . $transaction->sender_name . '%')->first();
                if ($student) return $student;
            }
        }

        return null;
    }

    public function manualMatch(Request $request, BankTransaction $bankTransaction)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'match_notes' => 'nullable|string|max:2000',
        ]);

        $student = Student::findOrFail($request->student_id);

        // Find pending fee for this student
        $pendingFee = Fee::where('class_id', $student->class_id)
            ->where('is_active', true)
            ->first();

        $feePayment = null;
        if ($pendingFee) {
            $feePayment = FeePayment::create([
                'fee_id' => $pendingFee->id,
                'student_id' => $student->id,
                'amount_paid' => $bankTransaction->amount,
                'payment_date' => $bankTransaction->transaction_date,
                'payment_method' => 'bank_transfer',
                'transaction_id' => $bankTransaction->transaction_reference,
                'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                'status' => 'completed',
            ]);
        }

        $bankTransaction->update([
            'status' => 'matched',
            'student_id' => $student->id,
            'fee_payment_id' => $feePayment?->id,
            'matched_amount' => $bankTransaction->amount,
            'matched_by' => Auth::id(),
            'matched_at' => now(),
            'match_notes' => $request->match_notes,
        ]);

        return back()->with('success', 'Transaction matched to student ' . $student->full_name . ' successfully.');
    }

    public function rejectTransaction(BankTransaction $bankTransaction)
    {
        $bankTransaction->update([
            'status' => 'rejected',
            'matched_by' => Auth::id(),
            'matched_at' => now(),
            'match_notes' => 'Rejected by user',
        ]);

        return back()->with('success', 'Transaction rejected.');
    }

    public function markUnmatched(BankTransaction $bankTransaction)
    {
        $bankTransaction->update([
            'status' => 'unmatched',
        ]);

        return back()->with('success', 'Transaction marked as unmatched.');
    }

    public function searchStudents(Request $request)
    {
        $search = $request->get('q', '');
        $students = Student::where('full_name', 'LIKE', "%{$search}%")
            ->orWhere('admission_number', 'LIKE', "%{$search}%")
            ->orWhere('roll_number', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'full_name', 'admission_number', 'roll_number', 'class_id']);

        return response()->json($students);
    }
}
