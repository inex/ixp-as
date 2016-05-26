
$( document ).ready( function(){

    // $( "span[id|='atlas-json']" ).each( function(e) {
    //     //alert( $(this).attr('id').substring(11) );
    // });

    $( "span[id|='atlas-json']" ).css('cursor', 'pointer').on( 'click', function() {
        showJSON( $(this).attr('id') );
    });

});

function showJSON( id ) {
    meas = $('#'+id).attr('data-measid');
    type = id.substring(11);
    type = type.substring( 0, type.length - meas.length - 1);

    $('#modal-json').modal('hide');

    body = function( data ) {
        alert(data);
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
