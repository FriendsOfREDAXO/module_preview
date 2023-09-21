<?php

class module_preview extends rex_article_content_editor
{
    private $modules = 0;

    public function getModules(): string
    {
        $hideImages = \rex_config::get('module_preview', 'hide_images');
        $loadImagesFromTheme = \rex_config::get('module_preview', 'load_images_from_theme');
        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_request('category_id', 'int');
        $clang = rex_request('clang', 'int');
        $ctype = rex_request('ctype', 'int');
        $article = rex_sql::factory();
        $article->setQuery('
            SELECT article.*, template.attributes as template_attributes
            FROM ' . rex::getTablePrefix() . 'article as article
            LEFT JOIN ' . rex::getTablePrefix() . 'template as template ON template.id = article.template_id
            WHERE article.id = ? AND clang_id = ?', [
            $articleId,
            $clang
        ]);

        $this->template_attributes = $article->getArrayValue('template_attributes');

        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $articleId,
            'clang' => $clang,
            'ctype' => $ctype,
            'category_id' => $categoryId,
            'function' => 'add',
        ]);

        $module = rex_sql::factory();
        $modules = $module->getArray('select * from ' . rex::getTablePrefix() . 'module order by name');

        $templateCtypes = $this->template_attributes['ctype'] ?? [];
        if (0 == count($templateCtypes)) {
            $templateCtypes = [1 => 'default'];
        }

        $additionalModuleListClass = $hideImages ? 'images-hidden' : '';
        $moduleList = '<div class="container">';
        $moduleList .= '<ul class="module-list ' . $additionalModuleListClass . '">';

        if (self::hasClipboardContents()) {
            $clipBoardContents = self::getClipboardContents();
            $sliceDetails = $this->getSliceDetails($clipBoardContents['slice_id'], $clipBoardContents['clang']);
            $context->setParam('source_slice_id', $clipBoardContents['slice_id']);

            if ($sliceDetails['article_id']) {
                $moduleList .= '<li class="column large">';
                $moduleList .= '<a href="' . $context->getUrl(['module_id' => $sliceDetails['module_id'], 'ctype' => $ctype]) . '" data-href="' . $context->getUrl(['module_id' => $sliceDetails['module_id'], 'ctype' => $ctype]) . '" class="module" data-name="' . $sliceDetails['module_id'] . '.jpg">';
                $moduleList .= '<div class="header">';
                if ($clipBoardContents['action'] === 'copy') {
                    $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true" style="margin-right: 5px;"></i>';
                } elseif ($clipBoardContents['action'] === 'cut') {
                    $moduleList .= '<i class="fa fa-scissors" aria-hidden="true" style="margin-right: 5px;"></i>';
                }
                $moduleList .= '<span>' . rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $clipBoardContents['slice_id'], rex_article::get($sliceDetails['article_id'])->getName()) . '</span>';
                $moduleList .= '</div>';
                $moduleList .= '</a>';
                $moduleList .= '</li>';
            }
        }

        $context->setParam('source_slice_id', '');
        $this->modules = [];

        // Build cards for all modules available to the user and the current template.
        foreach ($modules as $m) {
            // Check if the user has permission to use the module
            if (true !== (rex::getUser()->getComplexPerm('modules')->hasPerm($m['id']) ?? false)) {
                continue;
            }
            foreach ($templateCtypes as $cTypeId => $cTypeName) {
                // Check if the template allows the usage of the module
                if (true !== rex_template::hasModule($this->template_attributes, $cTypeId, $m['id'])) {
                    continue;
                }
                $moduleList .= $this->moduleListItem($context, $m, $ctype, $hideImages, $loadImagesFromTheme);
            }
        }
        $moduleList .= '</ul>';
        $moduleList .= '</div>';

        return $moduleList;
    }

    public function getSearch(): string
    {
        $addon = rex_addon::get('module_preview');
        $search = '<div class="container"><div class="form-group">';
        $search .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="' . $addon->i18n('module_preview_search_modules') . '" /></label>';
        $search .= '</div></div>';

        return $search;
    }

    public static function hasClipboardContents(): bool
    {
        $cookie = self::getClipboardContents();

        if ($cookie) {
            return true;
        }

        return false;
    }

    public static function getClipboardContents()
    {
        return @json_decode(rex_request::cookie('rex_bloecks_cutncopy', 'string', ''), true);
    }

    private function getSliceDetails($sliceId, $clangId)
    {
        if ($sliceId && $clangId) {
            $sql = rex_sql::factory();
            $sql->setQuery('select ' . rex::getTablePrefix() . 'article_slice.article_id, ' . rex::getTablePrefix() . 'article_slice.module_id, ' . rex::getTablePrefix() . 'module.name from ' . rex::getTablePrefix() . 'article_slice left join ' . rex::getTablePrefix() . 'module on ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id where ' . rex::getTablePrefix() . 'article_slice.id=? and ' . rex::getTablePrefix() . 'article_slice.clang_id=?', [$sliceId, $clangId]);
            return $sql->getArray()[0];
        }
    }

    /**
     * Create a list item HTML snippet for module selection
     *
     * @param rex_context $context             Current context
     * @param array       $moduleData          Module data
     * @param int         $ctype               Current content type
     * @param bool        $hideImages          Shall images be hidden
     * @param bool        $loadImagesFromTheme Are images loaded from theme
     *
     * @return string
     */
    private function moduleListItem(rex_context $context, array $moduleData, int $ctype, bool $hideImages, bool $loadImagesFromTheme): string
    {
        $moduleUrl = $context->getUrl(['module_id' => $moduleData['id'], 'ctype' => $ctype]);
        $moduleName = rex_i18n::translate($moduleData['name'], false);
        $imagePreview = (true !== $hideImages) ? $this->previewImageContainer($loadImagesFromTheme, $moduleData) : '';

        $slug = rex_string::normalize(rex_i18n::translate($moduleData['name'], false), '-');
        $this->modules[] = [
            'name'      => rex_i18n::translate($moduleData['name'], false),
            'slug'      => $slug,
            'id'        => $moduleData['id'],
            'key'       => $moduleData['key'],
            'imagePath' => rex_addon::get('module_preview')->getAssetsPath($slug . '.jpg'),
        ];

        return <<<HTML
            <li class="column">
                <a href="{$moduleUrl}" data-href="{$moduleUrl}" class="module" data-name="{$moduleData['id']}.jpg">
                    <div class="header">{$moduleName}</div>
                    {$imagePreview}
                </a>
            </li>
            HTML;
    }

    /**
     * Create and return the markup for a DIV element containing the preview image as base64 encoded data string.
     *
     * @param bool  $loadImagesFromTheme
     * @param array $moduleData
     *
     * @return string
     */
    private function previewImageContainer(bool $loadImagesFromTheme, array $moduleData): string
    {
        if ($loadImagesFromTheme && rex_addon::exists('theme') && rex_addon::get('theme')->isAvailable()) {
            $suffix = '';
            if (rex_config::get('developer', 'dir_suffix')) {
                $suffix = ' [' . $moduleData['id'] . ']';
            }
            $image = theme_path::base('/private/redaxo/modules/' . $moduleData['name'] . $suffix . '/module_preview.jpg');
        } else {
            $image = rex_url::assets('addons/module_preview_modules/' . $moduleData['id'] . '.jpg');

            if (array_key_exists('key', $moduleData) && isset($moduleData['key'])) {
                $image = rex_url::assets('addons/module_preview_modules/' . $moduleData['key'] . '.jpg');
            }
        }
        $preview = $this->imageFileToTag($image, $loadImagesFromTheme, rex_i18n::translate($moduleData['name'], false));

        return <<<HTML
            <div class="image">
                <div>
                    {$preview}
                </div>
            </div>
            HTML;
    }

    /**
     * Convert $imageFile to an IMG tag. If $loadImagesFromTheme is true, the src is set to the base64 encoded image
     * data string.
     * Returns a _not-available_ placeholder DIV tag if $imageFile does not exist.
     *
     * @param string $imageFile           Absolute image file to convert.
     * @param bool   $loadImagesFromTheme Are images loaded from theme?
     * @param string $moduleLabel         Localized label of the module the image is the preview for
     *
     * @return string
     */
    private function imageFileToTag(string $imageFile, bool $loadImagesFromTheme, string $moduleLabel): string
    {
        if (!file_exists($imageFile)) {
            return '<div class="not-available"></div>';
        }
        $image = $imageFile;
        if ($loadImagesFromTheme) {
            $data = file_get_contents($imageFile);
            $image = 'data:image/jpg;base64,' . base64_encode($data);
        }

        return "<img src=\"{$image}\" alt=\"{$moduleLabel}\">";
    }
}
