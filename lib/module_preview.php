<?php
class module_preview extends rex_article_content_editor
{
    private $modules = 0;

    public function getModules() {
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

        $moduleList = '<div class="container">';
            $moduleList .= '<ul class="module-list">';
            $this->modules = [];
            foreach ($templateCtypes as $ctId => $ctName) {
                foreach ($modules as $m) {

                    if (rex::getUser()->getComplexPerm('modules')->hasPerm($m['id'])) {
                        if (rex_template::hasModule($this->template_attributes, $ctId, $m['id'])) {
                            $image = rex_url::assets('addons/module_preview_modules/'.$m['id'].'.jpg');
                            $moduleList .= '<li class="column">';
                                $moduleList .= '<a href="'.$context->getUrl(['module_id' => $m['id']]).'" data-href="'.$context->getUrl(['module_id' => $m['id']]).'" class="module" data-name="'.$m['id'].'.jpg">';
                                    $moduleList .= '<div class="header">'.rex_i18n::translate($m['name'], false).'</div>';
                                    $moduleList .= '<div class="image"><div>';
                                        if(file_exists($image)) {
                                            $moduleList .= '<img src="'.$image.'" alt="'.rex_i18n::translate($m['name'], false).'">';
                                        }
                                        else {
                                            $moduleList .= '<div class="not-available"></div>';
                                        }
                                    $moduleList .= '</div></div>';
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
}
