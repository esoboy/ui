<?php

/*
 * Assets: dist/ files through the asset route, or their published copies while current.
 */

use Esoboy\Ui\Assets;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->dist = sys_get_temp_dir().'/esoboy-ui-dist-'.uniqid();
    $this->public = sys_get_temp_dir().'/esoboy-ui-public-'.uniqid();

    File::ensureDirectoryExists($this->dist.'/chunks');
    File::put($this->dist.'/manifest.json', json_encode(['ui.js' => 'aaaaaaaaaaaa', 'chunks/a.js' => 'bbbbbbbbbbbb']));
    File::put($this->dist.'/ui.js', 'window.ui = true');
    File::put($this->dist.'/ui.js.gz', 'gzip bytes');
    File::put($this->dist.'/ui.js.br', 'brotli bytes');
    File::put($this->dist.'/chunks/a.js', 'export default 1');
    File::put($this->dist.'/secret.php', '<?php');

    $this->app->instance(Assets::class, new Assets($this->dist));
    $this->app->usePublicPath($this->public);
});

afterEach(function () {
    File::deleteDirectory($this->dist);
    File::deleteDirectory($this->public);
});

it('versions urls and serves them from the asset route', function () {
    expect(app(Assets::class)->url('ui.js'))->toBe('http://localhost/esoboy-ui/ui.js?id=aaaaaaaaaaaa')
        ->and(app(Assets::class)->url('chunks/a.js'))->toBe('http://localhost/esoboy-ui/chunks/a.js?id=bbbbbbbbbbbb');
});

it('uses the published copy only while it matches the build', function () {
    File::ensureDirectoryExists($this->public.'/vendor/ui');
    File::copy($this->dist.'/manifest.json', $this->public.'/vendor/ui/manifest.json');

    expect((new Assets($this->dist))->url('ui.js'))->toBe('http://localhost/vendor/ui/ui.js?id=aaaaaaaaaaaa');

    File::put($this->public.'/vendor/ui/manifest.json', json_encode(['ui.js' => 'old']));

    expect((new Assets($this->dist))->url('ui.js'))->toBe('http://localhost/esoboy-ui/ui.js?id=aaaaaaaaaaaa');
});

it('fails loudly for a file the build did not produce', function () {
    app(Assets::class)->url('missing.js');
})->throws(InvalidArgumentException::class, '[missing.js] is not a built asset of esoboy/ui');

it('prints a deferred script that runs once across wire:navigate', function () {
    expect(Blade::render('@uiScripts'))
        ->toBe('<script src="http://localhost/esoboy-ui/ui.js?id=aaaaaaaaaaaa" defer data-navigate-once></script>')
        ->and(Blade::render("@uiScripts(['nonce' => 'abc'])"))
        ->toContain('defer data-navigate-once nonce="abc"></script>');
});

it('loads a heavy component module once per page through @assets', function () {
    $this->app->instance(Assets::class, new Assets(__DIR__.'/../Fixtures/dist'));

    Route::get('/blocks', fn () => Blade::render(<<<'BLADE'
        <html><head></head><body>
            <x-ui::code-block.content language="php" code="echo 1;" />
            <x-ui::code-block.content language="php" code="echo 2;" />
        </body></html>
        BLADE));

    $html = $this->get('/blocks')->getContent();

    expect(substr_count($html, '<script type="module" src="http://localhost/esoboy-ui/code-block.js?id=cdef01234567"></script>'))->toBe(1)
        ->and(strpos($html, 'code-block.js'))->toBeLessThan(strpos($html, '</head>'));
});

it('leaves heavy modules out of pages that do not need them', function () {
    $this->app->instance(Assets::class, new Assets(__DIR__.'/../Fixtures/dist'));

    Route::get('/plain', fn () => Blade::render('<html><head></head><body><x-ui::code-block.content code="echo 1;" /></body></html>'));

    $this->get('/plain')->assertDontSee('code-block.js', false);
});

it('serves the precompressed copy the browser accepts, cached for a year', function () {
    $this->get('/esoboy-ui/ui.js', ['Accept-Encoding' => 'gzip, deflate, br'])
        ->assertOk()
        ->assertHeader('Content-Type', 'text/javascript; charset=utf-8')
        ->assertHeader('Content-Encoding', 'br')
        ->assertHeader('Vary', 'Accept-Encoding')
        ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public')
        ->assertStreamedContent('brotli bytes');

    $this->get('/esoboy-ui/ui.js', ['Accept-Encoding' => 'gzip'])
        ->assertHeader('Content-Encoding', 'gzip')
        ->assertStreamedContent('gzip bytes');

    $this->get('/esoboy-ui/ui.js', ['Accept-Encoding' => 'identity'])
        ->assertHeaderMissing('Content-Encoding')
        ->assertStreamedContent('window.ui = true');

    $this->get('/esoboy-ui/chunks/a.js')->assertOk()->assertStreamedContent('export default 1');
});

it('answers 304 for a copy the browser already has', function () {
    $modified = $this->get('/esoboy-ui/ui.js')->headers->get('Last-Modified');

    $this->get('/esoboy-ui/ui.js', ['If-Modified-Since' => $modified])->assertStatus(304);
});

it('serves nothing the build did not list', function () {
    $this->get('/esoboy-ui/secret.php')->assertNotFound();
    $this->get('/esoboy-ui/../composer.json')->assertNotFound();
    $this->get('/esoboy-ui/manifest.json')->assertNotFound();
});
