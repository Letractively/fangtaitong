Z.Module.index = Z.Module.index || {};
Z.Module.index.index = Z.Module.index.index || {};

Z.Module.index.index.index = Z.Module.index.index.index || {};
Z.Module.index.index.index.init = function()
{
    $('.home-banner-showcase-wrapper').roundabout({
        minScale: 0.58,
        minOpacity: 0.6,
        childSelector: "img",
        autoplay: true,
        autoplayDuration: 4000,
        autoplayPauseOnHover: true
    });

    var now = {};
    $('.price_board').hover(function() {
        now = $(this);
        now.animate({marginTop:0},{queue:true, duration:200});
    }, function() {
        now.stop(true);
        $(this).animate({marginTop:10}, 200);
    });
};

Z.Module.index.index.history = Z.Module.index.index.history || {};
Z.Module.index.index.history.init = function()
{

};