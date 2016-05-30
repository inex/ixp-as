
$( document ).ready( function(){

    $( "span[id|='atlas-json']" ).css('cursor', 'pointer').on( 'click', function() {
        showJSON( $(this).attr('id') );
    });

    $( "span[id|='ip-address']" ).css('cursor', 'pointer')
        .css('border-bottom', '1px dotted #000' )
        .on( 'click', function() {
            showAsnInfo( $(this).attr('id') );
        });

});

function showJSON( id ) {
    meas = $('#'+id).attr('data-measid');
    type = id.substring(11);
    type = type.substring( 0, type.length - meas.length - 1);

    $('#modal-json').modal('hide');

    body = function( data ) {
        if( data == "null" ) {
            return "<p>No data available yet...</p>";
        } else {
            return "<pre>" + data + "</pre>";
        }
    };

    switch( type ) {
        case 'requestin':
            $('#modal-json-header').html( "Request: " + (results.measurements[meas]).dnetwork.name + " to " + results.snetwork.name );
            $('#modal-json-body').html( body( (results.measurements[meas]).atlas_in_request_data ) );
            break;

        case 'requestout':
            $('#modal-json-header').html( "Request: " + results.snetwork.name + " to " + (results.measurements[meas]).dnetwork.name );
            $('#modal-json-body').html( body( (results.measurements[meas]).atlas_out_request_data ) );
            break;

        case 'responsein':
            $('#modal-json-header').html( "Response: " + (results.measurements[meas]).dnetwork.name + " to " + results.snetwork.name );
            $('#modal-json-body').html( body( (results.measurements[meas]).atlas_in_data ) );
            break;

        case 'responseout':
            $('#modal-json-header').html( "Response: " + results.snetwork.name + " to " + (results.measurements[meas]).dnetwork.name );
            $('#modal-json-body').html( body( (results.measurements[meas]).atlas_out_data ) );
            break;


    }

    $('#modal-json').modal('show');

}

function showAsnInfo( id ) {
    $("span[id|='ip-address']").popover('hide');
    $("span[id|='ip-address']").popover('destroy');

    ip = $('#'+id).html();
    var jqxhr = $.getJSON( whois + "/" + ip, function() {
          console.log( "Sending whois request for: " + ip );
        })
        .done(function(data) {
            $('#'+id).popover({'content': formatWhois(data), 'html': true });
        })
        .fail(function() {
            $('#'+id).popover({'content':"Could not perform whois query"});
        })
        .always(function() {
            $('#'+id).popover('show');
        });
}

function formatWhois(d) {

    return "<strong>ASN:</strong>&nbsp" + d.asn + "<br>" +
        "<strong>LIR:</strong>&nbsp" + d.lir + "<br>" +
         "<strong>Prefix:</strong>&nbsp" + d.prefix + "<br>" +
         "<strong>RIR:</strong>&nbsp" + d.rir + "<br>" +
         "<strong>Date:</strong>&nbsp" + d.date;


}
