<?php

namespace Inox\Storage\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inox\Storage\Models\Media;

#[Layout('layouts.admin')]
class MediaManager extends Component
{
    use WithFileUploads;

    public $upload;

    public string $search = '';

    public string $viewMode = 'grid';

    public ?int $editingId = null;

    public string $editAlt = '';

    public string $editCaption = '';

    protected function rules(): array
    {
        return [
            'upload' => 'required|file|mimes:' . implode(',', config('storage.allowed_mimes', [])) . '|max:' . config('storage.max_file_size', 10240),
        ];
    }

    public function updatedUpload(): void
    {
        $this->validate();

        $file = $this->upload;
        $originalName = $file->getClientOriginalName();
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $path = $file->store('/' . date('Y/m'), 'media');

        $dimensions = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                [$width, $height] = getimagesize($file->getRealPath());
                $dimensions = ['width' => $width, 'height' => $height];
            } catch (\Exception $e) {
                $dimensions = [];
            }
        }

        Media::create(array_merge([
            'user_id' => auth()->id(),
            'name' => $name,
            'original_name' => $originalName,
            'path' => $path,
            'disk' => 'media',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ], $dimensions ?? []));

        $this->upload = null;
        session()->flash('message', 'File uploaded.');
    }

    public function startEdit(int $id): void
    {
        $media = Media::findOrFail($id);
        $this->editingId = $media->id;
        $this->editAlt = $media->alt_text ?? '';
        $this->editCaption = $media->caption ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editAlt' => 'nullable|max:500',
            'editCaption' => 'nullable|max:1000',
        ]);

        Media::findOrFail($this->editingId)->update([
            'alt_text' => $this->editAlt,
            'caption' => $this->editCaption,
        ]);

        $this->editingId = null;
        session()->flash('message', 'Media updated.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        $media = Media::findOrFail($id);
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
        session()->flash('message', 'File moved to trash.');
    }

    public function render()
    {
        $files = Media::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('original_name', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('storage::livewire.media-manager', [
            'files' => $files,
        ]);
    }
}
