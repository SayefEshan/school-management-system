<?php

namespace Modules\ImportDownloadManager\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FileManagerService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\ImportDownloadManager\Models\DownloadImportManager;

class DownloadImportManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:Download Import Manager Management'])->only('index', 'getStatusUpdate');
        $this->middleware(['can:Import Manager Data Download'])->only('downloadFile');
        $this->middleware(['can:Import Manager Data Delete'])->only('destroy');
    }

    public function index(Request $request)
    {
        $data['title'] = "Download Import Manager";

        $query = DownloadImportManager::with('user')->where('user_id', Auth::user()->id);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $data['downloadImports'] = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 10));

        return view('importdownloadmanager::index')->with($data);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy($id)
    {
        try {
            $downloadImport = DownloadImportManager::find($id);
            if (!$downloadImport || $downloadImport->type === 'Download') {
                return redirect()->back()->withErrors('Item not found!');
            }
            FileManagerService::deleteFile($downloadImport->url);
            $downloadImport->delete();
            return redirect()->back()->with('message', 'Item deleted successfully');
        } catch (Exception $ex) {
            Log::info($ex);
            return redirect()->back()->with('error', 'Error: ' . $ex->getMessage());
        }
    }

    public function getStatusUpdate(Request $request)
    {
        $ids = $request->ids;

        if (count($ids) > 0) {
            return DownloadImportManager::whereIn('id', $ids)->get();
        }
        return [];
    }

    public function downloadFile($id)
    {
        $downloadImport = DownloadImportManager::findOrFail($id);
        if (Storage::exists('/public/' . $downloadImport->url)) {
            return Storage::download('/public/' . $downloadImport->url);
        }
        return redirect()->back()->withErrors("File not found!");
    }
}
