
$( document ).ready( function(){

    for( var i=0; i < ixps.length; i++ ) {
        $('#ixp_select').append( '<option value="' + i + '">' + ixps[i].shortname + '</option>' );
    }

    $('#ixp_select').on( 'change', function(){
        setDropdowns();
    });

    $('#protocol_select').on( 'change', function() {
        setDropdowns();
    });

    $('#network_select').on( 'change', function() {
        if( $('#network_select' ).val() === '' ) {
            $('#form_network' ).val( '' );
            $('#form_protocol').val( '' );
            $('#div_submit').hide();
        } else {
            $('#form_network' ).val( $('#network_select').val() );
            $('#form_protocol').val( $('#protocol_select').val() );
            $('#div_submit').show();
        }
    });

});

function setDropdowns() {

    // reset networks
    $('#network_select').html('<option value=""></option>');
    $('#network_select').val('');

    // if no IXP or protocol is selected, hide networks:
    if( $('#ixp_select').val() === '' || $('#protocol_select').val() === '' ) {
        $('#div_network').hide();
    }

    // if no IXP is selected, hide and reset protocol (and we're done):
    if( $('#ixp_select').val() === '' ) {
        $('#div_protocol').hide();
        $('#protocol_select').val('');
        return;
    }

    // so, at this point, networks are reset and we have an IXP
    $('#div_protocol').show();

    // if we don't have a protocol, we're also done now:
    if( $('#protocol_select').val() === '' ) {
        return;
    }

    // so we have an IXP and a protocol - populate networks:
    ixps[ $('#ixp_select').val() ].networks.forEach( function( network ){
        var index = 'v' + $('#protocol_select').val();

        if( network[ index ] ) {
            index += 'asn';
            $('#network_select').append( '<option value="' + network.id + '">' + network.name + ' - AS' + network[index] + '</option>' );
        }
    });

    $('#div_network').show();

}
