<?php

namespace App\Http\Controllers\AuditCompliance;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('user_id'), fn (Builder $query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('module'), fn (Builder $query) => $query->where('module', $request->module))
            ->when($request->filled('action'), fn (Builder $query) => $query->where('action', $request->action))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('audit-compliance.audit-logs', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'modules' => AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
