<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class SyllabusController extends Controller
{
    /**
     * Показать список всех силлабусов
     */
    public function index()
    {
        $syllabuses = Syllabus::with(['subject', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($syllabus) {
                return [
                    'id' => $syllabus->id,
                    'name' => $syllabus->name,
                    'description' => $syllabus->description,
                    'file_name' => $syllabus->file_name,
                    'file_type' => $syllabus->file_type,
                    'file_size_formatted' => $syllabus->file_size_formatted,
                    'subject_name' => $syllabus->subject ? $syllabus->subject->name : 'Не указан',
                    'creator_name' => $syllabus->creator ? $syllabus->creator->name : 'Неизвестно',
                    'creation_year' => $syllabus->creation_year,
                    'created_at' => $syllabus->created_at ? $syllabus->created_at->format('d.m.Y H:i') : 'Не указано',
                    'download_url' => $syllabus->download_url,
                    'preview_url' => $syllabus->preview_url,
                ];
            });

        $stats = [
            'total' => Syllabus::count(),
            'this_year' => Syllabus::where('creation_year', date('Y'))->count(),
            'last_year' => Syllabus::where('creation_year', date('Y') - 1)->count(),
            'with_files' => Syllabus::whereNotNull('file_path')->count(),
        ];

        return spa('Admin/Syllabuses/Index', [
            'syllabuses' => $syllabuses,
            'stats' => $stats
        ]);
    }

    /**
     * Показать форму создания силлабуса
     */
    public function create()
    {
        $subjects = Subject::orderBy('name')->get()->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'display_name' => $subject->name
            ];
        });

        $years = [];
        $currentYear = date('Y');
        for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
            $years[] = $i;
        }

        return spa('Admin/Syllabuses/Create', [
            'subjects' => $subjects,
            'years' => $years
        ]);
    }

    /**
     * Сохранить новый силлабус
     */
    public function store(Request $request)
    {
        \Log::info('Начало создания силлабуса', [
            'request_data' => $request->except(['file']),
            'has_file' => $request->hasFile('file'),
            'all_data' => $request->all()
        ]);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'creation_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png,gif,bmp,webp,md,html,css,js,json,xml,csv,log|max:10240' // Максимум 10MB
        ]);

        try {
            $file = $request->file('file');
            \Log::info('Файл получен', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('syllabuses', $fileName, 'public');
            
            \Log::info('Файл сохранен', ['file_path' => $filePath]);

            $syllabus = Syllabus::create([
                'name' => $request->name,
                'description' => $request->description,
                'subject_id' => $request->subject_id,
                'creation_year' => $request->creation_year,
                'created_by' => auth()->id(),
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            \Log::info('Силлабус создан', ['syllabus_id' => $syllabus->id]);

            return response()->json(['redirect' => route('admin.syllabuses.show', $syllabus->id)]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании силлабуса', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Ошибка при создании силлабуса: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Показать информацию о силлабусе
     */
    public function show(Syllabus $syllabus)
    {
        $syllabusData = [
            'id' => $syllabus->id,
            'name' => $syllabus->name,
            'description' => $syllabus->description,
            'file_name' => $syllabus->file_name,
            'file_type' => $syllabus->file_type,
            'file_size_formatted' => $syllabus->file_size_formatted,
            'subject_name' => $syllabus->subject ? $syllabus->subject->name : 'Не указан',
            'subject_id' => $syllabus->subject_id,
            'creator_name' => $syllabus->creator ? $syllabus->creator->name : 'Неизвестно',
            'creation_year' => $syllabus->creation_year,
            'created_at' => $syllabus->created_at ? $syllabus->created_at->format('d.m.Y H:i') : 'Не указано',
            'updated_at' => $syllabus->updated_at ? $syllabus->updated_at->format('d.m.Y H:i') : 'Не указано',
            'download_url' => $syllabus->download_url,
            'preview_url' => $syllabus->preview_url,
        ];

        return spa('Admin/Syllabuses/Show', [
            'syllabus' => $syllabusData
        ]);
    }

    /**
     * Показать форму редактирования силлабуса
     */
    public function edit(Syllabus $syllabus)
    {
        $lessons = Lesson::orderBy('name')->get()->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'display_name' => $lesson->name
            ];
        });

        $years = [];
        $currentYear = date('Y');
        for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
            $years[] = $i;
        }

        return spa('Admin/Syllabuses/Edit', [
            'syllabus' => [
                'id' => $syllabus->id,
                'name' => $syllabus->name,
                'description' => $syllabus->description,
                'subject_id' => $syllabus->subject_id,
                'creation_year' => $syllabus->creation_year,
                'file_name' => $syllabus->file_name,
                'file_type' => $syllabus->file_type,
                'file_size_formatted' => $syllabus->file_size_formatted,
            ],
            'lessons' => $lessons,
            'years' => $years
        ]);
    }

    /**
     * Обновить силлабус
     */
    public function update(Request $request, Syllabus $syllabus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'creation_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,md,html,css,js,json,xml,csv,log|max:10240'
        ]);

        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'subject_id' => $request->subject_id,
                'creation_year' => $request->creation_year,
            ];

            // Если загружен новый файл
            if ($request->hasFile('file')) {
                // Удаляем старый файл
                if ($syllabus->file_path && Storage::disk('public')->exists($syllabus->file_path)) {
                    Storage::disk('public')->delete($syllabus->file_path);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('syllabuses', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            $syllabus->update($data);

            return redirect()->route('admin.syllabuses.show', $syllabus->id)
                ->with('success', 'Силлабус успешно обновлен');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении силлабуса: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Удалить силлабус
     */
    public function destroy(Syllabus $syllabus)
    {
        try {
            // Удаляем файл
            if ($syllabus->file_path && Storage::disk('public')->exists($syllabus->file_path)) {
                Storage::disk('public')->delete($syllabus->file_path);
            }

            $syllabus->delete();

            return redirect()->route('admin.syllabuses.index')
                ->with('success', 'Силлабус успешно удален');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при удалении силлабуса: ' . $e->getMessage());
        }
    }

    /**
     * Скачать файл силлабуса
     */
    public function download(Syllabus $syllabus)
    {
        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('public')->download($syllabus->file_path, $syllabus->file_name);
    }

    /**
     * Предварительный просмотр файла
     */
    public function preview(Syllabus $syllabus)
    {
        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            abort(404, 'Файл не найден');
        }

        // Для PDF файлов показываем в браузере
        if ($syllabus->file_type === 'application/pdf') {
            return response()->file(Storage::disk('public')->path($syllabus->file_path));
        }

        // Для других файлов предлагаем скачать
        return redirect()->route('admin.syllabuses.download', $syllabus->id);
    }

    /**
     * Получить содержимое файла для просмотра в текстовом редакторе
     */
    public function getFileContent(Syllabus $syllabus)
    {
        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        $filePath = Storage::disk('public')->path($syllabus->file_path);
        $fileExtension = pathinfo($syllabus->file_name, PATHINFO_EXTENSION);
        
        // Поддерживаемые текстовые форматы
        $textFormats = ['txt', 'md', 'html', 'css', 'js', 'json', 'xml', 'csv', 'log'];
        
        if (!in_array(strtolower($fileExtension), $textFormats)) {
            return response()->json([
                'error' => 'Этот тип файла не поддерживается для текстового просмотра',
                'file_type' => $syllabus->file_type,
                'file_extension' => $fileExtension
            ], 400);
        }

        try {
            $content = file_get_contents($filePath);
            
            // Определяем кодировку файла
            $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251', 'ISO-8859-1'], true);
            
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }
            
            return response()->json([
                'content' => $content,
                'file_name' => $syllabus->file_name,
                'file_type' => $syllabus->file_type,
                'file_size' => $syllabus->file_size,
                'encoding' => $encoding ?: 'UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при чтении файла: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить URL для просмотра PDF документа
     */
    public function getPdfViewerUrl(Syllabus $syllabus)
    {
        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        $fileExtension = pathinfo($syllabus->file_name, PATHINFO_EXTENSION);
        
        if (strtolower($fileExtension) !== 'pdf') {
            return response()->json([
                'error' => 'Этот файл не является PDF документом',
                'file_type' => $syllabus->file_type,
                'file_extension' => $fileExtension
            ], 400);
        }

        // Возвращаем URL для просмотра PDF
        $pdfUrl = Storage::disk('public')->url($syllabus->file_path);
        
        return response()->json([
            'pdf_url' => $pdfUrl,
            'file_name' => $syllabus->file_name,
            'file_type' => $syllabus->file_type,
            'file_size' => $syllabus->file_size
        ]);
    }

    /**
     * Получить URL для просмотра Word документа
     */
    public function getWordViewerUrl(Syllabus $syllabus)
    {
        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        $fileExtension = pathinfo($syllabus->file_name, PATHINFO_EXTENSION);
        $wordFormats = ['doc', 'docx'];
        
        if (!in_array(strtolower($fileExtension), $wordFormats)) {
            return response()->json([
                'error' => 'Этот файл не является документом Word',
                'file_type' => $syllabus->file_type,
                'file_extension' => $fileExtension
            ], 400);
        }

        // Создаем публичный URL для файла
        $fileUrl = Storage::disk('public')->url($syllabus->file_path);
        
        // Альтернативные URL для просмотра Word документов
        $viewerUrls = [
            // Google Docs Viewer (более надежный)
            'google' => 'https://docs.google.com/viewer?url=' . urlencode($fileUrl) . '&embedded=true',
            // Microsoft Office Online (основной)
            'office' => 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($fileUrl),
            // Альтернативный Microsoft Office Online URL
            'office_alt' => 'https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($fileUrl)
        ];
        
        return response()->json([
            'viewer_urls' => $viewerUrls,
            'file_url' => $fileUrl,
            'file_name' => $syllabus->file_name,
            'file_type' => $syllabus->file_type,
            'file_size' => $syllabus->file_size,
            'primary_viewer' => 'google' // Используем Google Docs как основной
        ]);
    }

    /**
     * Конвертировать Word документ в HTML для просмотра
     */
    public function convertWordToHtml(Syllabus $syllabus)
    {
        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        $fileExtension = pathinfo($syllabus->file_name, PATHINFO_EXTENSION);
        $wordFormats = ['doc', 'docx'];
        
        if (!in_array(strtolower($fileExtension), $wordFormats)) {
            return response()->json([
                'error' => 'Этот файл не является документом Word',
                'file_type' => $syllabus->file_type,
                'file_extension' => $fileExtension
            ], 400);
        }

        try {
            $filePath = Storage::disk('public')->path($syllabus->file_path);
            
            // Настройки PhpWord
            Settings::setOutputEscapingEnabled(true);
            
            // Загружаем документ
            $phpWord = IOFactory::load($filePath);
            
            // Конвертируем в HTML
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            
            // Создаем временный файл для HTML
            $htmlContent = '';
            ob_start();
            $htmlWriter->save('php://output');
            $htmlContent = ob_get_clean();
            
            // Очищаем HTML от лишних тегов и стилей
            $htmlContent = $this->cleanHtmlContent($htmlContent);
            
            return response()->json([
                'html_content' => $htmlContent,
                'file_name' => $syllabus->file_name,
                'file_type' => $syllabus->file_type,
                'file_size' => $syllabus->file_size
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при конвертации документа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Очистка HTML контента
     */
    private function cleanHtmlContent($html)
    {
        // Удаляем лишние теги и стили
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<meta[^>]*>/is', '', $html);
        $html = preg_replace('/<link[^>]*>/is', '', $html);
        $html = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $html);
        
        // Оставляем только основной контент
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }
        
        // Добавляем базовые стили
        $html = '<div style="font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto;">' . $html . '</div>';
        
        return $html;
    }
}
