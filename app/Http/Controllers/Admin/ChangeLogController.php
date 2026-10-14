<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChangeLog;
use App\Support\ChangeLog as ChangeLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ChangeLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'user' => trim((string) $request->query('user', '')),
            'table' => trim((string) $request->query('table', '')),
            'action' => trim((string) $request->query('action', '')),
            'from' => trim((string) $request->query('from', '')),
            'to' => trim((string) $request->query('to', '')),
        ];

        $tables = collect(ChangeLogger::TABLES)
            ->map(fn ($label, $name) => ['title' => $label, 'value' => $name])
            ->values();
        $actions = collect(ChangeLogger::ACTIONS)
            ->map(fn ($label, $name) => ['title' => $label, 'value' => $name])
            ->values();

        if (! Schema::hasTable('change_logs')) {
            return spa('Admin/ChangeLog/Index', [
                'ready' => false,
                'logs' => [],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
                'filters' => $filters,
                'tables' => $tables,
                'actions' => $actions,
            ]);
        }

        $query = ChangeLog::query()->orderByDesc('id');
        if ($filters['user'] !== '') {
            $query->where('user_name', 'like', '%'.$filters['user'].'%');
        }
        if ($filters['table'] !== '') {
            $query->where('table_name', $filters['table']);
        }
        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }
        if ($filters['from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if ($filters['to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $page = $query->paginate(40)->withQueryString();

        return spa('Admin/ChangeLog/Index', [
            'ready' => true,
            'logs' => $page->getCollection()->map(fn (ChangeLog $row) => [
                'id' => $row->id,
                'created_at' => $row->created_at ? $row->created_at->format('d.m.Y H:i:s') : '',
                'user_name' => $row->user_name ?: 'Система',
                'role_name' => ChangeLogger::roleLabel($row->role_name),
                'action' => ChangeLogger::actionLabel($row->action),
                'table_name' => ChangeLogger::tableLabel($row->table_name),
                'record_id' => $row->record_id,
                'before' => self::formatValues($row->before),
                'after' => self::formatValues($row->after),
                'url' => $row->url,
            ])->values(),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
            'filters' => $filters,
            'tables' => $tables,
            'actions' => $actions,
        ]);
    }

    private static function formatValues(?array $values): string
    {
        if (! $values) {
            return '';
        }
        $lines = [];
        foreach ($values as $key => $value) {
            if ($key === '_values') {
                $lines[] = json_encode($value, JSON_UNESCAPED_UNICODE);
                continue;
            }
            if (is_bool($value)) {
                $shown = $value ? '1' : '0';
            } elseif (is_array($value)) {
                $shown = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $shown = (string) $value;
            }
            $lines[] = $key.': '.$shown;
        }

        return implode("\n", $lines);
    }
}
