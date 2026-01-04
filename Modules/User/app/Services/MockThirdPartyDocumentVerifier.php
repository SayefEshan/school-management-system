<?php

namespace Modules\User\Services;

class MockThirdPartyDocumentVerifier implements ThirdPartyDocumentVerifier
{
    public function verify(array $documentData): array
    {
        // Simulate an external API call
        return [
            'verified' => true,
            'score' => 95,
            'details' => 'Document appears valid based on mock check.',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
