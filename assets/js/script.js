let $showModulePreview, slicePosition, slice, $modules, $modulePreview, $close, $modulesSearch, $body, $html, previewActive = false, moduleAdded = false;

$(document).on('rex:ready', function () {
  $showModulePreview = $('button.show-module-preview');
  $modulePreview = $('#module-preview');
  $close = $modulePreview.find('.close');
  $body = $('body');
  $html = $('html');
  $modules = $modulePreview.find('a.module');
  $modulesSearch = $modulePreview.find('#module-preview-search');

  hideModulePreview();

  $modules.off('click');
  $showModulePreview.off('click');
  $close.off('click');

  $modules.on('click', function () {
    const $this = $(this);
    moduleAdded = true;
    // eslint-disable-next-line no-undef
    const regex = new RegExp('\\bpage=' + rex.page + '(\\b[^/]|$)');
    if (regex.test($this.attr('href'))) {
      // event.preventDefault();
      hideModulePreview();
    }
  });

  $showModulePreview.on('click', function (event) {
    event.preventDefault();
    slicePosition = $(this).parents('li').attr('id');
    slice = $(this).data('slice');

    if(previewActive) {
      hideModulePreview();
    }
    else {
      showModulePreview();
    }
  });

  $modulePreview.on('click', function (event) {
    const $target = $(event.target);
    if($target.hasClass('module-list') || $target.attr('id') === 'module-preview') {
      event.preventDefault();
      event.stopPropagation();
      hideModulePreview();
    }
  });

  $close.on('click', function (event) {
    event.preventDefault();
    hideModulePreview();
  });

  /**
   * contains case insensitive...
   * https://stackoverflow.com/a/8747204
   */
  jQuery.expr[':'].icontains = function (a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
  };

  $modulesSearch.on('keyup', function () {
    const value = $modulesSearch.val();
    if (value) {
      $modules.parent().hide();
      $modules.filter(':icontains(' + value + ')').parent().show();
    }
    else {
      $modules.parent().show();
    }
  });

  /**
   * trap tabbable elements
   */
  const $tabbableElements = $modulePreview.find('select, input, textarea, button, a');
  const $firstTabbableElement = $tabbableElements.first();
  const $lastTabbableElement = $tabbableElements.last();

  $lastTabbableElement.on('keydown', function (e) {
    if ((e.which === 9 && !e.shiftKey) && previewActive) {
      e.preventDefault();
      $firstTabbableElement.focus();
    }
  });

  $firstTabbableElement.on('keydown', function (e) {
    if ((e.which === 9 && e.shiftKey) && previewActive) {
      e.preventDefault();
      $lastTabbableElement.focus();
    }
  });
});

$(document).on('keyup', function (event) {
  if (event.key === 'Escape') hideModulePreview();
});

function showModulePreview() {
  previewActive = true;
  $modulePreview.fadeIn();
  $body.addClass('module-preview');
  $body.css('height', 'auto');
  $html.css('overflow', 'hidden');
  $body.addClass('modal-open');
  $modulesSearch.val('');
  $modules.parent().show();

  if ($modulesSearch.length) {
    $modulesSearch.focus();
  }

  for (let i = 0; i < $modules.length; i++) {
    const href = $modules.eq(i).data('href');
    $modules.eq(i).attr('href', href + '&slice_id='+slice+'#'+slicePosition);
  }
}

function hideModulePreview() {
  $modulePreview.fadeOut(function () {
    previewActive = false;
    $body.removeClass('module-preview');
    $body.css('height', '100%');
    $html.css('overflow', 'initial');
    $body.removeClass('modal-open');

    if(moduleAdded) {
      setTimeout(function () {
        if($('#REX_FORM').length) {
          $('html,body').scrollTop($('#REX_FORM').offset().top);
          moduleAdded = false;
        }
      }, 10)
    }
  });
}
