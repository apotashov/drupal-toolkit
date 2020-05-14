/**
 * @file
 * Adds edit links alongside entity reference fields.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.toolkitAutocompleteEditLink = {
    attach: function attach(context, settings) {
      // Edit link callback
      var applyEditLink = function(el) {
        var wrapper = el.closest('.form-item');
        var val = el.val();
        var regExp = /\(([0-9)]+)\)/;
        var idMatches = regExp.exec(val);

        // Remove previously set edit link.
        if ($('.acEditLink', wrapper).length) {
          $('.acEditLink', wrapper).remove();
        }

        // Set edit link.
        if (val) {
          // Remove potential double or single quotes from both sides of a string with a regular expression.
          val = val.replace(/^("|')+|("|')+$/gm, '');
          if (val.substr(-1) === ')' && idMatches && idMatches[1]) {
            var link = el.data('edit-form').replace('entity_id', idMatches[1]);
            $('<a target="_blank" class="acEditLink button" href="' + link + '">Edit</a>').insertAfter(el);
          }
        }
      };

      // Attach events.
      $(context).find('input[data-edit-form]').once('autocompleteEditLink').each(function () {
        // Apply edit link.
        applyEditLink($(this));

        // Autocomplete events.
        $(this).on('autocompleteclose keyup', function () {
          applyEditLink($(this));
        });
      });
    }
  };

})(jQuery);
