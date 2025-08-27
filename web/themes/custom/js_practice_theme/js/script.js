(function ($, Drupal) {
  Drupal.behaviors.nodeAlertBehavior = {
    attach: function (context, settings) {
      // When the button with the ID 'node-info-button' is clicked
      $('#node-info-button', context).click(function () {
        // Get the Node ID and Title from the button's data attributes
        let nodeId = $(this).data('node-id');
        let nodeTitle = $(this).data('node-title');

        // Show the alert with Node ID and Title
        console.log('Node ID: ' + nodeId + '\nNode Title: ' + nodeTitle);
        $('.node__content', context).css('background-color', 'red');
        alert('Node ID: ' + nodeId + '\nNode Title: ' + nodeTitle);
      });
    }
  };
})(jQuery, Drupal);
