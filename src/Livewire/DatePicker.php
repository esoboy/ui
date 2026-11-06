<?php

namespace Esoboy\Ui\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use IntlDateFormatter;
use Livewire\Attributes\Locked;

class DatePicker extends Calendar
{
    /** @var array{from: ?int, to: ?int} */
    #[Locked]
    public array $shownHours = ['from' => null, 'to' => null];

    /** @return 'from'|'to'|null */
    private function timePart(string $part): ?string
    {
        if (($this->settings['disabled'] ?? false) || ! ($this->settings['time'] ?? false)) {
            return null;
        }

        return match ($part) {
            'from' => 'from',
            'to' => ($this->settings['mode'] ?? 'single') === 'range' ? 'to' : null,
            default => null,
        };
    }

    public function showTimeHour(string $part, int $hour): void
    {
        $part = $this->timePart($part);
        if ($part !== null && $hour >= 0 && $hour < 24) {
            $this->shownHours[$part] = $hour;
        }
    }

    protected function modelChanged(): void
    {
        parent::modelChanged();
        $this->shownHours = ['from' => null, 'to' => null];
    }

    public function pickTime(string $part, string $unit, int $option): void
    {
        $part = $this->timePart($part);
        if ($part === null) {
            return;
        }

        $time = $this->timeOptions()->choose($this->times[$part], $unit, $option);
        if ($time === null) {
            return;
        }

        $this->times[$part] = $time;
        $this->shownHours[$part] = null;
        $mode = $this->settings['mode'] ?? 'single';
        if ($mode === 'range') {
            if (isset($this->value[$part])) {
                $this->value[$part] = $this->at(substr($this->value[$part], 0, 10), $part);
            }
        } elseif ($mode === 'multiple') {
            $this->value = array_map(fn ($date) => $this->at(substr($date, 0, 10), $part), (array) $this->value);
        } elseif ($this->value) {
            $this->value = $this->at(substr($this->value, 0, 10), $part);
        }
    }

    public function label(): string
    {
        $mode = $this->settings['mode'] ?? 'single';
        $format = function (?string $value) use ($mode): string {
            if (! $value) {
                return '';
            }
            if (! ($this->settings['time'] ?? false)) {
                return $this->format($value, $mode === 'single' ? 'yMMMMd' : 'yMMMdd');
            }
            $formatter = new IntlDateFormatter(app()->getLocale(), $mode === 'single' ? IntlDateFormatter::LONG : IntlDateFormatter::MEDIUM, $this->timeOptions()->withSeconds ? IntlDateFormatter::MEDIUM : IntlDateFormatter::SHORT, config('app.timezone'));

            return str_replace("\u{202F}", ' ', $formatter->format(CarbonImmutable::parse($value)) ?: $value);
        };

        return is_array($this->value)
            ? implode($mode === 'range' ? ' - ' : ', ', array_map($format, array_values(array_filter($this->value))))
            : $format($this->value);
    }

    public function render(): View
    {
        return view('ui::livewire.date-picker', ['months' => $this->months(), 'label' => $this->label(), 'timeOptions' => $this->timeOptions()]);
    }
}
