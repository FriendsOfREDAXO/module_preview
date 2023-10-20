<?php
/** @var rex_fragment $this */

$module = $this->getVar('module');

['image' => $image, 'moduleKey' => $key] = module_preview::getModulePreviewImage($module);
?>

<div class="module-col">
    <div class="name"><strong><?= $module['name'] ?></strong> <span>[<?= $module['id'] ?>]</span><?= $key ?? '' ?></div>
    <div class="module rex-form-group form-group">
        <div class="image">
            <?php if (null !== $image) : ?>
                <button class="delete-image" data-image="<?= $image ?>" value="delete_image">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
                <img src="<?= $image ?>" id="img-module-<?= $module['id'] ?>" alt="<?= rex_i18n::translate($module['name'], false) ?>" class="img-responsive">
            <?php else: ?>
                <img src="<?= rex_url::assets('addons/module_preview/na.png') ?>" id="img-module-<?= $module['id'] ?>" alt="Not available" class="img-responsive n-a">
            <?php endif ?>
        </div>
        <div class="file" style="margin-top: 5px;">
            <label class="form-label file-label">
                <input type="file" id="module-<?= $module['id'] ?>" class="module-image-input" name="module_<?= $module['id'] ?>" accept="image/jpeg,image/png">
                <span class="btn btn-default"><?= rex_i18n::msg('module_preview_select_image') ?></span>
            </label>
        </div>
    </div>
</div>
