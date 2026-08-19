<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\Import;
use App\Models\Recipient;
use App\Models\StudentCategory;
use App\Services\FileImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentCategoryController extends Controller
{
    public function __construct(
        private FileImportService $fileImportService
    ) {}

    public function index(Request $request)
    {
        $categories = StudentCategory::whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->withCount('recipients');
            }])
            ->withCount('recipients')
            ->get();

        $allStudentsCount = Recipient::where('is_valid', true)->count();

        return view('categories.index', compact('categories', 'allStudentsCount'));
    }

    public function show(Request $request, StudentCategory $category)
    {
        $search = $request->query('search');

        $recipientsQuery = $category->getAllValidRecipientsQuery();

        if ($search) {
            $recipientsQuery->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $recipients = $recipientsQuery->paginate(20);
        $totalCount = $category->getAllValidRecipientsQuery()->count();

        return view('categories.show', compact('category', 'recipients', 'totalCount', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:student_categories,id',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        StudentCategory::create([
            'user_id' => auth()->id(),
            'parent_id' => $request->input('parent_id'),
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'type' => $request->input('type', 'custom'),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Student Contact Category created successfully.');
    }

    public function edit(StudentCategory $category)
    {
        $parents = StudentCategory::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, StudentCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:student_categories,id',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $category->update([
            'parent_id' => $request->input('parent_id'),
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'type' => $request->input('type', 'custom'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(StudentCategory $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Category Excel Upload Form
     */
    public function uploadForm(StudentCategory $category)
    {
        return view('categories.upload', compact('category'));
    }

    /**
     * Process Excel Upload specifically for a Student Category.
     */
    public function processUpload(Request $request, StudentCategory $category)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'phone_column' => 'nullable|string',
            'country_code' => 'nullable|string|max:5',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        // Detect columns
        $studentsImport = new StudentsImport();
        Excel::import($studentsImport, Storage::path($path));
        $columns = $studentsImport->getHeaders();
        $rows = $studentsImport->getRows();

        if (empty($columns) || empty($rows)) {
            Storage::delete($path);
            return back()->withErrors(['file' => 'Uploaded file is empty or missing valid columns.']);
        }

        // Detect phone column automatically if not selected
        $phoneColumn = $request->input('phone_column');
        if (!$phoneColumn) {
            foreach ($columns as $col) {
                if (stripos($col, 'phone') !== false || stripos($col, 'number') !== false || stripos($col, 'whatsapp') !== false || stripos($col, 'contact') !== false) {
                    $phoneColumn = $col;
                    break;
                }
            }
            if (!$phoneColumn) {
                $phoneColumn = $columns[count($columns) - 1]; // fallback to last column
            }
        }

        $countryCode = $request->input('country_code', '233');

        // Create Import record
        $import = Import::create([
            'user_id' => auth()->id(),
            'student_category_id' => $category->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'columns' => $columns,
            'phone_column' => $phoneColumn,
            'total_records' => count($rows),
            'status' => 'completed',
        ]);

        $processed = $this->fileImportService->processRows($rows, $phoneColumn, $countryCode);

        DB::beginTransaction();
        try {
            $categoryIds = [$category->id];

            $importedRecipients = [];

            foreach ($processed['valid'] as $row) {
                $recipient = Recipient::create([
                    'import_id' => $import->id,
                    'phone_number' => $row['phone_number'],
                    'data' => $row['data'],
                    'is_valid' => true,
                ]);

                // Sync pivot relation to category
                $recipient->categories()->syncWithoutDetaching($categoryIds);
                $importedRecipients[] = $recipient;
            }

            foreach ($processed['invalid'] as $row) {
                $recipient = Recipient::create([
                    'import_id' => $import->id,
                    'phone_number' => $row['phone_number'] ?? '',
                    'data' => $row['data'],
                    'is_valid' => false,
                    'validation_errors' => $row['errors'],
                ]);
                $recipient->categories()->syncWithoutDetaching($categoryIds);
            }

            $import->update([
                'valid_records' => count($processed['valid']),
                'invalid_records' => count($processed['invalid']),
            ]);

            DB::commit();

            return redirect()->route('categories.show', $category)
                ->with('success', "Uploaded " . count($processed['valid']) . " student contacts into category '{$category->name}'.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to upload category data: ' . $e->getMessage());
        }
    }
}
