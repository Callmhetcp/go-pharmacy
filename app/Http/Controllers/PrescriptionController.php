<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    /**
     * Display customer's prescriptions.
     */
    public function index(): Response
    {
        $prescriptions = Prescription::query()
            ->where('user_id', Auth::id())
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return Inertia::render('Prescriptions/Index', [
            'prescriptions' => $prescriptions,
        ]);
    }

    /**
     * Show prescription submission form.
     */
    public function create(): Response
    {
        return Inertia::render('Prescriptions/Create');
    }

    /**
     * Store a new prescription.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'hospital_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'prescription_date' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'prescription_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $file = $request->file('prescription_file');

        $path = $file->store(
            'prescriptions',
            'public'
        );

        $prescription = Prescription::create([
            'user_id' => Auth::id(),

            'reference_number' => $this->generateReferenceNumber(),

            'doctor_name' => $validated['doctor_name'] ?? null,

            'hospital_name' => $validated['hospital_name'] ?? null,

            'prescription_date' =>
                $validated['prescription_date'] ?? null,

            'file_path' => $path,

            'file_type' => $file->getClientMimeType(),

            'notes' => $validated['notes'] ?? null,

            'status' => 'pending',
        ]);

        return redirect()
            ->route('prescriptions.show', $prescription)
            ->with(
                'success',
                'Your prescription has been submitted successfully.'
            );
    }

    /**
     * Display a customer's prescription.
     */
    public function show(Prescription $prescription): Response
    {
        abort_unless(
            $prescription->user_id === Auth::id(),
            403
        );

        $prescription->load([
            'items.product',
        ]);

        return Inertia::render('Prescriptions/Show', [
            'prescription' => $prescription,
        ]);
    }

    /**
     * Generate a unique prescription reference number.
     */
    private function generateReferenceNumber(): string
    {
        do {
            $reference = 'GP-RX-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(6));
        } while (
            Prescription::where(
                'reference_number',
                $reference
            )->exists()
        );

        return $reference;
    }
}