<?php

$builtinJs = [
    /* BUILT-IN JAVASCRIPT */
    'md5.js',
    'dmsf/main.js',
    'dmsf/modal.js',
    'dmsf/notification.js',
    'dmsf/graphs.js',
    'dmsf/deepclone.js',
    'dmsf/isEqual.js',
    'accordion.js',
];
foreach ($builtinJs as $key => $value) {
    $builtinJs[$key] = "builtin/${value}";
}

$customJs = [
    /* CUSTOM JAVASCRIPT */
];

foreach ($customJs as $key => $value) {
    $customJs[$key] = "custom/${value}";
}

$javascriptFiles = array_merge($builtinJs, $customJs);
