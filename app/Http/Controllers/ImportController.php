<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Recipient;
use App\Imports\StudentsImport;
use App\Services\FileImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function __construct(
        private FileImportService $fileImportService
    ) {}

    public function index(Request $request)
    {
        $imports = Import::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('imports.index', compact('imports'));
    }

    public function create()
    {
        return view('imports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        // Detect columns using StudentsImport
        $studentsImport = new StudentsImport();
        Excel::import($studentsImport, Storage::path($path));
        $columns = $studentsImport->getHeaders();

        if (empty($columns)) {
            Storage::delete($path);
            return back()->withErrors(['file' => 'Could not detect any columns in the uploaded file.']);
        }

        $import = Import::create([
            'user_id' => auth()->id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'columns' => $columns,
            'total_records' => count($studentsImport->getRows()),
            'status' => 'pending',
            'valid_records' => 0,
            'invalid_records' => 0,
        ]);

        return redirect()->route('imports.preview', $import)
            ->with('success', 'File uploaded successfully. Please map your columns.');
    }

    public function preview(Request $request, Import $import)
    {
        if ($import->user_id !== auth()->id()) {
            abort(403);
        }

        $filePath = Storage::path($import->stored_path);
        $import_data = new StudentsImport();
        Excel::import($import_data, $filePath);
        $allRows = $import_data->getRows();
        $previewRows = array_slice($allRows, 0, 10);

        return view('imports.preview', compact('import', 'previewRows'));
    }

    public function process(Request $request, Import $import)
    {
        if ($import->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'phone_column' => 'required|string',
            'country_code' => 'nullable|string|max:5',
        ]);

        $phoneColumn = $request->input('phone_column');
        $countryCode = $request->input('country_code', '233');

        // Update import with phone column
        $import->update(['phone_column' => $phoneColumn]);

        // Read all rows from file
        $filePath = Storage::path($import->stored_path);
        $studentsImport = new StudentsImport();
        Excel::import($studentsImport, $filePath);
        $rows = $studentsImport->getRows();

        // Process and validate rows
        $processed = $this->fileImportService->processRows($rows, $phoneColumn, $countryCode);

        try {
            DB::beginTransaction();

            // Create recipient records
            foreach ($processed['valid'] as $row) {
                Recipient::create([
                    'import_id' => $import->id,
                    'phone_number' => $row['phone_number'],
                    'data' => $row['data'],
                    'is_valid' => true,
                ]);
            }

            foreach ($processed['invalid'] as $row) {
                Recipient::create([
                    'import_id' => $import->id,
                    'phone_number' => $row['phone_number'] ?? '',
                    'data' => $row['data'],
                    'is_valid' => false,
                    'validation_errors' => $row['errors'],
                ]);
            }

            // Update import counts
            $import->update([
                'total_records' => count($rows),
                'valid_records' => count($processed['valid']),
                'invalid_records' => count($processed['invalid']),
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->route('imports.show', $import)
                ->with('success', "Import completed! {$import->valid_records} valid records imported.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing import: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Import $import)
    {
        if ($import->user_id !== auth()->id()) {
            abort(403);
        }

        $search = $request->query('search');

        $recipients = $import->recipients()
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('phone_number', 'like', "%{$search}%")
                      ->orWhere('data', 'like', "%{$search}%");
                });
            })
            ->paginate(20);

        return view('imports.show', compact('import', 'recipients', 'search'));
    }

    public function destroy(Request $request, Import $import)
    {
        if ($import->user_id !== auth()->id()) {
            abort(403);
        }

        if ($import->campaigns()->exists()) {
            return back()->with('error', 'Cannot delete import because it is used by one or more campaigns.');
        }

        if (Storage::exists($import->stored_path)) {
            Storage::delete($import->stored_path);
        }

        $import->recipients()->delete();
        $import->delete();

        return redirect()->route('imports.index')
            ->with('success', 'Import deleted successfully.');
    }
}
