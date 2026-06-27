<?php

namespace Inox\Storage\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\File;

#[Layout('layouts.admin')]
class MediaSettings extends Component
{
    public string $disk = 'local';

    public string $s3_key = '';

    public string $s3_secret = '';

    public string $s3_region = 'us-east-1';

    public string $s3_bucket = '';

    public string $s3_endpoint = '';

    public string $r2_key = '';

    public string $r2_secret = '';

    public string $r2_region = 'auto';

    public string $r2_bucket = '';

    public string $r2_url = '';

    public string $r2_endpoint = '';

    public bool $envWritable = false;

    public function mount(): void
    {
        $this->disk = config('storage.disk', 'local');
        $this->s3_key = env('STORAGE_AWS_ACCESS_KEY_ID', '');
        $this->s3_secret = env('STORAGE_AWS_SECRET_ACCESS_KEY', '');
        $this->s3_region = env('STORAGE_AWS_DEFAULT_REGION', 'us-east-1');
        $this->s3_bucket = env('STORAGE_AWS_BUCKET', '');
        $this->s3_endpoint = env('STORAGE_AWS_ENDPOINT', '');
        $this->r2_key = env('STORAGE_R2_ACCESS_KEY_ID', '');
        $this->r2_secret = env('STORAGE_R2_SECRET_ACCESS_KEY', '');
        $this->r2_region = env('STORAGE_R2_REGION', 'auto');
        $this->r2_bucket = env('STORAGE_R2_BUCKET', '');
        $this->r2_url = env('STORAGE_R2_URL', '');
        $this->r2_endpoint = env('STORAGE_R2_ENDPOINT', '');
        $this->envWritable = File::isWritable(cms_path('.env'));
    }

    public function save(): void
    {
        $this->validate(['disk' => 'required|in:local,s3,r2']);

        if ($this->envWritable) {
            $this->writeEnv('STORAGE_DISK', $this->disk);

            if ($this->disk === 's3') {
                $this->writeEnv('STORAGE_AWS_ACCESS_KEY_ID', $this->s3_key);
                $this->writeEnv('STORAGE_AWS_SECRET_ACCESS_KEY', $this->s3_secret);
                $this->writeEnv('STORAGE_AWS_DEFAULT_REGION', $this->s3_region);
                $this->writeEnv('STORAGE_AWS_BUCKET', $this->s3_bucket);
                $this->writeEnv('STORAGE_AWS_ENDPOINT', $this->s3_endpoint);
            }

            if ($this->disk === 'r2') {
                $this->writeEnv('STORAGE_R2_ACCESS_KEY_ID', $this->r2_key);
                $this->writeEnv('STORAGE_R2_SECRET_ACCESS_KEY', $this->r2_secret);
                $this->writeEnv('STORAGE_R2_REGION', $this->r2_region);
                $this->writeEnv('STORAGE_R2_BUCKET', $this->r2_bucket);
                $this->writeEnv('STORAGE_R2_URL', $this->r2_url);
                $this->writeEnv('STORAGE_R2_ENDPOINT', $this->r2_endpoint);
            }
        }

        $env = cms_path('.env');
        if (File::exists($env)) {
            $content = File::get($env);
            if (preg_match('/^STORAGE_DISK=/m', $content)) {
                $content = preg_replace('/^STORAGE_DISK=.*/m', "STORAGE_DISK={$this->disk}", $content);
            } else {
                $content .= "\nSTORAGE_DISK={$this->disk}";
            }
            File::put($env, $content);
        }

        config(['storage.disk' => $this->disk]);

        session()->flash('message', 'Storage settings saved.');
    }

    protected function writeEnv(string $key, string $value): void
    {
        $envPath = cms_path('.env');
        if (! File::exists($envPath)) return;

        $env = File::get($envPath);

        if (str_contains($env, "$key=")) {
            $env = preg_replace("/^$key=.*/m", "$key=$value", $env);
        } else {
            $env .= "\n$key=$value";
        }

        File::put($envPath, $env);
    }

    public function render()
    {
        return view('storage::livewire.media-settings');
    }
}
