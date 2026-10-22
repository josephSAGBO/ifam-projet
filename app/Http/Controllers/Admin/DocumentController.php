<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    use HasSiteFilter;

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        $query = Document::with('uploadedBy');

        if ($selectedSiteId !== null) {
            $query->whereHasMorph(
                'documentable',
                [StudentProfile::class, TeacherProfile::class],
                function (Builder $ownerQuery, string $type) use ($selectedSiteId): void {
                    if ($type === StudentProfile::class) {
                        $ownerQuery->whereHas('enrollments', fn (Builder $q) => $q->where('site_id', $selectedSiteId));
                    } elseif ($type === TeacherProfile::class) {
                        $ownerQuery->whereHas('courses.academicSession', fn (Builder $q) => $q->where('site_id', $selectedSiteId));
                    }
                }
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('owner_type')) {
            $query->where('documentable_type', $request->owner_type);
        }
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === '1');
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $typeLabels = Document::typeLabels();

        return view('admin.documents.index', compact('documents', 'typeLabels', 'sites', 'selectedSiteId'));
    }

    public function create()
    {
        $students = StudentProfile::with('user')->get()->sortBy(fn ($s) => $s->user->last_name ?? '');
        $teachers = TeacherProfile::with('user')->get()->sortBy(fn ($t) => $t->user->last_name ?? '');
        $typeLabels = Document::typeLabels();

        return view('admin.documents.create', compact('students', 'teachers', 'typeLabels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', array_keys(Document::typeLabels())),
            'owner_type' => 'required|in:student,teacher',
            'owner_id' => 'required|integer',
            'file' => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'notes' => 'nullable|string',
        ]);

        // Upload du fichier
        $file = $request->file('file');
        $folder = 'documents/'.date('Y/m');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        // Résoudre le modèle propriétaire
        if ($request->owner_type === 'student') {
            $owner = StudentProfile::findOrFail($request->owner_id);
        } else {
            $owner = TeacherProfile::findOrFail($request->owner_id);
        }

        $owner->documents()->create([
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'uploaded_by' => auth()->id(),
            'is_verified' => false,
        ]);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document uploadé avec succès.');
    }

    public function show(Document $document)
    {
        return view('admin.documents.show', compact('document'));
    }

    public function toggleVerified(Document $document)
    {
        $document->update(['is_verified' => ! $document->is_verified]);

        return back()->with('success', $document->is_verified ? 'Document vérifié.' : 'Vérification retirée.');
    }

    public function destroy(Document $document)
    {
        // Supprimer le fichier physique
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document supprimé.');
    }
}
