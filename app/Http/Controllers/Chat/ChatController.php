<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get the list of users this authenticated user can message, based on role.
     */
    private function getAvailableUsers(): object
    {
        $user = Auth::user();
        $userId = $user->id;
        $role = $user->role;

        // Admin, super_admin, general_manager can message everyone
        if (in_array($role, ['admin', 'super_admin', 'general_manager'])) {
            return User::where('id', '!=', $userId)
                ->where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }

        // Branch principal can message teachers in their branch, parents of students in their branch, and managers/admin
        if ($role === 'branch_principal') {
            return User::where('id', '!=', $userId)
                ->where('is_active', 1)
                ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'teacher', 'parent', 'branch_principal'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }

        // Teacher can message: parents of students they teach, branch principal, manager, admin
        if ($role === 'teacher') {
            $teacher = Teacher::where('user_id', $userId)->first();
            if (!$teacher) {
                $teacher = Teacher::where('email', $user->email)->first();
            }

            $allowedUserIds = collect();

            // Always can message admins, branch principals, general managers
            $adminIds = User::where('is_active', 1)
                ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'branch_principal'])
                ->pluck('id');
            $allowedUserIds = $allowedUserIds->merge($adminIds);

            if ($teacher) {
                // Parents of students in classes the teacher teaches or is homeroom for
                $assignmentClassIds = $teacher->assignments()->pluck('class_id')->unique();
                $homeroomClassIds = $teacher->classRooms()->pluck('id');
                $classIds = $assignmentClassIds->merge($homeroomClassIds)->unique();

                $studentIds = Student::whereIn('class_id', $classIds)->where('status', 'active')->pluck('id');
                $parentUserIds = ParentModel::whereHas('students', function ($q) use ($studentIds) {
                    $q->whereIn('students.id', $studentIds);
                })->pluck('user_id')->filter();

                $allowedUserIds = $allowedUserIds->merge($parentUserIds);

                // Other teachers in same classes
                $colleagueIds = TeacherAssignment::whereIn('class_id', $classIds)
                    ->pluck('teacher_id')
                    ->unique()
                    ->filter();
                $colleagueUserIds = Teacher::whereIn('id', $colleagueIds)->pluck('user_id')->filter();
                $allowedUserIds = $allowedUserIds->merge($colleagueUserIds);
            }

            return User::where('id', '!=', $userId)
                ->where('is_active', 1)
                ->whereIn('id', $allowedUserIds->unique())
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }

        // Parent can message: homeroom teacher, branch principal, manager, admin
        if ($role === 'parent') {
            $parentModel = ParentModel::where('user_id', $userId)->first();
            $allowedUserIds = collect();

            // Always can message admins, branch principals, general managers
            $adminIds = User::where('is_active', 1)
                ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'branch_principal'])
                ->pluck('id');
            $allowedUserIds = $allowedUserIds->merge($adminIds);

            if ($parentModel) {
                // Get children's classes and find homeroom teachers + subject teachers
                $studentIds = $parentModel->students()->where('status', 'active')->pluck('students.id');
                $classIds = Student::whereIn('id', $studentIds)->pluck('class_id')->unique();

                // Homeroom teachers
                $homeroomTeacherIds = \App\Models\Section::whereIn('class_id', $classIds)
                    ->whereNotNull('teacher_id')
                    ->pluck('teacher_id')
                    ->unique();
                $homeroomUserIds = Teacher::whereIn('id', $homeroomTeacherIds)->pluck('user_id')->filter();
                $allowedUserIds = $allowedUserIds->merge($homeroomUserIds);

                // Subject teachers for children's classes
                $subjectTeacherIds = TeacherAssignment::whereIn('class_id', $classIds)
                    ->pluck('teacher_id')
                    ->unique();
                $subjectUserIds = Teacher::whereIn('id', $subjectTeacherIds)->pluck('user_id')->filter();
                $allowedUserIds = $allowedUserIds->merge($subjectUserIds);
            }

            return User::where('id', '!=', $userId)
                ->where('is_active', 1)
                ->whereIn('id', $allowedUserIds->unique())
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }

        // Student can message: homeroom teacher, branch principal, admin
        if ($role === 'student') {
            $student = Student::where('user_id', $userId)->first();
            $allowedUserIds = collect();

            // Always can message admins, branch principals, general managers
            $adminIds = User::where('is_active', 1)
                ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'branch_principal'])
                ->pluck('id');
            $allowedUserIds = $allowedUserIds->merge($adminIds);

            if ($student) {
                // Homeroom teacher
                $homeroomTeacherIds = \App\Models\Section::where('class_id', $student->class_id)
                    ->whereNotNull('teacher_id')
                    ->pluck('teacher_id')
                    ->unique();
                $homeroomUserIds = Teacher::whereIn('id', $homeroomTeacherIds)->pluck('user_id')->filter();
                $allowedUserIds = $allowedUserIds->merge($homeroomUserIds);

                // Subject teachers for their class
                $subjectTeacherIds = TeacherAssignment::where('class_id', $student->class_id)
                    ->pluck('teacher_id')
                    ->unique();
                $subjectUserIds = Teacher::whereIn('id', $subjectTeacherIds)->pluck('user_id')->filter();
                $allowedUserIds = $allowedUserIds->merge($subjectUserIds);
            }

            return User::where('id', '!=', $userId)
                ->where('is_active', 1)
                ->whereIn('id', $allowedUserIds->unique())
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        }

        // Default: other roles can message admins and managers
        return User::where('id', '!=', $userId)
            ->where('is_active', 1)
            ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'branch_principal'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    /**
     * Determine which view to use based on the user's role.
     */
    private function resolveView(string $view): string
    {
        $role = Auth::user()->role;
        if (in_array($role, ['student'])) {
            return str_replace('admin.chat', 'portal.student.chat', $view);
        }
        if (in_array($role, ['parent'])) {
            return str_replace('admin.chat', 'parent.chat', $view);
        }
        return $view;
    }

    /**
     * Determine the route prefix based on the user's role.
     */
    private function resolveRoutePrefix(): string
    {
        $role = Auth::user()->role;
        if ($role === 'student') return 'student.chat';
        if ($role === 'parent') return 'parent.chat';
        return 'admin.chat';
    }

    public function index()
    {
        $userId = Auth::id();

        $conversations = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['participants.user', 'lastMessage.sender'])
          ->orderByDesc('last_message_at')
          ->paginate(20);

        $users = $this->getAvailableUsers();

        $unreadCount = ChatMessage::whereHas('conversation.participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('sender_id', '!=', $userId)->where('is_read', false)->count();

        $routePrefix = $this->resolveRoutePrefix();

        return view($this->resolveView('admin.chat.index'), compact('conversations', 'users', 'unreadCount', 'routePrefix'));
    }

    public function show($id)
    {
        $userId = Auth::id();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['participants.user', 'messages.sender', 'messages.reads'])
          ->findOrFail($id);

        // Mark messages as read
        ChatMessage::where('conversation_id', $id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Update participant's last_read_at
        ChatParticipant::where('conversation_id', $id)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        $routePrefix = $this->resolveRoutePrefix();

        return view($this->resolveView('admin.chat.show'), compact('conversation', 'routePrefix'));
    }

    public function storeConversation(Request $r)
    {
        $r->validate([
            'type' => 'required|in:private,group',
            'title' => 'nullable|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);

        $userId = Auth::id();
        $routePrefix = $this->resolveRoutePrefix();

        // Verify the user is allowed to message the selected participants
        $availableUserIds = $this->getAvailableUsers()->pluck('id')->toArray();
        foreach ($r->participant_ids as $pid) {
            if (!in_array($pid, $availableUserIds)) {
                abort(403, 'You are not authorized to message this user.');
            }
        }

        if ($r->type === 'private' && count($r->participant_ids) === 1) {
            // Check if private conversation already exists
            $existing = ChatConversation::where('type', 'private')
                ->whereHas('participants', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->whereHas('participants', function ($q) use ($r) {
                    $q->where('user_id', $r->participant_ids[0]);
                })
                ->first();

            if ($existing) {
                return redirect()->route($routePrefix . '.show', $existing->id);
            }
        }

        $conversation = ChatConversation::create([
            'type' => $r->type,
            'title' => $r->title,
            'created_by' => $userId,
        ]);

        // Add creator as admin
        ChatParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'role' => 'admin',
        ]);

        // Add other participants
        foreach ($r->participant_ids as $pid) {
            if ($pid != $userId) {
                ChatParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $pid,
                    'role' => 'member',
                ]);
            }
        }

        return redirect()->route($routePrefix . '.show', $conversation->id)->with('success', 'Conversation created.');
    }

    public function sendMessage(Request $r, $id)
    {
        $userId = Auth::id();
        $routePrefix = $this->resolveRoutePrefix();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $r->validate([
            'message' => 'required_without:file|string|nullable',
            'file' => 'nullable|file|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        if ($r->hasFile('file')) {
            $file = $r->file('file');
            $filePath = $file->store('chat-files', 'public');

            if (str_starts_with($file->getMimeType(), 'image/')) {
                $type = 'image';
            } else {
                $type = 'file';
            }
        }

        $msg = ChatMessage::create([
            'conversation_id' => $id,
            'sender_id' => $userId,
            'message' => $r->message,
            'type' => $type,
            'file_path' => $filePath,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json($msg->load('sender'));
        }

        return redirect()->route($routePrefix . '.show', $id);
    }

    public function destroyConversation($id)
    {
        $userId = Auth::id();
        $routePrefix = $this->resolveRoutePrefix();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('role', 'admin');
        })->findOrFail($id);

        $conversation->delete();

        return redirect()->route($routePrefix . '.index')->with('success', 'Conversation deleted.');
    }

    public function getMessages($id)
    {
        $userId = Auth::id();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $messages = ChatMessage::where('conversation_id', $id)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($messages);
    }
}
