<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDocumentComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_document_id', 'user_id', 'comment', 'action',
    ];

    public function reportDocument()
    {
        return $this->belongsTo(ReportDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionBadgeAttribute()
    {
        $badges = [
            'comment' => '<span class="badge bg-light text-dark"><i class="fas fa-comment me-1"></i>Comment</span>',
            'approve' => '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>',
            'reject' => '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Rejected</span>',
            'request_revision' => '<span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i>Revision Requested</span>',
        ];
        return $badges[$this->action] ?? '<span class="badge bg-secondary">' . ucfirst($this->action) . '</span>';
    }
}
