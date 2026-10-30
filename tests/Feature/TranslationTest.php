<?php

use Illuminate\Support\Facades\File;

/**
 * Every package string, as "group.key" => line, per locale.
 *
 * @return array<string, array<string, string>>
 */
function packageLocales(): array
{
    return collect(glob(__DIR__.'/../../lang/*', GLOB_ONLYDIR))
        ->mapWithKeys(fn (string $locale) => [basename($locale) => collect(glob($locale.'/*.php'))
            ->flatMap(fn (string $file) => collect(require $file)->mapWithKeys(fn (string $line, string $key) => [basename($file, '.php').'.'.$key => $line]))
            ->all()])
        ->all();
}

it('ships english and simplified chinese', function () {
    expect(array_keys(packageLocales()))->toContain('en', 'zh_CN');
});

it('keeps every locale in sync with english', function () {
    $locales = packageLocales();

    foreach ($locales as $locale => $lines) {
        expect(array_keys($lines))->toEqualCanonicalizing(array_keys($locales['en']), "Locale {$locale} is out of sync with en.");
    }
});

it('only references keys that exist, through the ui namespace', function () {
    $keys = array_keys(packageLocales()['en']);

    $usages = collect(componentViews())->flatMap(function (string $path, string $name) {
        preg_match_all('/__\(\s*[\'"]([^\'"]+)[\'"]/', file_get_contents($path), $matches);

        return array_map(fn (string $key) => [$name, $key], $matches[1]);
    });

    expect($usages->reject(fn (array $usage) => str_starts_with($usage[1], 'ui::'))->all())->toBe([])
        ->and($usages->reject(fn (array $usage) => in_array(substr($usage[1], 4), $keys, true))->all())->toBe([]);
});

it('resolves package strings per locale', function () {
    app()->setLocale('zh_CN');
    expect(__('ui::common.close'))->toBe('关闭');

    app()->setLocale('en');
    expect(__('ui::common.close'))->toBe('Close');
});

it('lets the app override strings in lang/vendor/ui', function () {
    $override = lang_path('vendor/ui/zh_CN/common.php');

    File::ensureDirectoryExists(dirname($override));
    File::put($override, "<?php\n\nreturn ['close' => '关掉'];\n");

    try {
        app()->setLocale('zh_CN');

        expect(__('ui::common.close'))->toBe('关掉')
            ->and(__('ui::common.remove'))->toBe('移除')
            ->and(__('ui::spinner.label'))->toBe('加载中');
    } finally {
        File::deleteDirectory(lang_path('vendor/ui'));
    }
});
