
$(function() {
    'use strict';
     var borderless = true;
    $('#blueimp-gallery').data('useBootstrapModal', !borderless);
    $('#blueimp-gallery').toggleClass('blueimp-gallery-controls', borderless);
});
