define([
    "jquery",
    "jquery/ui",
    "OwlCarousel"
], function ($) {
    "use strict";
    return function main(config, element) {
        var $element = $(element);
        var ajaxUrl = config.ajaxUrl;
        $(document).ready(function () {
            setTimeout(function () {
                $.ajax({
                    context: '#lsRecommendation',
                    url: ajaxUrl,
                    type: "POST"
                }).done(function (data) {
                    $('#lsRecommendation').html(data.output);
                    $("#mp-list-items-lsrecommend").owlCarousel({
                        autoPlay: true,
                        items: 4,
                        itemsDesktop: [1199, 3],
                        itemsDesktopSmall: [979, 3]
                    });
                    return true;
                });
            }, 2000);
        });
    };
});
