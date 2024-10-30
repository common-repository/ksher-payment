(function($){
  $(document).ready(function() {


    $('.ksher-upload-btn').on('click', function() {
      $('#ksher-private-key-file').trigger('click');
    });

    $('#ksher-private-key-file').on('change', function() {
      var ksherPrivateKeyName = $('#ksher-private-key-file').prop('files')[0]['name'];
      $('#ksher-private-key-filename').html('Your filename is ' + ksherPrivateKeyName);
      $('#ksher-check-result').html('<p class="ksher-warning">Pls Click Save Change</p>')
    });

    $('#ksher-submit').on('click', function(e) {
      ksherAppID = $('#ksher_app_id').val();
      ksherMchcode = $('#ksher_mch_code').val();
      ksherColor = $('#ksher_color').val();
      ksherLogo = $('#ksher_logo').val();


      ksherPrivateKey = $('#ksher-private-key-file').prop('files')[0];
      ksherPrivateKeyUrl = '';
      form_data = new FormData();
      form_data.append('file', ksherPrivateKey);
      form_data.append('action', 'file_upload');

      hasErrorAppid = '0';
      hasErrorPrivatekey = '0';

      $.ajax({
        url: ul.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          'action': 'ksher_update_appid',
          'ksher_app_id': ksherAppID,
          'ksher_mch_code': ksherMchcode,
          'ksher_color': ksherColor,
          'ksher_logo': ksherLogo
        },
        beforeSend: function() {
          $('#ksher-message').html(
            '<div class="ksher-preload"><div class="lds-facebook"><div></div><div></div><div></div></div></div>'
          );
        },
        success: function() {
          hasErrorAppid = '0';
        },
        error: function(response) {
          alert('App ID Error occured', response);
          hasErrorAppid = '1';
        }
      });

      $.ajax({
        url: ul.ajaxurl,
        type: "POST",
        contentType: false,
        processData: false,
        data: form_data,
        beforeSend: function() {
          $('#ksher-message').html(
            '<div class="ksher-preload"><div class="lds-facebook"><div></div><div></div><div></div></div></div>'
          );
        },
        success: function (response) {
          ksherPrivateKeyUrl = response ? response : '0';
          if ( ksherPrivateKeyUrl !== '0' ) {
            setTimeout(function() {
              var filename = ksherPrivateKeyUrl.substring(ksherPrivateKeyUrl.lastIndexOf('/')+1);
              $('#ksher-private-key-filename').html('Your filename is ' + filename);
              $('#ksher-private-key-filename').removeClass('ksher-warning');
              $('#ksher-private-key-filename').addClass('ksher-success');
            }, 2000);
            hasErrorPrivatekey = '0'
          } else {
            setTimeout(function() {
              $('#ksher-private-key-filename').html('No file Upload(.pem only)');
              $('#ksher-private-key-filename').removeClass('ksher-success');
              $('#ksher-private-key-filename').addClass('ksher-warning');
            }, 2000);
            hasErrorPrivatekey = '1'
          }

          $.ajax({
            url: ul.ajaxurl,
            type: "POST",
            dataType: "json",
            data: {
              'action': 'ksher_check_connection',
              'ksher_app_id': ksherAppID,
              'ksher_private_key_url': ksherPrivateKeyUrl,
            },
            success: function(response) {
              setTimeout(function() {
                if (response.result === 'true') {
                  $('#ksher-check-result').html('<p class="ksher-success">You Website is Connected</p>');
                } else {
                  $('#ksher-check-result').html('<p class="ksher-fail">Not Connect</p>');
                }
              }, 1000);
            },
            error: function(response) {
              alert('Error occured', response);
              $('#ksher-check-result').html('<p class="ksher-fail">error</p>');
            }
          });
        },
        error: function(response) {
          alert('Upload file Error occured', response);
        }
      });

      setTimeout(function() {
        if ( (hasErrorPrivatekey == '0') && (hasErrorAppid == '0')) {
          $('#ksher-message').html(
            '<div id="message" class="updated"><p><strong>Save Complete</strong></p></div>'
          );
        } else if (hasErrorPrivatekey == '1') {
          $('#ksher-message').html(
            '<div id="message" class="notice notice-error"><p><strong>No file Upload(.pem only)</strong></p></div>'
          );
        } else if (hasErrorAppid == '1') {
          $('#ksher-message').html(
            '<div id="message" class="notice notice-error"><p><strong>Error AppID</strong></p></div>'
          );
        } else {
          $('#ksher-message').html(
            '<div id="message" class="notice notice-error"><p><strong>Error</strong></p></div>'
          );
        }
      }, 2100);      
    });


    $('.ksher-upload-image').on('click',function(e){
      e.preventDefault();
      var button = $(this),
      custom_uploader = wp.media({
        title: 'Insert image',
        library : {
          type : 'image'
        },
        button: {
          text: 'Use this image'
        },
        multiple: false
      }).on('select', function() {
        var attachment = custom_uploader.state().get('selection').first().toJSON();

        button.html('<img style="width:100px;" src="' + attachment.url + '">').next().val(attachment.id).next().show();
        $('#ksher_logo').val(attachment.url);
        $('.ksher-remove-image').show();
      }).open();
    });
  

    $('.ksher-remove-image').on('click',function(e) {
      e.preventDefault();
      var button = $(this);
      button.next().val('');
      button.hide().prev().html('Upload image');
    });

  });
})(jQuery);