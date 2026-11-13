<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Emoji;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EditorPanels extends Component
{
    /** @var array<string, array{string, string, array<string, string>}> */
    #[Locked]
    public array $slash = [];

    #[Locked]
    public bool $emoji = false;

    #[Locked]
    public bool $counts = false;

    #[Locked]
    public ?int $maxLength = null;

    #[Locked]
    public bool $uploads = false;

    #[Locked]
    public bool $images = false;

    #[Locked]
    public bool $find = false;

    #[Locked]
    public bool $links = false;

    #[Locked]
    public string $query = '';

    #[Locked]
    public string $menu = '';

    /** @var array<string, mixed> */
    #[Locked]
    public array $status = [];

    public function mount(int $characters = 0): void
    {
        $this->status = ['characters' => max(0, $characters)];
    }

    public function search(string $menu, string $query): void
    {
        $this->menu = in_array($menu, ['slash', 'emoji'], true) ? $menu : '';
        $this->query = mb_strtolower(mb_substr($query, 0, 80));
    }

    /** @param array<string, mixed> $status */
    public function syncStatus(array $status): void
    {
        $this->status = [
            'characters' => max(0, (int) ($status['characters'] ?? 0)),
            'uploads' => max(0, (int) ($status['uploads'] ?? 0)),
            'refused' => ($status['refused'] ?? false) === true,
            'unsaved' => max(0, (int) ($status['unsaved'] ?? 0)),
            'saving' => ($status['saving'] ?? false) === true,
            'matches' => max(0, (int) ($status['matches'] ?? 0)),
            'match' => max(0, (int) ($status['match'] ?? 0)),
            'searching' => ($status['searching'] ?? false) === true,
            'mathError' => $this->shortText($status['mathError'] ?? null),
            'imageError' => $this->shortText($status['imageError'] ?? null),
            'link' => $this->linkUrl($status['link'] ?? null),
            'uploadProgress' => array_map(
                fn (mixed $percent): int => max(0, min(100, (int) $percent)),
                is_array($status['uploadProgress'] ?? null) ? array_slice($status['uploadProgress'], 0, 8) : [],
            ),
            'uploadErrors' => array_values(array_filter(array_map(
                $this->shortText(...),
                is_array($status['uploadErrors'] ?? null) ? array_slice($status['uploadErrors'], -8) : [],
            ))),
        ];
    }

    private function shortText(mixed $message): string
    {
        return is_string($message) ? mb_substr($message, 0, 400) : '';
    }

    private function linkUrl(mixed $value): string
    {
        $url = is_string($value) ? trim($value) : '';
        if (preg_match('/[\x00-\x1f\x7f]/', $url)
            || (preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $scheme) && ! in_array(strtolower($scheme[1]), ['http', 'https', 'mailto', 'tel'], true))) {
            return '';
        }

        return $url;
    }

    public function render(): View
    {
        $slashItems = $this->menu === 'slash'
            ? array_filter($this->slash, fn (array $item, string $key) => str_contains(mb_strtolower($item[1].' '.$key), $this->query), ARRAY_FILTER_USE_BOTH)
            : [];
        $emojiItems = [];
        if ($this->menu === 'emoji' && $this->emoji && $this->query !== '') {
            foreach (Emoji::catalog() as $category) {
                foreach ($category as [$emoji, $label, $keywords]) {
                    $score = Emoji::score($label, $keywords, $this->query);
                    if ($score > 0) {
                        $emojiItems[] = [$emoji, $label, $score];
                    }
                }
            }
            usort($emojiItems, fn (array $a, array $b) => $b[2] <=> $a[2]);
            $emojiItems = array_slice($emojiItems, 0, 8);
        }

        return view('ui::livewire.editor-panels', compact('slashItems', 'emojiItems'));
    }
}
