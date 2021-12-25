<?php
    use \Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * hide modules if redaxo version < 5.12.0 -> symfony/http-foundation
     * https://github.com/redaxo/redaxo/releases/tag/5.12.0
     */
    if(rex_version::compare(rex::getVersion(),'5.12.0', '<')) {
        $warning = '<div class="alert alert-warning" style="margin-bottom: 0" role="alert"><strong>'.$this->i18n('version_warning').'</strong></div>';
        $fragment = new rex_fragment();
        $fragment->setVar('body', $warning, false);
        $content = $fragment->parse('core/page/section.php');
        echo $content;
    }
    else {
        /** @var rex_addon $this */
        $maxFilesToUpload = (int)(ini_get('max_file_uploads'));

        if(!empty(rex_post('module_upload'))) {
            $targetDir = rex_url::assets('addons/module_preview_modules/');
            $module = rex_sql::factory();
            $moduleIds = $module->getArray('select id from ' . rex::getTablePrefix() . 'module order by name');
            $maxFileSize = (int)(ini_get('upload_max_filesize'));
            $imageCount = 1;
            $error = false;

            foreach ($moduleIds as $moduleId) {
                $tmpImage = rex_files('module_'.$moduleId['id']);

                if(!$tmpImage || !$tmpImage['tmp_name'] && $tmpImage['error'] !== 0) {
                    continue;
                }

                if($imageCount > $maxFilesToUpload) {
                    echo rex_view::error($this->i18n('module_preview_upload_max_file_uploads', $maxFilesToUpload));
                    $error = true;
                    break;
                }

                if($tmpImage['size'] > UploadedFile::getMaxFilesize()) {
                    echo rex_view::error($this->i18n('module_preview_upload_max_filesize'));
                    continue;
                }

                $uploadedImage = new UploadedFile($tmpImage['tmp_name'], $tmpImage['name'], $tmpImage['type'], $tmpImage['error']);
                $uploadedImage->move($targetDir, $moduleId['id'].'.jpg');
                $imageCount++;
            }

            if(!$error) {
                echo rex_view::success($this->i18n('saved'));
            }
        }

        if(!empty(rex_post('delete_image'))) {
            ob_end_clean();
            $image = rex_post('image');
            if(rex_post('image') && file_exists($image)) {
                if(rex_file::delete($image)) {
                    http_response_code(200);
                }
            }
            else {
                echo $this->i18n('module_preview_image_not_found');
                http_response_code(404);
            }
            exit();
        }

        $maxFilesToUpload = (int)(ini_get('max_file_uploads'));

        $content = '<fieldset>';

        $formElements = [];

        $module = rex_sql::factory();
        $modules = $module->getArray('select * from ' . rex::getTablePrefix() . 'module order by name');

        $content .= '<div class="container-fluid module-container">';
            $content .= '<p class="help-block rex-note">'.$this->i18n('module_preview_upload_max_file_uploads', $maxFilesToUpload).'</p>';
            $content .= '<form action="'.rex_url::currentBackendPage().'" method="POST" enctype="multipart/form-data" class="module-row">';
                foreach ($modules as $module)
                {
                    $image = rex_url::assets('addons/module_preview_modules/'.$module['id'].'.jpg');
                    $content .= '<div class="module-col">';
                        $content .= '<div class="name"><strong>'.$module['name'].'</strong> <span>['.$module['id'].']</span></div>';
                        $content .= '<div class="module rex-form-group form-group">';
                            $content .= '<div class="image">';
                                if(file_exists($image)) {
                                    $content .= '<button class="delete-image" data-image="'.$image.'" value="delete_image"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                                    $content .= '<img src="'.$image.'" id="img-module-'.$module['id'].'" alt="'.rex_i18n::translate($module['name'], false).'" class="img-responsive">';
                                }
                                else {
                                    $content .= '<img src="'.rex_url::assets('addons/module_preview/na.png').'" id="img-module-'.$module['id'].'" alt="Not available" class="img-responsive n-a">';
                                }
                            $content .= '</div>';
                    $content .= '<div class="file">';
                    $content .= '<label class="form-label file-label">';
                    $content .= '<input type="file" id="module-'.$module['id'].'" class="module-image-input" name="module_'.$module['id'].'" accept="image/jpeg">';
                    $content .= '<span class="btn btn-default">'.$this->i18n('select_image').'</span>';
                    $content .= '</label>';
                    $content .= '</div>';
                        $content .= '</div>';
                    $content .= '</div>';
                }
            $content .= '<input type="hidden" name="random" id="random" value="'.microtime().'" />';
            $content .= '<div class="col-sm-12"><input type="submit" name="module_upload" value="'.$this->i18n('module_preview_save').'" class="btn btn-save"></div>';
            $content .= '</form>';
        $content .= '</div>';

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit');
        $fragment->setVar('title', $this->i18n('settings'));
        $fragment->setVar('body', $content, false);
        $content = $fragment->parse('core/page/section.php');

        echo $content;
    }
?>

    <script>
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
    </script>
