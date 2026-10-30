<?php

namespace Esoboy\Ui;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use InvalidArgumentException;

/**
 * The package's built JavaScript in dist/, served by the asset route or, once published
 * (vendor:publish --tag=ui-assets), straight from public/vendor/ui.
 */
final class Assets
{
    /** @var array<string, string>|null */
    private ?array $manifest = null;

    private ?bool $published = null;

    public function __construct(private readonly string $path) {}

    /**
     * The versioned URL of a built file. A published copy is used only while it matches
     * this build, so an app never gets files older than its package.
     */
    public function url(string $file): string
    {
        $version = $this->manifest()[$file]
            ?? throw new InvalidArgumentException("[{$file}] is not a built asset of esoboy/ui; run `npm run build` in the package.");

        $url = $this->published() ? asset('vendor/ui/'.$file) : route('esoboy-ui.asset', ['file' => $file]);

        return $url.'?id='.$version;
    }

    /**
     * The path of a built file, or null for anything the build didn't produce.
     */
    public function path(string $file): ?string
    {
        return isset($this->manifest()[$file]) ? $this->path.'/'.$file : null;
    }

    /**
     * The script tag @uiScripts prints. Deferred, so it can go anywhere in the page and
     * still runs before Livewire starts.
     *
     * @param  array{nonce?: string}  $options
     */
    public function scripts(array $options = []): string
    {
        $nonce = $options['nonce'] ?? Vite::cspNonce();

        return '<script src="'.e($this->url('ui.js')).'" defer data-navigate-once'.($nonce ? ' nonce="'.e($nonce).'"' : '').'></script>';
    }

    /**
     * The script tag of a heavy component's module, for its view's @assets block.
     */
    public function module(string $file): string
    {
        $nonce = Vite::cspNonce();

        return '<script type="module" src="'.e($this->url($file)).'"'.($nonce ? ' nonce="'.e($nonce).'"' : '').'></script>';
    }

    /**
     * @return array<string, string>
     */
    private function manifest(): array
    {
        return $this->manifest ??= self::read($this->path.'/manifest.json') ?? [];
    }

    private function published(): bool
    {
        return $this->published ??= self::read(public_path('vendor/ui/manifest.json')) === $this->manifest();
    }

    /**
     * @return array<string, string>|null
     */
    private static function read(string $manifest): ?array
    {
        return is_file($manifest) ? File::json($manifest, JSON_THROW_ON_ERROR) : null;
    }
}
