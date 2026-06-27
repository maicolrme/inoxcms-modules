<?php

namespace Inox\Api\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\File;

#[Layout('layouts.admin')]
class ApiSettings extends Component
{
    public string $authType = 'sanctum';

    public string $rateLimit = '60';

    public bool $logEnabled = true;

    public bool $envWritable = false;

    public function mount(): void
    {
        $this->authType = config('inox.api.auth_type', 'sanctum');
        $this->rateLimit = (string) config('inox.api.rate_limit', 60);
        $this->logEnabled = config('inox.api.log_enabled', true);
        $this->envWritable = File::isWritable(cms_path('.env'));
    }

    public function save(): void
    {
        $this->validate([
            'authType' => 'required|in:sanctum,none',
            'rateLimit' => 'required|integer|min:1|max:10000',
        ]);

        if ($this->envWritable) {
            $this->writeEnv('API_AUTH_TYPE', $this->authType);
            $this->writeEnv('API_RATE_LIMIT', $this->rateLimit);
            $this->writeEnv('API_LOG_ENABLED', $this->logEnabled ? 'true' : 'false');
        }

        config(['inox.api.auth_type' => $this->authType]);
        config(['inox.api.rate_limit' => (int) $this->rateLimit]);
        config(['inox.api.log_enabled' => $this->logEnabled]);

        $this->dispatch('notify', message: 'API settings saved.');
    }

    protected function writeEnv(string $key, string $value): void
    {
        $envPath = cms_path('.env');
        if (!File::exists($envPath)) return;

        $env = File::get($envPath);
        $escaped = preg_quote($key, '/');

        if (preg_match("/^{$escaped}=/m", $env)) {
            $env = preg_replace("/^{$escaped}=.*/m", "{$key}={$value}", $env);
        } else {
            $env .= "\n{$key}={$value}";
        }

        File::put($envPath, $env);
    }

    public function render()
    {
        return view('api::livewire.api-settings');
    }
}
