<?php

/** @var rex_addon $this */

if (rex_post('config-submit', 'boolean')) {
    $this->setConfig(rex_post('config', [
        ['classic', 'bool'],
        ['hide_search', 'bool'],
        ['hide_images', 'bool'],
        ['load_images_from_theme', 'bool'],
    ]));

    echo rex_view::success($this->i18n('saved'));
}

$content = '<fieldset>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-module-preview-classic">' . $this->i18n('classic') . '</label>';
$n['field'] = '<input type="checkbox" id="rex-module-preview-classic" name="config[classic]" value="1" ' . ($this->getConfig('classic') ? ' checked="checked"' : '') . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-module-preview-hide-search">' . $this->i18n('hide_search') . '</label>';
$n['field'] = '<input type="checkbox" id="rex-module-preview-hide-search" name="config[hide_search]" value="1" ' . ($this->getConfig('hide_search') ? ' checked="checked"' : '') . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-module-preview-hide-images">' . $this->i18n('hide_images') . '</label>';
$n['field'] = '<input type="checkbox" id="rex-module-preview-hide-images" name="config[hide_images]" value="1" ' . ($this->getConfig('hide_images') ? ' checked="checked"' : '') . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$formElements = [];

if (rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
    $n = [];
    $n['label'] = '<label for="rex-module-load-images-from-theme">' . $this->i18n('load_images_from_theme') . '</label>';
    $n['field'] = '<input type="checkbox" id="rex-module-load-images-from-theme" name="config[load_images_from_theme]" value="1" ' . ($this->getConfig('load_images_from_theme') ? ' checked="checked"' : '') . ' />';
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="config-submit" value="1" ' . rex::getAccesskey($this->i18n('save'), 'save') . '>' . $this->i18n('save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('settings'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';
