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
        $clang = rex_request('clang', 'int');
        $clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();
        $category_id = rex_request('category_id', 'int');
        $article_id = rex_request('article_id', 'int');

        $params = [
            'clang' => $clang,
            'category_id' => $category_id,
            'article_id' => $article_id,
            'buster' => time()
        ];

        $html = '<div class="btn-block '.($bloecksDragIsInstalled && $ep->getParam('slice_id') !== -1 ? 'bloecks' : '').'">';
            $html .= '<button class="btn btn-default btn-block show-module-preview" type="button" data-slice="'.$ep->getParam('slice_id').'" data-url="'.rex_url::currentBackendPage($params + rex_api_module_preview_get_modules::getUrlParams()).'">';
                $html .= '<strong>Block hinzufügen</strong> ';
                $html .= '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
            $html .= '</button>';
        $html .= '</div>';

        $ep->setSubject($html);
    });

    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $output = '<div id="module-preview" data-pjax-container="#rex-js-page-main-content"><div class="close"><span aria-hidden="true">&times;</span></div>';
            $output .= '<div class="inner"></div>';
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
