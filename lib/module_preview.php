<?php
class module_preview extends rex_article_content_editor
{
    private $modules = 0;

    public function getModules(): string {
        $hideImages = \rex_config::get('module_preview', 'hide_images');
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
            $moduleList .= '<ul class="module-list '.$additionalModuleListClass.'">';

            if(self::hasClipboardContents()) {
                $clipBoardContents = self::getClipboardContents();
                $sliceDetails = $this->getSliceDetails($clipBoardContents['slice_id'], $clipBoardContents['clang']);
                $context->setParam('source_slice_id', $clipBoardContents['slice_id']);

                if($sliceDetails['article_id']) {
                    $moduleList .= '<li class="column large">';
                        $moduleList .= '<a href="'.$context->getUrl(['module_id' => $sliceDetails['module_id']]).'" data-href="'.$context->getUrl(['module_id' => $sliceDetails['module_id']]).'" class="module" data-name="'.$sliceDetails['module_id'].'.jpg">';
                            $moduleList .= '<div class="header">';
                                if($clipBoardContents['action'] === 'copy') {
                                    $moduleList .= '<i class="fa fa-clipboard" aria-hidden="true" style="margin-right: 5px;"></i>';
                                }
                                elseif($clipBoardContents['action'] === 'cut') {
                                    $moduleList .= '<i class="fa fa-scissors" aria-hidden="true" style="margin-right: 5px;"></i>';
                                }
                                $moduleList .= '<span>'.rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $clipBoardContents['slice_id'], rex_article::get($sliceDetails['article_id'])->getName()).'</span>';
                            $moduleList .= '</div>';
                        $moduleList .= '</a>';
                    $moduleList .= '</li>';
                }
            }

            $context->setParam('source_slice_id', '');
            $this->modules = [];
            foreach ($templateCtypes as $ctId => $ctName) {
                foreach ($modules as $m) {

                    if (rex::getUser()->getComplexPerm('modules')->hasPerm($m['id'])) {
                        if (rex_template::hasModule($this->template_attributes, $ctId, $m['id'])) {
                            $moduleList .= '<li class="column">';
                            $moduleList .= '<a href="'.$context->getUrl(['module_id' => $m['id']]).'" data-href="'.$context->getUrl(['module_id' => $m['id']]).'" class="module" data-name="'.$m['id'].'.jpg">';
                            $moduleList .= '<div class="header">'.rex_i18n::translate($m['name'], false).'</div>';
                            if(!$hideImages) {
                                $image = rex_url::assets('addons/module_preview_modules/'.$m['id'].'.jpg');

                                if(array_key_exists('key', $m) && isset($m['key'])) {
                                    $image = rex_url::assets('addons/module_preview_modules/'.$m['key'].'.jpg');
                                }

                                $moduleList .= '<div class="image"><div>';
                                if(file_exists($image)) {
                                    $moduleList .= '<img src="'.$image.'" alt="'.rex_i18n::translate($m['name'], false).'">';
                                }
                                else {
                                    $moduleList .= '<div class="not-available"></div>';
                                }
                                $moduleList .= '</div></div>';
                            }
                            $moduleList .= '</a>';
                            $moduleList .= '</li>';

                            $slug = rex_string::normalize(rex_i18n::translate($m['name'], false), '-');
                            $this->modules[] = [
                                'name' => rex_i18n::translate($m['name'], false),
                                'slug' => $slug,
                                'id' => $m['id'],
                                'key' => $m['key'],
                                'imagePath' => rex_addon::get('module_preview')->getAssetsPath($slug.'.jpg'),
                            ];
                        }
                    }
                }
            }
            $moduleList .= '</ul>';
        $moduleList .= '</div>';

        return $moduleList;
    }

    public function getSearch(): string {
        $addon = rex_addon::get('module_preview');
        $search = '<div class="container"><div class="form-group">';
            $search .= '<label class="control-label" for="module-preview-search"><input class="form-control" name="module-preview-search" type="text" id="module-preview-search" value="" placeholder="'.$addon->i18n('module_preview_search_modules').'" /></label>';
        $search .= '</div></div>';
        return $search;
    }

    public static function hasClipboardContents(): bool {
        $cookie = self::getClipboardContents();

        if($cookie) {
            return true;
        }

        return false;
    }

    public static function getClipboardContents() {
        return @json_decode(rex_request::cookie('rex_bloecks_cutncopy', 'string', ''), true);
    }

    private function getSliceDetails($sliceId, $clangId) {
        if($sliceId && $clangId) {
            $sql = rex_sql::factory();
            $sql->setQuery('select ' . rex::getTablePrefix() . 'article_slice.article_id, ' . rex::getTablePrefix() . 'article_slice.module_id, ' . rex::getTablePrefix() . 'module.name from ' . rex::getTablePrefix() . 'article_slice left join ' . rex::getTablePrefix() . 'module on ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id where ' . rex::getTablePrefix() . 'article_slice.id=? and ' . rex::getTablePrefix() . 'article_slice.clang_id=?', [$sliceId, $clangId]);
            return $sql->getArray()[0];
        }
    }
}
