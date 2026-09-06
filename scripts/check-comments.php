<?php

$files = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$failures = [];

foreach ($files as $file) {
    if (! str_ends_with($file, '.php') || str_ends_with($file, '.blade.php')) {
        continue;
    }

    foreach (token_get_all(file_get_contents($file)) as $token) {
        if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT]) && str_contains($token[1], "\n")) {
            $failures[] = $file.':'.$token[2];
        }
    }
}

echo json_encode($failures, JSON_THROW_ON_ERROR);
