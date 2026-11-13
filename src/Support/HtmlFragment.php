<?php

namespace Esoboy\Ui\Support;

use Dom\HTMLDocument;

final class HtmlFragment
{
    public static function parse(string $html): HTMLDocument
    {
        return HTMLDocument::createFromString('<!doctype html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>', LIBXML_NOERROR);
    }
}
