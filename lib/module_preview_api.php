<?php

class rex_api_module_preview_get_modules extends rex_api_function
{
    public function execute()
    {
        $hideSearch = \rex_config::get('module_preview', 'hide_search');
        $modulePreview = new module_preview();
        $output = '';
        if (!$hideSearch) {
            $output .= $modulePreview->getSearch();
        }
        $output .= $modulePreview->getModules();

        header('Content-Type: text/html; charset=UTF-8');
        echo $output;
        exit();
    }
}
