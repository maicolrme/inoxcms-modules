<?php

namespace Inox\Api\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class ApiDocs extends Component
{
    public function render()
    {
        return view('api::livewire.api-docs');
    }
}
