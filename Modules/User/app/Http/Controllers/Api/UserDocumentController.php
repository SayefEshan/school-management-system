<?php

namespace Modules\User\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Actions\UploadUserDocumentAction;
use Modules\User\Data\UserDocumentData;

class UserDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $documents = $request->user()->documents()->get();
        return response()->json($documents);
    }

    public function store(UserDocumentData $data, UploadUserDocumentAction $action): JsonResponse
    {
        // Validation is handled automatically by UserDocumentData injection
        
        $document = $action->execute(request()->user(), $data);

        return response()->json($document, 201);
    }
}
