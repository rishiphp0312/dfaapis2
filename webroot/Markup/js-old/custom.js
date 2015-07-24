$(document).ready(function ()
{
    /*****************Popup Box Starts Here******************/
    $('a.popup').click(function ()
    {
        var popupid = $(this).attr('rel');
        $('#' + popupid).fadeIn();
        $('body').append('<div id="fade"></div>');
        $('#fade').css({ 'filter': 'alpha(opacity=80)' }).fadeIn();
        var popuptopmargin = ($('#' + popupid).height() + 10) / 2;
        var popupleftmargin = ($('#' + popupid).width() + 10) / 2;
        $('#' + popupid).css({
            'margin-top': -popuptopmargin,
            'margin-left': -popupleftmargin
        });
    });
    $('#fade, #close, #start_survey, #vote_indi').click(function ()
    {
        $('#fade, .popupbox').fadeOut()
        return false;
    });
    /*****************Popup Box Ends Here******************/


    /***********Setting the Heights Dynamically************/
    function setHeight()
    {
        var top = $('.main-header ').outerHeight(true);
        var bottom = $('.footer').outerHeight(true);
        var totHeight = $(window).height();
        var scrollHeight = (totHeight - top - bottom);

        $('.scroll').css({
            'min-height': scrollHeight + 'px'
        });
    }
    $(window).load(function () { setHeight(); });
    $(window).on('resize', function () { setHeight(); });
});    