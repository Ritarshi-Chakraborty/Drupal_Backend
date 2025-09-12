(function ($, Drupal) {
  // Toggle the body field of the node.
  Drupal.behaviors.readMoreToggle = {
    attach: function (context, settings) {
      $('.field__body--toggle', context).each(function () {
        let $container = $(this);

        $container.find('.button--toggle').on('click', function () {
          $container.find('.field__body--long').toggle();
          $container.find('.field__body--short').toggle();
        });
      });
    }
  };

  // Like the post using ajax and update the count.
  Drupal.behaviors.like = {
  attach: function (context, settings) {
    $('.button__like', context).each(function () {
      let $button = $(this);
      let nodeId = $button.data('nid');
      let $wrapper = $button.closest('.field__like_dislike');

      $button.on('click', function () {
        $.ajax({
          url: '/blog/like/' + nodeId,
          method: 'POST',
          dataType: 'json',
          success: function (response) {
            if (response.status === 'nochange') {
              $wrapper.find('.message__like_dislike').text(response.message).css({
                'background-color': '#F3CF3C',
                'padding': '10px',
                'color': '#000'
              });
            }
            else if (response.status === 'success') {
              $wrapper.find('.message__like_dislike').text(response.message).css({
                'background-color': '#188FD4',
                'padding': '10px',
                'color': '#fff'
              });
              // ✅ Update counts inside this wrapper container
              $wrapper.find('.like_count').text(response.likes);
              $wrapper.find('.dislike_count').text(response.dislikes);
            }
          },
          error: function () {
            alert('There was an error liking the post.');
          }
        });
      });
    });
  }
};

// Dislike the post using ajax and update the count.
Drupal.behaviors.dislike = {
  attach: function (context, settings) {
    $('.button__dislike', context).each(function () {
      let $button = $(this);
      let nodeId = $button.data('nid');
      let $wrapper = $button.closest('.field__like_dislike');

      $button.on('click', function () {
        $.ajax({
          url: '/blog/dislike/' + nodeId,
          method: 'POST',
          dataType: 'json',
          success: function (response) {
            if (response.status === 'nochange') {
              $wrapper.find('.message__like_dislike').text(response.message).css({
                'background-color': '#F3CF3C',
                'padding': '10px',
                'color': '#000'
              });
            }
            else if (response.status === 'success') {
              $wrapper.find('.message__like_dislike').text(response.message).css({
                'background-color': '#EA4131',
                'padding': '10px',
                'color': '#fff'
              });
              // ✅ Update counts inside this wrapper container
              $wrapper.find('.like_count').text(response.likes);
              $wrapper.find('.dislike_count').text(response.dislikes);
            }
          },
          error: function () {
            alert('There was an error disliking the post.');
          }
        });
      });
    });
  }
};

})(jQuery, Drupal);
