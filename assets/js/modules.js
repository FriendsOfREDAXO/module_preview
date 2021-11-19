$(document).on('rex:ready', function () {
  const $moduleImageInputs = document.querySelectorAll('input.module-image-input');
  const $form = $('form.module-row');
  const $modules = $('.module-col .module');
  const $deleteImage = $modules.find('button.delete-image');

  for (let i = 0; i < $moduleImageInputs.length; i++) {
    $moduleImageInputs[i].onchange = () => {
      const [file] = $moduleImageInputs[i].files;
      const $image = document.getElementById('img-'+$moduleImageInputs[i].id);
      if (file) {
        $image.src = URL.createObjectURL(file);
      }
    };
  }

  $deleteImage.on('click', function (event) {
    event.preventDefault();

    $('#rex-js-ajax-loader').addClass('rex-visible');
    $.post($form.attr('action'), {'delete_image':true, 'image':$(this).data('image')})
      .done(function() {
        location.reload();
      })
      .fail(function(response) {
        alert(response.responseText);
      })
      .always(function() {
        $('#rex-js-ajax-loader').removeClass('rex-visible');
      });
  });
});
