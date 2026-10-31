<?php

use Symfony\Component\Yaml\Yaml;

/*
 * What Laravel Boost installs into apps from resources/boost: the guidelines and skills
 * it discovers there, and a component reference that has to stay true to the views.
 */

function boostPath(string $path = ''): string
{
    return realpath(__DIR__.'/../../resources/boost').($path === '' ? '' : '/'.$path);
}

/**
 * The skill directories, keyed by their name.
 *
 * @return array<string, string>
 */
function boostSkills(): array
{
    return collect(glob(boostPath('skills/*'), GLOB_ONLYDIR))
        ->mapWithKeys(fn (string $path) => [basename($path) => $path])
        ->all();
}

it('ships guidelines as Markdown, which Boost reads without rendering Blade', function () {
    $guidelines = glob(boostPath('guidelines/*'));

    expect($guidelines)->not->toBeEmpty()
        ->and(collect($guidelines)->every(fn (string $path) => str_ends_with($path, '.md')))->toBeTrue();
});

it('names every skill in its frontmatter as its directory, with a description', function (string $path) {
    $source = (string) file_get_contents("{$path}/SKILL.md");

    expect(preg_match('/\A---\R(.*?)\R---\R/s', $source, $matches))->toBe(1);

    $frontmatter = Yaml::parse($matches[1]);

    expect($frontmatter['name'])->toBe(basename($path))
        ->and($frontmatter['name'])->toMatch('/^[a-z0-9]+(-[a-z0-9]+)*$/')
        ->and(mb_strlen($frontmatter['name']))->toBeLessThanOrEqual(64)
        ->and($frontmatter['description'])->toBeString()->not->toBeEmpty()
        ->and(mb_strlen($frontmatter['description']))->toBeLessThanOrEqual(1024);
})->with(fn () => boostSkills());

it('links only files the skill has', function (string $path) {
    preg_match_all('/\]\((?!https?:)([^)#]+)\)/', (string) file_get_contents("{$path}/SKILL.md"), $matches);

    foreach ($matches[1] as $link) {
        expect("{$path}/{$link}")->toBeFile();
    }
})->with(fn () => boostSkills());

it('lists every component with exactly its parts in the skill reference', function () {
    $reference = (string) file_get_contents(boostPath('skills/esoboy-ui-development/references/components.md'));

    $listed = [];

    preg_match_all('/^- \*\*([a-z-]+)\*\*(.*)$/m', $reference, $lines, PREG_SET_ORDER);

    foreach ($lines as [, $name, $rest]) {
        $listed[$name] = preg_match('/Parts: ([a-z-]+(?:, [a-z-]+)*)\.$/', $rest, $parts) ? explode(', ', $parts[1]) : [];
        sort($listed[$name]);
    }

    // The Editor's parts build its own toolbar and menus, and Extras loads the extras' script.
    $shipped = collect(componentViews())
        ->keys()
        ->map(fn (string $view) => explode('/', substr($view, 0, -strlen('.blade.php'))))
        ->reject(fn (array $segments) => $segments[0] === 'extras' || ($segments[0] === 'editor' && $segments[1] !== 'index'))
        ->groupBy(fn (array $segments) => $segments[0])
        ->map(fn ($views) => $views->map(fn (array $segments) => $segments[1] ?? 'index')->reject(fn (string $part) => $part === 'index')->sort()->values()->all())
        ->sortKeys()
        ->all();

    ksort($listed);

    expect($listed)->toBe($shipped);
});
