<?php

namespace App\Http\Controllers\ReportExchange;

use App\Http\Controllers\Controller;
use App\Models\ReportDocument;
use App\Models\ReportDocumentComment;
use App\Models\ReportDocumentRecipient;
use App\Models\Branch;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReportExchangeController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportDocument::with(['creator', 'fromBranch', 'toBranch', 'academicYear', 'term', 'recipients']);

        $user = Auth::user();

        // Filter by tab
        $tab = $request->get('tab', 'all');
        if ($tab === 'sent') {
            $query->sentBy(Auth::id());
        } elseif ($tab === 'received') {
            $query->receivedBy(Auth::id());
        } elseif ($tab === 'draft') {
            $query->byStatus('draft')->sentBy(Auth::id());
        }

        // Additional filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }
        if ($request->filled('from_branch')) {
            $query->fromBranch($request->from_branch);
        }
        if ($request->filled('to_branch')) {
            $query->toBranch($request->to_branch);
        }

        // Report grouping filters
        if ($request->filled('report_grouping')) {
            $query->byGrouping($request->report_grouping);
        }
        if ($request->filled('report_period')) {
            $query->byPeriod($request->report_period);
        }

        // Branch scope filtering for branch principals
        $branchScope = request()->attributes->get('branch_scope');
        if ($branchScope) {
            $query->where(function ($q) use ($branchScope) {
                $q->where('from_branch_id', $branchScope)
                  ->orWhere('to_branch_id', $branchScope)
                  ->orWhereHas('recipients', function ($rq) {
                      $rq->where('user_id', Auth::id());
                  });
            });
        }

        $documents = $query->latest()->paginate(15);
        $branches = Branch::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();

        // Report grouping options
        $reportGroupings = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'half_year' => 'Half-Year',
            'yearly' => 'Yearly',
        ];

        // Dynamic period options based on selected grouping
        $periodOptions = [];
        if ($request->filled('report_grouping')) {
            $year = date('Y');
            $periodOptions = ReportDocument::getPeriodOptions($year, $request->report_grouping);
        }

        // Get recipient candidates based on user role hierarchy
        $recipientCandidates = $this->getRecipientCandidates($user);

        $stats = [
            'total' => ReportDocument::when($branchScope, function ($q) use ($branchScope) {
                $q->where('from_branch_id', $branchScope)
                  ->orWhere('to_branch_id', $branchScope);
            })->count(),
            'draft' => ReportDocument::byStatus('draft')->sentBy(Auth::id())->count(),
            'submitted' => ReportDocument::byStatus('submitted')->when($branchScope, function ($q) use ($branchScope) {
                $q->where('from_branch_id', $branchScope)->orWhere('to_branch_id', $branchScope);
            })->count(),
            'approved' => ReportDocument::byStatus('approved')->when($branchScope, function ($q) use ($branchScope) {
                $q->where('from_branch_id', $branchScope)->orWhere('to_branch_id', $branchScope);
            })->count(),
            'rejected' => ReportDocument::byStatus('rejected')->when($branchScope, function ($q) use ($branchScope) {
                $q->where('from_branch_id', $branchScope)->orWhere('to_branch_id', $branchScope);
            })->count(),
            'unread' => ReportDocumentRecipient::where('user_id', Auth::id())->where('is_read', false)->count(),
        ];

        return view('admin.report-exchange.index', compact(
            'documents', 'branches', 'academicYears', 'stats', 'tab',
            'reportGroupings', 'periodOptions', 'recipientCandidates'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $branches = Branch::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();

        // Report grouping options
        $reportGroupings = [
            'monthly' => 'Monthly Report',
            'quarterly' => 'Quarterly Report',
            'half_year' => 'Half-Year Report',
            'yearly' => 'Yearly Report',
        ];

        // Dynamic period options (default: monthly)
        $periodOptions = ReportDocument::getPeriodOptions(date('Y'), 'monthly');

        // Get recipient candidates based on user role
        $recipientCandidates = $this->getRecipientCandidates($user);

        // Auto-set from_branch_id for branch principals
        $defaultFromBranch = $user->branch_id;

        return view('admin.report-exchange.create', compact(
            'branches', 'academicYears', 'reportGroupings', 'periodOptions',
            'recipientCandidates', 'defaultFromBranch'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|in:report,memo,proposal,financial,academic,inspection',
            'priority' => 'required|in:low,normal,high,urgent',
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id' => 'nullable|exists:branches,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'report_grouping' => 'required|in:monthly,quarterly,half_year,yearly',
            'report_period' => 'required|string|max:20',
            'file' => 'nullable|file|max:25600',
            'recipients' => 'nullable|array',
            'recipients.*' => 'exists:users,id',
            'action' => 'in:draft,submit',
        ]);

        DB::beginTransaction();
        try {
            $data = $validated;
            $data['created_by'] = Auth::id();
            $data['status'] = ($request->action === 'submit') ? 'submitted' : 'draft';

            // Auto-set from_branch for branch principals
            if ($user->isBranchPrincipal() && empty($data['from_branch_id'])) {
                $data['from_branch_id'] = $user->branch_id;
            }

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store('report-documents', 'public');
                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_size'] = $file->getSize();
            }

            unset($data['file'], $data['action'], $data['recipients']);
            $document = ReportDocument::create($data);

            // Add recipients
            if ($request->filled('recipients') && $request->action === 'submit') {
                foreach ($request->recipients as $userId) {
                    ReportDocumentRecipient::create([
                        'report_document_id' => $document->id,
                        'user_id' => $userId,
                    ]);

                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'New Report Document',
                        'message' => Auth::user()->name . ' sent you a report: ' . $document->title,
                        'type' => 'report_exchange',
                        'link' => route('admin.report-exchange.show', $document->id),
                    ]);
                }
            } else if ($request->action === 'submit' && !$request->filled('recipients')) {
                // Auto-route reports based on hierarchy:
                // Teachers → their branch principal
                // Branch principals → general manager
                $autoRecipients = $this->getAutoRecipients($user);
                foreach ($autoRecipients as $recipientId) {
                    ReportDocumentRecipient::create([
                        'report_document_id' => $document->id,
                        'user_id' => $recipientId,
                    ]);

                    Notification::create([
                        'user_id' => $recipientId,
                        'title' => 'New Report Document',
                        'message' => Auth::user()->name . ' submitted a report: ' . $document->title,
                        'type' => 'report_exchange',
                        'link' => route('admin.report-exchange.show', $document->id),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.report-exchange.index')
                ->with('success', $request->action === 'submit' ? 'Report submitted successfully.' : 'Report saved as draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(ReportDocument $report_exchange)
    {
        $report_exchange->load(['creator', 'fromBranch', 'toBranch', 'academicYear', 'term', 'comments.user', 'recipients.user']);

        // Mark as read for current user
        $recipient = $report_exchange->recipients()->where('user_id', Auth::id())->first();
        if ($recipient && !$recipient->is_read) {
            $recipient->markAsRead();
        }

        return view('admin.report-exchange.show', compact('report_exchange'));
    }

    public function edit(ReportDocument $report_exchange)
    {
        if ($report_exchange->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return back()->with('error', 'You can only edit your own documents.');
        }
        if (!in_array($report_exchange->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected documents can be edited.');
        }

        $user = Auth::user();
        $branches = Branch::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $reportGroupings = [
            'monthly' => 'Monthly Report',
            'quarterly' => 'Quarterly Report',
            'half_year' => 'Half-Year Report',
            'yearly' => 'Yearly Report',
        ];
        $periodOptions = ReportDocument::getPeriodOptions(date('Y'), $report_exchange->report_grouping ?? 'monthly');
        $recipientCandidates = $this->getRecipientCandidates($user);
        $selectedRecipients = $report_exchange->recipients->pluck('user_id')->toArray();

        return view('admin.report-exchange.edit', compact(
            'report_exchange', 'branches', 'academicYears',
            'reportGroupings', 'periodOptions', 'recipientCandidates', 'selectedRecipients'
        ));
    }

    public function update(Request $request, ReportDocument $report_exchange)
    {
        if ($report_exchange->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return back()->with('error', 'You can only edit your own documents.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|in:report,memo,proposal,financial,academic,inspection',
            'priority' => 'required|in:low,normal,high,urgent',
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id' => 'nullable|exists:branches,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'report_grouping' => 'required|in:monthly,quarterly,half_year,yearly',
            'report_period' => 'required|string|max:20',
            'file' => 'nullable|file|max:25600',
            'recipients' => 'nullable|array',
            'recipients.*' => 'exists:users,id',
            'action' => 'in:draft,submit',
        ]);

        DB::beginTransaction();
        try {
            $data = $validated;
            $data['status'] = ($request->action === 'submit') ? 'submitted' : $report_exchange->status;

            if ($request->hasFile('file')) {
                if ($report_exchange->file_path) {
                    Storage::disk('public')->delete($report_exchange->file_path);
                }
                $file = $request->file('file');
                $filePath = $file->store('report-documents', 'public');
                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_size'] = $file->getSize();
            }

            unset($data['file'], $data['action'], $data['recipients']);
            $report_exchange->update($data);

            if ($request->action === 'submit' && $request->filled('recipients')) {
                $report_exchange->recipients()->delete();
                foreach ($request->recipients as $userId) {
                    ReportDocumentRecipient::create([
                        'report_document_id' => $report_exchange->id,
                        'user_id' => $userId,
                    ]);
                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'New Report Document',
                        'message' => Auth::user()->name . ' sent you a report: ' . $report_exchange->title,
                        'type' => 'report_exchange',
                        'link' => route('admin.report-exchange.show', $report_exchange->id),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.report-exchange.index')
                ->with('success', 'Report updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(ReportDocument $report_exchange)
    {
        if ($report_exchange->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return back()->with('error', 'You can only delete your own documents.');
        }
        if ($report_exchange->file_path) {
            Storage::disk('public')->delete($report_exchange->file_path);
        }
        $report_exchange->delete();
        return redirect()->route('admin.report-exchange.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function download(ReportDocument $report_exchange)
    {
        if (!$report_exchange->file_path || !Storage::disk('public')->exists($report_exchange->file_path)) {
            return back()->with('error', 'File not found.');
        }
        return Storage::disk('public')->download($report_exchange->file_path, $report_exchange->file_name);
    }

    public function addComment(Request $request, ReportDocument $report_exchange)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'action' => 'required|in:comment,approve,reject,request_revision',
        ]);

        DB::beginTransaction();
        try {
            ReportDocumentComment::create([
                'report_document_id' => $report_exchange->id,
                'user_id' => Auth::id(),
                'comment' => $validated['comment'],
                'action' => $validated['action'],
            ]);

            $newStatus = $report_exchange->status;
            if ($validated['action'] === 'approve') {
                $newStatus = 'approved';
            } elseif ($validated['action'] === 'reject') {
                $newStatus = 'rejected';
            } elseif ($validated['action'] === 'request_revision') {
                $newStatus = 'draft';
            } elseif ($validated['action'] === 'comment' && $report_exchange->status === 'submitted') {
                $newStatus = 'reviewed';
            }
            $report_exchange->update(['status' => $newStatus]);

            if ($report_exchange->created_by !== Auth::id()) {
                $actionText = [
                    'comment' => 'commented on',
                    'approve' => 'approved',
                    'reject' => 'rejected',
                    'request_revision' => 'requested revision for',
                ];
                Notification::create([
                    'user_id' => $report_exchange->created_by,
                    'title' => 'Report Update',
                    'message' => Auth::user()->name . ' ' . ($actionText[$validated['action']] ?? 'updated') . ' your report: ' . $report_exchange->title,
                    'type' => 'report_exchange',
                    'link' => route('admin.report-exchange.show', $report_exchange->id),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Comment added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getTerms(Request $request)
    {
        $terms = Term::where('academic_year_id', $request->academic_year_id)->get(['id', 'name']);
        return response()->json($terms);
    }

    /**
     * API: Get period options for a given year and grouping.
     */
    public function getPeriodOptions(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $grouping = $request->input('grouping', 'monthly');
        return response()->json(ReportDocument::getPeriodOptions($year, $grouping));
    }

    /**
     * Get recipient candidates based on user role hierarchy:
     * - Teachers → their branch principal(s)
     * - Branch principals → general managers
     * - Admin/GM → all users
     */
    private function getRecipientCandidates(User $user)
    {
        $query = User::where('is_active', true);

        if ($user->role === 'teacher') {
            // Teachers can only send reports to their branch principal(s)
            $teacherModel = \App\Models\Teacher::where('user_id', $user->id)->first();
            if (!$teacherModel) {
                $teacherModel = \App\Models\Teacher::where('email', $user->email)->first();
            }
            $branchId = $teacherModel?->branch_id ?? $user->branch_id;

            if ($branchId) {
                // Get branch principals for this branch
                $query->where('role', 'branch_principal')
                    ->where('branch_id', $branchId);
            } else {
                // Fallback: all branch principals
                $query->where('role', 'branch_principal');
            }
        } elseif ($user->isBranchPrincipal()) {
            // Branch principals send reports to general managers
            $query->where('role', 'general_manager');
        } elseif (in_array($user->role, ['admin', 'super_admin'])) {
            // Admin can send to anyone
            $query->whereIn('role', ['general_manager', 'branch_principal', 'teacher', 'staff']);
        } elseif ($user->isGeneralManager()) {
            // GM can send to admin and branch principals
            $query->whereIn('role', ['admin', 'super_admin', 'branch_principal']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Auto-determine recipients based on hierarchy.
     */
    private function getAutoRecipients(User $user): array
    {
        $recipientIds = [];

        if ($user->role === 'teacher') {
            // Auto-route to branch principal
            $teacherModel = \App\Models\Teacher::where('user_id', $user->id)->first();
            if (!$teacherModel) {
                $teacherModel = \App\Models\Teacher::where('email', $user->email)->first();
            }
            $branchId = $teacherModel?->branch_id ?? $user->branch_id;

            if ($branchId) {
                $principals = User::where('role', 'branch_principal')
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->pluck('id')
                    ->toArray();
                $recipientIds = array_merge($recipientIds, $principals);
            }
        } elseif ($user->isBranchPrincipal()) {
            // Auto-route to general managers
            $gms = User::where('role', 'general_manager')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $recipientIds = array_merge($recipientIds, $gms);
        }

        return array_unique($recipientIds);
    }
}
