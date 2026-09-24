<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChannelController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Channel::class);

        $channels = Channel::orderBy('id', 'desc')->paginate(10);

        return view('admin.channels.index', compact('channels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Channel::class);

        return view('admin.channels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Channel $channel)
    {
        return view('admin.channels.show', compact('channel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Channel $channel)
    {
        $this->authorize('edit', $channel);

        return view('admin.channels.edit', compact('channel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Channel $channel)
    {
        $this->authorize('delete', $channel);

        if ($channel->image_url && Storage::disk('public')->exists($channel->image_url)) {
            Storage::disk('public')->delete($channel->image_url);
        }

        $channel->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Channel deleted successfully.'),
        ]);

        return redirect()->route('admin.channels.index');
    }
}
