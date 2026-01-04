<?php

namespace Modules\User\Actions;

class UploadUserDocumentAction
{
    public function execute(\App\Models\User $user, \Modules\User\Data\UserDocumentData $data): \Modules\User\Models\UserDocument
    {
        // Check if a document of the same type already exists for this user
        $existingDocument = \Modules\User\Models\UserDocument::where('user_id', $user->id)
            ->where('document_type', $data->document_type)
            ->first();

        // Upload new files
        $path = \App\Services\FileManagerService::uploadFile($data->file, null, 'documents/users');
        
        $backPath = null;
        if ($data->back_file) {
            $backPath = \App\Services\FileManagerService::uploadFile($data->back_file, null, 'documents/users');
        }

        if ($existingDocument) {
            // Delete old files before replacing
            if ($existingDocument->file_path) {
                \App\Services\FileManagerService::deleteFile($existingDocument->file_path);
            }
            if ($existingDocument->back_file_path) {
                \App\Services\FileManagerService::deleteFile($existingDocument->back_file_path);
            }

            // Update existing document
            $existingDocument->update([
                'document_number' => $data->document_number,
                'file_path' => $path,
                'back_file_path' => $backPath,
                'expiry_date' => $data->expiry_date,
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'rejection_reason' => null,
                'verification_response' => null,
            ]);

            $document = $existingDocument;
        } else {
            // Create new document
            $document = \Modules\User\Models\UserDocument::create([
                'user_id' => $user->id,
                'document_type' => $data->document_type,
                'document_number' => $data->document_number,
                'file_path' => $path,
                'back_file_path' => $backPath,
                'expiry_date' => $data->expiry_date,
                'status' => 'pending',
            ]);
        }

        // Dispatch verification job automatically
        \Modules\User\Jobs\VerifyUserDocumentJob::dispatch($document, 'verified');

        return $document;
    }
}
