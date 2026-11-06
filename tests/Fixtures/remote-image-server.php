<?php

if ($_SERVER['REQUEST_URI'] === '/redirect') {
    header('Location: /cat.png');

    return;
}

if ($_SERVER['REQUEST_URI'] === '/inside') {
    header('Location: http://127.0.0.1:'.$_SERVER['SERVER_PORT'].'/cat.png');

    return;
}

if ($_SERVER['REQUEST_URI'] === '/large') {
    header('Content-Length: 60000000');
}

header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
