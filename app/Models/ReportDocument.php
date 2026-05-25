<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'document_type', 'priority', 'status',
        'file_path', 'file_name', 'file_size',
        'created_by', 'from_branch_id', 'to_branch_id',
        'academic_year_id', 'term_id',
        'report_period', 'report_grouping',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function comments()
    {
        return $this->hasMany(ReportDocumentComment::class);
    }

    public function recipients()
    {
        return $this->hasMany(ReportDocumentRecipient::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeFromBranch($query, $branchId)
    {
        return $query->where('from_branch_id', $branchId);
    }

    public function scopeToBranch($query, $branchId)
    {
        return $query->where('to_branch_id', $branchId);
    }

    public function scopeSentBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeReceivedBy($query, $userId)
    {
        return $query->whereHas('recipients', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope: filter by report grouping (monthly, quarterly, half_year, yearly).
     */
    public function scopeByGrouping($query, $grouping)
    {
        return $query->where('report_grouping', $grouping);
    }

    /**
     * Scope: filter by report period (e.g., '2026-01', '2026-Q1', '2026-H1', '2026').
     */
    public function scopeByPeriod($query, $period)
    {
        return $query->where('report_period', $period);
    }

    // Helpers
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'submitted' => '<span class="badge bg-info">Submitted</span>',
            'reviewed' => '<span class="badge bg-warning text-dark">Reviewed</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            'archived' => '<span class="badge bg-dark">Archived</span>',
        ];
        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => '<span class="badge bg-light text-dark">Low</span>',
            'normal' => '<span class="badge bg-primary">Normal</span>',
            'high' => '<span class="badge bg-warning text-dark">High</span>',
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
        ];
        return $badges[$this->priority] ?? '<span class="badge bg-secondary">' . ucfirst($this->priority) . '</span>';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'report' => 'General Report',
            'memo' => 'Memo',
            'proposal' => 'Proposal',
            'financial' => 'Financial Report',
            'academic' => 'Academic Report',
            'inspection' => 'Inspection Report',
        ];
        return $labels[$this->document_type] ?? ucfirst($this->document_type);
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return '-';
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get the human-readable report grouping label.
     */
    public function getGroupingLabelAttribute(): string
    {
        $labels = [
            'monthly' => 'Monthly Report',
            'quarterly' => 'Quarterly Report',
            'half_year' => 'Half-Year Report',
            'yearly' => 'Yearly Report',
        ];
        return $labels[$this->report_grouping] ?? 'Monthly Report';
    }

    /**
     * Get the display period string.
     */
    public function getDisplayPeriodAttribute(): string
    {
        if (!$this->report_period) return '-';

        switch ($this->report_grouping) {
            case 'monthly':
                // Format: '2026-01' → 'January 2026'
                $parts = explode('-', $this->report_period);
                if (count($parts) === 2) {
                    $months = [1 => 'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];
                    $monthNum = (int) $parts[1];
                    return ($months[$monthNum] ?? $parts[1]) . ' ' . $parts[0];
                }
                break;
            case 'quarterly':
                // Format: '2026-Q1' → 'Q1 2026'
                return str_replace('-', ' ', $this->report_period);
            case 'half_year':
                // Format: '2026-H1' → 'H1 2026'
                return str_replace('-', ' ', $this->report_period);
            case 'yearly':
                // Format: '2026' → 'Year 2026'
                return 'Year ' . $this->report_period;
        }

        return $this->report_period;
    }

    /**
     * Generate period options for a given year and grouping type.
     */
    public static function getPeriodOptions(string $year = null, string $grouping = 'monthly'): array
    {
        $year = $year ?? date('Y');
        $options = [];

        switch ($grouping) {
            case 'monthly':
                $months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
                foreach ($months as $i => $month) {
                    $val = $year . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                    $options[$val] = $month . ' ' . $year;
                }
                break;
            case 'quarterly':
                for ($q = 1; $q <= 4; $q++) {
                    $val = $year . '-Q' . $q;
                    $options[$val] = 'Quarter ' . $q . ' (' . $year . ')';
                }
                break;
            case 'half_year':
                $options[$year . '-H1'] = 'First Half (' . $year . ')';
                $options[$year . '-H2'] = 'Second Half (' . $year . ')';
                break;
            case 'yearly':
                $options[$year] = 'Year ' . $year;
                break;
        }

        return $options;
    }
}
