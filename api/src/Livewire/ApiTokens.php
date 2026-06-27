<?php

namespace Inox\Api\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class ApiTokens extends Component
{
    public string $newTokenName = '';

    public string $newToken = '';

    public function createToken(): void
    {
        $this->validate(['newTokenName' => 'required|max:255']);

        $token = auth()->user()->createToken($this->newTokenName);

        $this->newToken = $token->plainTextToken;
        $this->newTokenName = '';
    }

    public function revokeToken(int $id): void
    {
        auth()->user()->tokens()->where('id', $id)->delete();
        session()->flash('message', 'Token revoked.');
    }

    public function render()
    {
        return view('api::livewire.api-tokens', [
            'tokens' => auth()->user()->tokens()->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
