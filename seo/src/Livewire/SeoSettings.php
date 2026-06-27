<?php

namespace Inox\Seo\Livewire;

use Livewire\Component;

class SeoSettings extends Component
{
    public string $metaTitle = '';

    public string $metaDescription = '';

    public function render()
    {
        return view('inox-seo::livewire.seo-settings');
    }

    public function save(): void
    {
        session()->flash('message', 'SEO settings saved.');
    }
}
