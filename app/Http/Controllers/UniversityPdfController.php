<?php

namespace App\Http\Controllers;

use App\Models\UniversityPdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class UniversityPdfController extends BaseController
{
    use AuthorizesRequests;

    /**
     * Côté étudiant : affiche le PDF dans une visionneuse.
     */
    public function show()
    {
        $pdf = UniversityPdf::getCurrent();

        return Inertia::render('Etudiant/UniversityPdfViewer', [
            'pdf' => $pdf ? [
                'url' => $pdf->url,
                'filename' => $pdf->filename,
                'updated_at' => $pdf->updated_at->format('d/m/Y'),
            ] : null,
        ]);
    }

    /**
     * Côté conseiller : interface de gestion.
     */
    public function manage()
    {
        $this->authorize('managePdf', UniversityPdf::class);

        $pdf = UniversityPdf::getCurrent();

        return Inertia::render('Conseiller/UniversityPdfManager', [
            'pdf' => $pdf ? [
                'url' => $pdf->url,
                'filename' => $pdf->filename,
                'size' => round($pdf->size / 1024, 2) . ' Ko',
                'updated_at' => $pdf->updated_at->format('d/m/Y H:i'),
            ] : null,
        ]);
    }

    /**
     * Upload d'un nouveau PDF (remplace l'ancien).
     */
    public function upload(Request $request)
    {
        $this->authorize('managePdf', UniversityPdf::class);

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('pdf_file');

        // Supprimer l'ancien PDF
        $old = UniversityPdf::getCurrent();
        if ($old) {
            $old->delete();
        }

        // Stocker le nouveau
        $path = $file->store('pdfs', 'public');

        UniversityPdf::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
        ]);

        return redirect()->route('conseiller.university-pdf.manage')
            ->with('success', 'Le PDF a été mis à jour avec succès.');
    }

    /**
     * Supprimer le PDF (sans en remettre un nouveau).
     */
    public function delete()
    {
        $this->authorize('managePdf', UniversityPdf::class);

        $pdf = UniversityPdf::getCurrent();
        if ($pdf) {
            $pdf->delete();
        }

        return redirect()->route('conseiller.university-pdf.manage')
            ->with('success', 'PDF supprimé.');
    }
}