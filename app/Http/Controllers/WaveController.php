<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Wave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Wave::with('nodes')->orderBy('name');

        if ($request->filled('search')) {
            $query->search($request->search, ['name', 'description']);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $waves = $query->paginate(20)->withQueryString();
        $favoriteWaveIds = Auth::user()->favoriteWaves()->pluck('waves.id')->toArray();

        return view('waves.index', compact('waves', 'favoriteWaveIds'));
    }

    public function favorites(Request $request)
    {
        $query = Auth::user()->favoriteWaves()->with('nodes')->orderBy('name');

        if ($request->filled('search')) {
            $query->search($request->search, ['name', 'description']);
        }

        $waves = $query->paginate(20)->withQueryString();

        return view('waves.favorites', compact('waves'));
    }

    public function toggleFavorite(Wave $wave)
    {
        $user = Auth::user();
        $result = $user->favoriteWaves()->toggle($wave->id);
        $isFavorite = in_array($wave->id, $result['attached']);

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function create()
    {
        $nodes = Node::all();

        return view('waves.create', compact('nodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|exists:nodes,id',
            'nodes.*.mappings' => 'nullable|array',
            'nodes.*.include_in_output' => 'nullable|boolean',
        ]);

        $wave = Wave::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->syncNodes($wave, $validated['nodes']);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'wave' => $wave]);
        }

        return redirect()->route('waves.index')->with('status', 'Wave created successfully.');
    }

    public function show(Wave $wave)
    {
        $wave->load('nodes');

        return view('waves.show', compact('wave'));
    }

    public function edit(Wave $wave)
    {
        $wave->load('nodes');
        $nodes = Node::all();

        return view('waves.edit', compact('wave', 'nodes'));
    }

    public function update(Request $request, Wave $wave)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|exists:nodes,id',
            'nodes.*.mappings' => 'nullable|array',
            'nodes.*.include_in_output' => 'nullable|boolean',
        ]);

        $wave->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->syncNodes($wave, $validated['nodes']);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'wave' => $wave]);
        }

        return redirect()->route('waves.index')->with('status', 'Wave updated successfully.');
    }

    public function destroy(Wave $wave)
    {
        $wave->delete();

        return redirect()->route('waves.index')->with('status', 'Wave deleted successfully.');
    }

    public function copy(Wave $wave)
    {
        $copy = $wave->duplicate();

        return redirect()->route('waves.edit', $copy)->with('status', 'Wave copied successfully.');
    }

    protected function syncNodes(Wave $wave, array $nodeData): void
    {
        $wave->nodes()->detach();

        foreach ($nodeData as $position => $data) {
            // Filter out empty mappings (where type is empty)
            $mappings = [];
            if (isset($data['mappings']) && is_array($data['mappings'])) {
                foreach ($data['mappings'] as $field => $mapping) {
                    if (!empty($mapping['type'])) {
                        $mappings[$field] = $mapping;
                    }
                }
            }

            $wave->nodes()->attach($data['id'], [
                'position' => $position,
                'mappings' => !empty($mappings) ? json_encode($mappings) : null,
                'include_in_output' => isset($data['include_in_output']) ? (bool) $data['include_in_output'] : true,
            ]);
        }
    }
}
