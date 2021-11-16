<?php
$addon = rex_addon::get('module_preview');

if (!$addon->hasConfig()) {
    $addon->setConfig([
        'classic' => false,
        'hide_search' => true,
        'hide_images' => false,
    ]);
}
