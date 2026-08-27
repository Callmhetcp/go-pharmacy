<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    /**
     * Display the authenticated customer's prescriptions.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $prescriptions = Prescription::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(10);

        return PrescriptionResource::collection($prescriptions);
    }

    /**
     * Display a single customer prescription.
     */
    public function show(
        Request $request,
        Prescription $prescription
    ): PrescriptionResource {
        $this->authorizePrescription($request, $prescription);

        $prescription->load('items');

        return new PrescriptionResource($prescription);
    }

    /**
     * Upload a new prescription.
     */
    public function store(Request $request): PrescriptionResource
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

            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $file = $request->file('file');

        $path = $file->store(
            'prescriptions',
            'local'
        );

        $prescription = Prescription::create([
            'user_id' => $request->user()->id,
            'reference_number' => $this->generateReferenceNumber(),

            'doctor_name' => $validated['doctor_name'] ?? null,
            'hospital_name' => $validated['hospital_name'] ?? null,
            'prescription_date' => $validated['prescription_date'] ?? null,

            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),

            'notes' => $validated['notes'] ?? null,

            'status' => 'pending',
        ]);

        return new PrescriptionResource($prescription);
    }

    /**
     * Update a customer's prescription.
     */
    public function update(
        Request $request,
        Prescription $prescription
    ): PrescriptionResource|JsonResponse {
        $this->authorizePrescription($request, $prescription);

        /*
         * Customers cannot modify prescriptions that have
         * already been approved or fulfilled.
         */
        if ($this->isLocked($prescription)) {
            return response()->json([
                'success' => false,
                'message' => 'Approved or fulfilled prescriptions cannot be modified.',
            ], 422);
        }

        $validated = $request->validate([
            'doctor_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'hospital_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'prescription_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            'file' => [
                'sometimes',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
        ]);

        $oldFilePath = $prescription->file_path;
        $newFilePath = null;

        /*
         * Upload the replacement file first.
         *
         * This prevents us from deleting the existing file before
         * we know that the new file has been stored successfully.
         */
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $newFilePath = $file->store(
                'prescriptions',
                'local'
            );

            $validated['file_path'] = $newFilePath;
            $validated['file_type'] = $file->getClientMimeType();

            /*
             * A replacement file must go through prescription
             * review again.
             */
            $validated['status'] = 'pending';
            $validated['rejection_reason'] = null;
            $validated['review_notes'] = null;
            $validated['reviewed_by'] = null;
            $validated['reviewed_at'] = null;

            unset($validated['file']);
        }

        try {
            $prescription->update($validated);
        } catch (\Throwable $exception) {
            /*
             * If the database update fails, remove the newly
             * uploaded file so it does not become orphaned.
             */
            if ($newFilePath) {
                Storage::disk('local')->delete($newFilePath);
            }

            throw $exception;
        }

        /*
         * Delete the old file only after the database update
         * succeeds.
         */
        if (
            $newFilePath &&
            $oldFilePath &&
            Storage::disk('local')->exists($oldFilePath)
        ) {
            Storage::disk('local')->delete($oldFilePath);
        }

        return new PrescriptionResource(
            $prescription->fresh()->load('items')
        );
    }

    /**
     * Delete a customer's prescription.
     */
    public function destroy(
        Request $request,
        Prescription $prescription
    ): JsonResponse {
        $this->authorizePrescription($request, $prescription);

        /*
         * Customers cannot delete prescriptions that have
         * already been approved or fulfilled.
         */
        if ($this->isLocked($prescription)) {
            return response()->json([
                'success' => false,
                'message' => 'Approved or fulfilled prescriptions cannot be deleted.',
            ], 422);
        }

        $filePath = $prescription->file_path;

        $prescription->delete();

        /*
         * Delete the private file after the prescription record
         * has been removed.
         */
        if (
            $filePath &&
            Storage::disk('local')->exists($filePath)
        ) {
            Storage::disk('local')->delete($filePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prescription deleted successfully.',
        ]);
    }

    /**
     * Ensure the prescription belongs to the authenticated customer.
     */
    protected function authorizePrescription(
        Request $request,
        Prescription $prescription
    ): void {
        abort_unless(
            $prescription->user_id === $request->user()->id,
            404
        );
    }

    /**
     * Determine whether the prescription can still be modified
     * or deleted by the customer.
     */
    protected function isLocked(Prescription $prescription): bool
    {
        return in_array(
            $prescription->status,
            ['approved', 'fulfilled'],
            true
        );
    }

    /**
     * Generate a unique prescription reference number.
     */
    protected function generateReferenceNumber(): string
    {
        do {
            $reference = 'GP-RX-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(8));
        } while (
            Prescription::query()
                ->where('reference_number', $reference)
                ->exists()
        );

        return $reference;
    }
}