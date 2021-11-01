let $showModulePreview, slicePosition, slice, $modules, $modulePreview, $close,$body, $html, previewActive, moduleAdded = false;

$(document).on('rex:ready', function () {
  $showModulePreview = $('button.show-module-preview');
  $modulePreview = $('#module-preview');
  $close = $modulePreview.find('.close');
  $body = $('body');
  $html = $('html');
  $modules = $modulePreview.find('a.module');

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
});

$(document).on('keyup', function(event) {
  if (event.key === 'Escape') hideModulePreview();
});

function showModulePreview() {
  previewActive = true;
  $modulePreview.fadeIn();
  $body.addClass('module-preview');
  $body.css('height', 'auto');
  $html.css('overflow', 'hidden');
  $body.addClass('modal-open');

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
        $('html,body').scrollTop($('#REX_FORM').offset().top);
        moduleAdded = false;
      }, 10)
    }
  });
}
