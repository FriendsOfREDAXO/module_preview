let $showModulePreview, slicePosition, slice, $modules, $modulePreview, $close, $modulesSearch, $body, $html, previewActive = false, moduleAdded = false;

$(document).on('rex:ready', function () {
  $showModulePreview = $('button.show-module-preview');
  $modulePreview = $('#module-preview');
  $close = $modulePreview.find('.close');
  $body = $('body');
  $html = $('html');

  hideModulePreview();

  $showModulePreview.off('click');
  $close.off('click');

  $showModulePreview.on('click', function (event) {
    event.preventDefault();
    slicePosition = $(this).parents('li').attr('id');
    slice = $(this).data('slice');

    if (previewActive) {
      hideModulePreview();
    }
    else {
      showModulePreview();
    }
  });

  $modulePreview.on('click', function (event) {
    const $target = $(event.target);
    if ($target.hasClass('module-list') || $target.parent().hasClass('inner') || $target.attr('id') === 'module-preview') {
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
});

$(document).on('keyup', function (event) {
  if (event.key === 'Escape') hideModulePreview();
});

function showModulePreview() {
  $.ajax({
    url: $showModulePreview.eq(0).data('url'),
    beforeSend: function () {
    }
  })
  .done(function (html) {
    if(html) {
      $modulePreview.find('.inner').html(html);

      previewActive = true;
      $modulePreview.fadeIn();
      $body.addClass('module-preview');
      $body.css('height', 'auto');
      $html.css('overflow', 'hidden');
      $body.addClass('modal-open');
      $modules = $modulePreview.find('a.module');
      $modules.parent().show();

      for (let i = 0; i < $modules.length; i++) {
        const href = $modules.eq(i).data('href');
        $modules.eq(i).attr('href', href + '&slice_id=' + slice + '#' + slicePosition);
      }

      attachModuleEventHandler();
    }
  })
  .fail(function (jqXHR, textStatus, errorThrown) {
    console.error('script.js:89', '  ↴', '\n', jqXHR, textStatus, errorThrown);
  });
}

function attachModuleEventHandler() {
  $modulesSearch = $modulePreview.find('#module-preview-search');

  if ($modulesSearch.length) {
    $modulesSearch.focus();
  }

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
}

function hideModulePreview() {
  $modulePreview.fadeOut(function () {
    previewActive = false;
    $body.removeClass('module-preview');
    $body.css('height', '100%');
    $html.css('overflow', 'initial');
    $body.removeClass('modal-open');
    $modulePreview.find('.inner').empty();

    if (moduleAdded) {
      setTimeout(function () {
        if ($('#REX_FORM').length) {
          $('html,body').scrollTop($('#REX_FORM').offset().top);
          moduleAdded = false;
        }
      }, 10)
    }
  });
}
