<?php
$useClassic = \rex_config::get('module_preview', 'classic');

if (rex::isBackend() && ('index.php?page=module_preview/modules' == rex_url::currentBackendPage() && rex::getUser())) {
    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addJSFile($this->getAssetsUrl('js/modules.min.js'));
}

if (rex::isBackend() && ('index.php?page=content/edit' == rex_url::currentBackendPage() && rex::getUser() && !$useClassic) ) {
    rex_view::addJSFile($this->getAssetsUrl('js/script.min.js'));
    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));

    $bloecksDragIsInstalled = false;
    if(rex_addon::exists('bloecks')) {
        $addons = rex_addon::getInstalledAddons();

        if(isset($addons['bloecks'])) {
            $bloecksDragIsInstalled = $addons['bloecks']->getPlugin('dragndrop')->isAvailable();
        }
    }

    rex_extension::register('STRUCTURE_CONTENT_MODULE_SELECT', function (rex_extension_point $ep) use ($bloecksDragIsInstalled) {
        $html = '<div class="btn-block '.($bloecksDragIsInstalled && $ep->getParam('slice_id') !== -1 ? 'bloecks' : '').'">';
            $html .= '<button class="btn btn-default btn-block show-module-preview" type="button" data-slice="'.$ep->getParam('slice_id').'">';
                $html .= '<strong>Block hinzufügen</strong> ';
                $html .= '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
            $html .= '</button>';
        $html .= '</div>';

        $ep->setSubject($html);
    });

    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $hideSearch = \rex_config::get('module_preview', 'hide_search');
        $modulePreview = new module_preview();
        $output = '<div id="module-preview" data-pjax-container="#rex-js-page-main-content"><div class="close"><span aria-hidden="true">&times;</span></div>';
        if (!$hideSearch) {
            $output .= $modulePreview->getSearch();
        }
        $output .= $modulePreview->getModules();
        $output .= '</div>';

        if ($output) {
            $ep->setSubject(str_ireplace(
                    ['</body>'],
                    [$output . '</body>'],
                    $ep->getSubject())
            );
        }
    });
}
