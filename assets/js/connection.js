(function($){
  $(document).ready(function() {
    $('#ksher-check-connection').on('click', function(e) {
      $.ajax({
        url: ul.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          'action': 'ksher_check_connection',
        },
        beforeSend: function() {
          $('#ksher-check-result').html(
            '<div class="lds-hourglass"></div>'
          );
        },
        success: function(response) {
            setTimeout(function() {
              if (response.result === 'true') {
                $('#ksher-check-result').html('<p class="ksher-success">You Website is Connected</p>');
              } else {
                $('#ksher-check-result').html('<p class="ksher-fail">Not Connect</p>');
              }
            }, 2000);
        },
        error: function(response) {
          alert('Error occured', response);
          $('#ksher-check-result').html('error');
        }
      });
    });
  });
})(jQuery);