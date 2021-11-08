<?php
$addon = rex_addon::get('module_preview');

if (!$addon->hasConfig()) {
    $addon->setConfig([
        'classic' => false,
    ]);
}

if (class_exists('rex_scss_compiler')) {
    $compiler = new rex_scss_compiler();
    $compiler->setRootDir(rex_path::addon('module_preview/scss'));
    $compiler->setScssFile([$addon->getPath('scss/styles.scss')]);
    $compiler->setCssFile($addon->getPath('assets/css/styles.css'));
    $compiler->compile();
}

rex_dir::create(rex_path::assets('addons/module_preview_modules'));
