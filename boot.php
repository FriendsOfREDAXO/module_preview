<?php
$useClassic = rex_config::get('module_preview', 'classic');

if (rex::isBackend() && ('index.php?page=content/edit' == rex_url::currentBackendPage() && rex::getUser() && !$useClassic) ){
    rex_view::addJSFile($this->getAssetsUrl('js/script.min.js'));
    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));

    rex_extension::register('STRUCTURE_CONTENT_MODULE_SELECT', function (rex_extension_point $ep) {
        $html = '<div class="btn-block '.(rex_addon::exists('bloecks') && $ep->getParam('slice_id') !== -1 ? 'bloecks' : '').'">';
            $html .= '<button class="btn btn-default btn-block show-module-preview" type="button" data-slice="'.$ep->getParam('slice_id').'">';
                $html .= '<strong>Block hinzufügen</strong> ';
                $html .= '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
            $html .= '</button>';
        $html .= '</div>';

        $ep->setSubject($html);
    });

    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) {
        $modulePreview = new module_preview();
        $output = '<div id="module-preview" data-pjax-container="#rex-js-page-main-content"><div class="close"><span aria-hidden="true">&times;</span></div>'.$modulePreview->getModules().'</div>';

        if ($output) {
            $ep->setSubject(str_ireplace(
                    ['</body>'],
                    [$output . '</body>'],
                    $ep->getSubject())
            );
        }
    });
}
