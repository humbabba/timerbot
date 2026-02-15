<?php

namespace App\Http\Controllers;

use App\Models\Trash;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $query = Trash::with('deletedByUser')
            ->orderBy('deleted_at', 'desc');

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->deletedBetween($request->from, $request->to);
        }

        $trashItems = $query->paginate(20)->withQueryString();

        $types = Trash::select('trashable_type')
            ->distinct()
            ->pluck('trashable_type')
            ->mapWithKeys(fn($type) => [$type => class_basename($type)]);

        return view('trash.index', compact('trashItems', 'types'));
    }

    public function show(Trash $trash)
    {
        $trash->load('deletedByUser');

        return view('trash.show', compact('trash'));
    }

    public function restore(Trash $trash)
    {
        $modelName = class_basename($trash->trashable_type);
        $displayName = $trash->display_name;

        $restored = $trash->restore();

        if (!$restored) {
            return back()->with('error', "Failed to restore {$modelName}.");
        }

        return redirect()->route('trash.index')
            ->with('status', "{$modelName} \"{$displayName}\" has been restored.");
    }

    public function destroy(Trash $trash)
    {
        $modelName = class_basename($trash->trashable_type);
        $displayName = $trash->display_name;

        $trash->delete();

        return redirect()->route('trash.index')
            ->with('status', "{$modelName} \"{$displayName}\" has been permanently deleted.");
    }

    public function empty(Request $request)
    {
        $query = Trash::query();

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        $count = $query->count();
        $query->delete();

        return redirect()->route('trash.index')
            ->with('status', "{$count} item(s) have been permanently deleted.");
    }
}
