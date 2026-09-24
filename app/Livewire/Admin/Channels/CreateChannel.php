<?php

namespace App\Livewire\Admin\Channels;

use App\Models\Channel;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateChannel extends Component
{
    use WithFileUploads;

    public $image_url;

    public $number;

    public $area = '';

    public $origin = '';

    public $name;

    public $url;

    public $category = '';

    public $audio_spanish_enabled;

    public $audio_english_enabled;

    public $subtitles_enabled;

    public $status = '';

    public $profiles = [];

    public function boot()
    {
        $this->withValidator(function ($validator) {
            if ($validator->fails()) {
                $errorMessages = '<ul style="list-style-type: disc; padding-left: 20px; margin-left: 0; padding-right: 0;">';

                foreach ($validator->errors()->all() as $error) {
                    $errorMessages .= "<li style='list-style-position: inside;'>$error</li>";
                }

                $errorMessages .= '</ul>';

                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => '¡Error!',
                    'html' => '<b>'.__('Your registration for a new channel contains the following errors:').'</b><br><br>'.$errorMessages,
                ]);
            }
        });
    }

    public function store()
    {
        $this->validate([
            'image_url' => 'required|unique:channels,image_url',
            'number' => 'required|integer',
            'area' => 'required|string|in:OTT,DTH,DTH/OTT',
            'origin' => 'nullable|string',
            'name' => 'required|string',
            'url' => 'nullable',
            'category' => 'required|string',
            'audio_spanish_enabled' => 'nullable|boolean',
            'audio_english_enabled' => 'nullable|boolean',
            'subtitles_enabled' => 'nullable|boolean',
            'profiles' => 'nullable|array',
            'profiles.high' => 'nullable|string',
            'profiles.medium' => 'nullable|string',
            'profiles.low' => 'nullable|string',
            'status' => 'required|string',
        ], [], [
            'image_url' => __('channel image'),
            'number' => __('channel number'),
            'area' => __('channel area'),
            'origin' => __('channel origin'),
            'name' => __('channel name'),
            'url' => __('channel URL'),
            'category' => __('channel category'),
            'audio_spanish_enabled' => __('Spanish audio'),
            'audio_english_enabled' => __('English audio'),
            'subtitles_enabled' => __('Subtitles'),
            'status' => __('channel status'),
        ]);

        $imageName = time().'_'.$this->image_url->getClientOriginalName();

        $this->image_url->storeAs('channels', $imageName, 'public');

        $channel = Channel::create([
            'image_url' => 'channels/'.$imageName,
            'number' => $this->number,
            'area' => $this->area,
            'origin' => $this->origin,
            'name' => $this->name,
            'url' => $this->url,
            'category' => $this->category,
            'audio_spanish_enabled' => $this->audio_spanish_enabled,
            'audio_english_enabled' => $this->audio_english_enabled,
            'subtitles_enabled' => $this->subtitles_enabled,
            'status' => $this->status,
            'profiles' => $this->profiles ?: null,
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('New channel created successfully.'),
        ]);

        return redirect()->route('admin.channels.show', $channel);
    }

    public function render()
    {
        return view('livewire.admin.channels.create-channel');
    }
}
