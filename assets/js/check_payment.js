(function($){
  $(document).ready(function() {
    $('#ksher-check-payment').on('click', function(e) {
      $.ajax({
        url: ul.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          'action': 'ksher_check_payment',
        },
        beforeSend: function() {
          $('.ksher-wrapper').html(
            '<div class="lds-hourglass"></div>'
          );
        },
        success: function(response) {
          console.log(response.query);
          $('.ksher-wrapper').html(response.data);
        },
        error: function(response) {
          alert('Error occured', response);
          $('#ksher-check-result').html('error');
        }
      });
    });
  });
})(jQuery);