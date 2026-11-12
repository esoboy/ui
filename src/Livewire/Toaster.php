<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Toaster extends Component
{
    #[Locked]
    public int $limit = 3;

    #[Locked]
    public int $timeout = 5000;

    /** @var array<string, array{id: string, revision: int, message: string, description: string, type: string, duration: int, action: ?array<string, mixed>}> */
    #[Locked]
    public array $notifications = [];

    #[Locked]
    public int $revision = 0;

    /** @var array<string, mixed> */
    #[Locked]
    public array $viewportAttributes = [];

    #[Locked]
    public ?string $closeView = null;

    #[Locked]
    public ?string $actionView = null;

    /** @param array<string, mixed> $notification */
    public function show(array $notification): void
    {
        $message = is_string($notification['message'] ?? null) ? $notification['message'] : '';
        $description = is_string($notification['description'] ?? null) ? $notification['description'] : '';
        if ($message === '' && $description === '') {
            return;
        }

        $revision = ++$this->revision;
        $id = is_string($notification['id'] ?? null) && $notification['id'] !== ''
            ? $notification['id']
            : 'ui-toast-'.$revision;
        $type = $notification['type'] ?? 'default';
        $type = in_array($type, ['success', 'info', 'warning', 'error', 'loading'], true) ? $type : 'default';
        $duration = $notification['duration'] ?? ($type === 'loading' ? 0 : $this->timeout);
        $action = $notification['action'] ?? null;
        $action = is_array($action) && is_string($action['label'] ?? null) ? [
            'label' => $action['label'],
            'component' => is_string($action['component'] ?? null) ? $action['component'] : null,
            'method' => is_string($action['method'] ?? null) ? $action['method'] : null,
            'params' => is_array($action['params'] ?? null) ? array_values($action['params']) : [],
        ] : null;

        $this->notifications['id:'.$id] = [
            'id' => $id,
            'revision' => $revision,
            'message' => $message,
            'description' => $description,
            'type' => $type,
            'duration' => is_numeric($duration) ? max(0, (int) $duration) : $this->timeout,
            'action' => $action,
        ];

        // Keep room for the oldest items' exit transitions during a burst.
        $this->notifications = array_slice($this->notifications, -max(1, $this->limit * 2), preserve_keys: true);
    }

    public function dismiss(string $id, int $revision): void
    {
        $key = 'id:'.$id;
        if (($this->notifications[$key]['revision'] ?? null) === $revision) {
            unset($this->notifications[$key]);
        }
    }

    public function render(): View
    {
        return view('ui::livewire.toaster');
    }
}
