<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | <x-ui::editor>: the formats it can be given besides text, basic and full,
    | what its whitelist keeps besides its own, and the video platforms it embeds.
    |
    */

    'editor' => [

        // Named formats, for format="…", Ui::sanitize() and Ui::plainText(): features
        // and presets, and the whitelist's tags kept besides theirs.
        // 'comment' => ['features' => ['bold', 'italic', 'link', 'emoji']],
        // 'notes' => ['features' => ['text', 'image'], 'tags' => ['section']],
        'formats' => [],

        // Added to the whitelist, for every format that keeps the tag: tags, their
        // attributes and the values kept ('*' for all; for style, CSS properties).
        // 'img' => ['data-ratio' => '*'], 'span' => ['style' => ['transform']], 'section' => '*',
        'whitelist' => [],

        // Embed platforms besides the built-in ones (YouTube, Bilibili, Vimeo, Tencent
        // Video, Youku, AcFun, Dailymotion, iQiyi). src is its player's address: embed
        // code plays when its player is on that host. pattern (a JavaScript regular
        // expression, optional) is its share links', which become src with $1 their
        // first capture. still (optional, autoplay=0) keeps it from playing by itself.
        // 'acme' => ['src' => 'https://player.acme.test/$1', 'pattern' => '^https://acme\.test/v/(\w+)', 'still' => 'autoplay=0'],
        'embed_platforms' => [],

    ],

];
