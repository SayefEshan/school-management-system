<?php

namespace Modules\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\User\Models\UserDocument;
use Modules\User\Services\ThirdPartyDocumentVerifier;
use Illuminate\Support\Facades\Log;

class VerifyUserDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public UserDocument $document,
        public string $status,
        public ?int $verifiedById = null,
        public ?string $rejectionReason = null
    ) {}

    public function handle(ThirdPartyDocumentVerifier $verifier): void
    {
        $data = [
            'status' => $this->status,
            'verified_at' => now(),
            'verified_by' => $this->verifiedById,
            'rejection_reason' => $this->rejectionReason,
        ];

        if ($this->status === 'verified') {
             try {
                 $verificationResult = $verifier->verify([
                    'document_type' => $this->document->document_type,
                    'document_number' => $this->document->document_number,
                    'file_path' => $this->document->file_path
                 ]);
                 
                 $data['verification_response'] = $verificationResult;
             } catch (\Exception $e) {
                 Log::error('Third Party Verification Failed: ' . $e->getMessage());
                 // Optionally fail the job or update status to 'manual_review_required'
             }
        }

        $this->document->update($data);
    }
}
