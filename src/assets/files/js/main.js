//https://jsfiddle.net/BSCheshir/xpvt214o/161574/

$( ".column" ).sortable({
    connectWith: ".column",
    handle: ".portlet-header",
    cancel: ".portlet-toggle",
    start: function (event, ui) {
        ui.item.addClass('tilt');
        tiltDirection(ui.item);
    },
    stop: function (event, ui) {
        ui.item.removeClass("tilt");
        $("html").unbind('mousemove', ui.item.data("moveHandler"));
        ui.item.removeData("moveHandler");
    }
});

function tiltDirection(item) {
    var leftLastPosition = item.position().left,
        lastPosition,
        moveHandler = function (e) {
            if (e.pageX >= leftLastPosition) {
                if (lastPosition !== 'cw') {
                    item.addClass("cw");
                    item.removeClass("ccw");
                }
                lastPosition = 'cw';
                if ((e.pageX - leftLastPosition) > 3) {
                    leftLastPosition = e.pageX;
                }

            } else {
                if (lastPosition !== 'ccw') {
                    item.addClass("ccw");
                    item.removeClass("cw");
                }
                lastPosition = 'ccw';
                if ((leftLastPosition - e.pageX) > 3) {
                    leftLastPosition = e.pageX;
                }

            }
        };
    $("html").bind("mousemove", moveHandler);
    item.data("moveHandler", moveHandler);
}

$( ".portlet" )
    .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
    .find( ".portlet-header" )
    .addClass( "ui-widget-header ui-corner-all" )
    .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

$( ".portlet-toggle" ).click(function() {
    var icon = $( this );
    icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
    icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
});

var counter = 0;
$( ".portlet" ).each(function(index) {
    $( this ).data('alias', $( this ).attr("id") || counter++);
});
counter = 0;
$( ".column" ).each(function(index) {
    $( this ).data('alias', $( this ).attr("id") || counter++);
});
