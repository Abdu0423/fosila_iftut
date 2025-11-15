<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LibraryController extends Controller
{
    /**
     * Display a listing of the library resources.
     */
    public function index(Request $request)
    {
        // Получаем список файлов из storage/app/public/library
        $files = [];
        $path = 'library';
        
        if (Storage::disk('public')->exists($path)) {
            $allFiles = Storage::disk('public')->allFiles($path);
            
            foreach ($allFiles as $file) {
                $files[] = [
                    'id' => md5($file),
                    'name' => basename($file),
                    'path' => $file,
                    'url' => Storage::url($file),
                    'size' => Storage::disk('public')->size($file),
                    'type' => $this->getFileType($file),
                    'extension' => pathinfo($file, PATHINFO_EXTENSION),
                    'modified' => Storage::disk('public')->lastModified($file),
                    'category' => $this->getCategory($file)
                ];
            }
        }

        // Фильтрация
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $files = array_filter($files, function($file) use ($search) {
                return str_contains(strtolower($file['name']), $search);
            });
        }

        if ($request->filled('type')) {
            $type = $request->type;
            $files = array_filter($files, function($file) use ($type) {
                return $file['type'] === $type;
            });
        }

        if ($request->filled('category')) {
            $category = $request->category;
            $files = array_filter($files, function($file) use ($category) {
                return $file['category'] === $category;
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'modified');
        $sortOrder = $request->get('sort_order', 'desc');
        
        usort($files, function($a, $b) use ($sortBy, $sortOrder) {
            $result = $a[$sortBy] <=> $b[$sortBy];
            return $sortOrder === 'desc' ? -$result : $result;
        });

        // Пагинация
        $perPage = 20;
        $page = $request->get('page', 1);
        $total = count($files);
        $files = array_slice($files, ($page - 1) * $perPage, $perPage);

        return Inertia::render('Admin/Library/Index', [
            'files' => [
                'data' => $files,
                'current_page' => (int)$page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ],
            'filters' => $request->only(['search', 'type', 'category', 'sort_by', 'sort_order']),
            'categories' => $this->getCategories(),
            'types' => ['document', 'image', 'video', 'audio', 'archive', 'other']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'category' => 'nullable|string|max:100'
        ]);

        $file = $request->file('file');
        $category = $request->category ?? 'uncategorized';
        
        // Создаем директорию категории, если не существует
        $path = "library/{$category}";
        
        // Сохраняем файл с оригинальным именем
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs($path, $fileName, 'public');

        return back()->with('success', 'Файл успешно загружен в библиотеку');
    }

    /**
     * Download the specified resource.
     */
    public function download(Request $request)
    {
        $path = $request->query('path');
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('public')->download($path);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $path = $request->path;
        
        if (!Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Файл не найден');
        }

        Storage::disk('public')->delete($path);

        return back()->with('success', 'Файл успешно удален');
    }

    /**
     * Bulk delete files
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'string'
        ]);

        foreach ($request->paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return back()->with('success', 'Выбранные файлы удалены');
    }

    /**
     * Get file type based on extension
     */
    private function getFileType($file)
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        $types = [
            'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'xls', 'xlsx', 'ppt', 'pptx'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
            'audio' => ['mp3', 'wav', 'ogg', 'wma', 'm4a', 'flac'],
            'archive' => ['zip', 'rar', '7z', 'tar', 'gz']
        ];

        foreach ($types as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return 'other';
    }

    /**
     * Get category from file path
     */
    private function getCategory($file)
    {
        $parts = explode('/', $file);
        return count($parts) > 1 ? $parts[1] : 'uncategorized';
    }

    /**
     * Get all categories
     */
    private function getCategories()
    {
        $directories = Storage::disk('public')->directories('library');
        $categories = [];
        
        foreach ($directories as $dir) {
            $categories[] = basename($dir);
        }

        if (empty($categories)) {
            $categories = ['uncategorized'];
        }

        return $categories;
    }
}

