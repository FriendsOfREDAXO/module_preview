<?php

class module_preview extends rex_article_content_editor
{
    /** @var array */
    private $modules = [];

    /**
     * Get Modules from database and build HTML snippet.
     * @throws rex_sql_exception
     */
    public function getModules(): string
    {
        $hideImages = \rex_config::get('module_preview', 'hide_images', false);
        $loadImagesFromTheme = \rex_config::get('module_preview', 'load_images_from_theme', false);
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
            $clang,
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
                $moduleLink = $context->getUrl(['module_id' => $sliceDetails['module_id'], 'ctype' => $ctype]);
                $moduleList .= '<li class="column large">';
                $moduleList .= "<a href=\"{$moduleLink}\" data-href=\"{$moduleLink}\" class=\"module\" data-name=\"{$sliceDetails['module_id']}.jpg\">";
                $moduleList .= '<div class="header">';
                if ('copy' === $clipBoardContents['action']) {
                    $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true" style="margin-right: 5px;"></i>';
                } elseif ('cut' === $clipBoardContents['action']) {
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
        foreach ($modules as $module) {
            // Check if the user has permission to use the module
            if (true !== (rex::getUser()->getComplexPerm('modules')->hasPerm($module['id']) ?? false)) {
                continue;
            }
            foreach ($templateCtypes as $cTypeId => $cTypeName) {
                // Check if the template allows the usage of the module
                if (true !== rex_template::hasModule($this->template_attributes, $cTypeId, $module['id'])) {
                    continue;
                }
                $moduleList .= $this->moduleListItem($context, $module, $ctype, $hideImages, $loadImagesFromTheme);
            }
        }
        $moduleList .= '</ul>';
        $moduleList .= '</div>';

        return $moduleList;
    }

    public function getSearch(): string
    {
        $addon = rex_addon::get('module_preview');

        return <<<HTML
            <div class="container">
                <div class="form-group">
                    <label class="control-label" for="module-preview-search">
                        <input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="{$addon->i18n('module_preview_search_modules')}" />
                    </label>
                </div>
            </div>
            HTML;
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

    /**
     * Get the preview image for a module and, if set, the key of the module as a `span` HTML snippet.
     *
     * @return array{image: ?string, moduleKey: ?string}
     */
    public static function getModulePreviewImage(array $module): array
    {
        // If the module has a key set, use the key to identify the image. Else use the ID of the module
        if (empty($module['key'] ?? null)) {
            $key = null;
            $imageName = $module['id'];
        } else {
            $key = ' <span>[' . $module['key'] . ']</span>';
            $imageName = $module['key'];
        }

        // Search the preview directory for valid images
        $globPattern = rex_url::assets('addons/module_preview_modules/') . "{$imageName}.*";
        $foundImages = glob($globPattern);
        $validImages = false !== $foundImages ? preg_grep('/^.*(jpe?g|png)/', $foundImages) : [];

        return [
            'image' => (empty($validImages)) ? null : reset($validImages),
            'moduleKey' => $key,
        ];
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
     * Create a list item HTML snippet for module selection.
     *
     * @param rex_context $context             Current context
     * @param array       $moduleData          Module data
     * @param int         $ctype               Current content type
     * @param bool        $hideImages          Shall images be hidden
     * @param bool        $loadImagesFromTheme Are images loaded from theme
     */
    private function moduleListItem(rex_context $context, array $moduleData, int $ctype, bool $hideImages, bool $loadImagesFromTheme): string
    {
        $moduleUrl = $context->getUrl(['module_id' => $moduleData['id'], 'ctype' => $ctype]);
        $moduleName = rex_i18n::translate($moduleData['name'], false);
        $imagePreview = (true !== $hideImages) ? $this->previewImageContainer($loadImagesFromTheme, $moduleData) : '';

        $slug = rex_string::normalize(rex_i18n::translate($moduleData['name'], false), '-');
        $this->modules[] = [
            'name' => rex_i18n::translate($moduleData['name'], false),
            'slug' => $slug,
            'id' => $moduleData['id'],
            'key' => $moduleData['key'],
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
            ['image' => $image] = static::getModulePreviewImage($moduleData);
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
     * @param string $imageFile           absolute image file to convert
     * @param bool   $loadImagesFromTheme Are images loaded from theme?
     * @param string $moduleLabel         Localized label of the module the image is the preview for
     */
    private function imageFileToTag(?string $imageFile, bool $loadImagesFromTheme, string $moduleLabel): string
    {
        if (null === $imageFile || !file_exists($imageFile)) {
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
