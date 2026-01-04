<?php

namespace Modules\User\Services;

interface ThirdPartyDocumentVerifier
{
    public function verify(array $documentData): array;
}
