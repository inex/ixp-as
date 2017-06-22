<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

function generateIXPData() {
    // populate array of IXPs -> Networks
    $ixps = [];
    foreach( Registry::getRepository('Entities\IXP')->findAll() as $ixp ) {
        $i['id']        = $ixp->getId();
        $i['name']      = $ixp->getName();
        $i['shortname'] = $ixp->getShortname();
        $i['networks'] = [];

        foreach( $ixp->getNetworks() as $network ) {
            if( !count( $network->getProbes() ) ) {
                continue;
            }

            $n['id']      = $network->getId();
            $n['name']    = $network->getName();
            $n['asn']     = $network->getAsn();

            $n['v4']      = $network->hasProbeProtocol( 4 );
            $n['v6']      = $network->hasProbeProtocol( 6 );

            $i['networks'][] = $n;
        }

        $ixps[] = $i;
    }
    return $ixps;
}

Route::get('/', function () {
    return view('index', [ 'ixps' => json_encode( generateIXPData() ) ] );
});

Route::get('/json', function () {
    return response()->json( generateIXPData() );
});


Route::get('/request/{ixp_id}/{network_id}/{protocol}/{json?}',function( $ixp_id, $network_id, $protocol, $json = false ){
    if( !( $n = Registry::getRepository('Entities\Network')->find( $network_id ) ) ) {
        App::abort(404);
    }

    $ixp = false;
    foreach( $n->getIXPs() as $i ) {
        if( $i->getId() == $ixp_id ) {
            $ixp = $i;
            break;
        }
    }

    if( !$ixp ) {
        App::abort(404);
    }

    // let's rate limit these a little
    $existing = Registry::getRepository('Entities\Request')->findOneBy(
            [ 'IXP' => $ixp, 'network' => $n, 'protocol' => $protocol, 'completed' => null ]
        );

    if( $existing ) {
        if( $json ) {
            return response()->json( ['request_nonce' => $existing->getNonce()] );
        } else {
            return redirect( '/result/' . $existing->getNonce() )->with('duplicate',true);
        }
    }

    $r = new Entities\Request;
    $r->setIXP($ixp);
    $r->setNetwork($n);
    $r->setProtocol($protocol);
    $r->setCreated( new Carbon\Carbon );
    EntityManager::persist($r);
    EntityManager::flush();
    $r->setNonce( $r->getId() . '-' . strtolower(str_random(8)) . '-' . strtolower(str_random(8)) . '-' . strtolower(str_random(8)) );
    EntityManager::flush();

    if( strtolower( $json ) == 'json' ) {
        return response()->json( ['request_nonce' => $r->getNonce()] );
    }

    return redirect( '/result/' . $r->getNonce() );
})
    ->where( [
        'ixp_id'     => '[0-9]+',
        'network_id' => '[0-9]+',
        'protocol'   => '[46]{1,1}'
    ] );


Route::get('/result/{nonce}/{json?}', function($nonce,$json=false) {

    /** @var Entities\Request $r */
    if( !( $r = Registry::getRepository('Entities\Request')->findOneBy( ['nonce' => $nonce ] ) ) ) {
        App::abort(404);
    }

    // build up JSON object for the result
    $obj = new stdClass;
    $obj->protocol  = $r->getProtocol();
    $obj->created   = $r->getCreated();
    $obj->started   = $r->getStarted();
    $obj->completed = $r->getCompleted();

    $obj->ixp               = new stdClass;
    $obj->ixp->name         = $r->getIXP()->getName();
    $obj->ixp->shortname    = $r->getIXP()->getShortname();
    $obj->ixp->organisation = $r->getIXP()->getOrganisation();

    $obj->snetwork = new stdClass;
    $obj->snetwork->name = $r->getNetwork()->getName();
    $obj->snetwork->asn  = $r->getNetwork()->getAsn();

    $obj->measurements = [];

    foreach( $r->getMeasurements() as $m ) {
        $mc = new stdClass;
        $mc->id = $m->getId();

        if( $m->getAtlasOutState() == 'Failed' || $m->getAtlasOutState() == 'ABANDONNED'
                || $m->getAtlasInState() == 'Failed' || $m->getAtlasInState() == 'ABANDONNED' ) {
            $mc->state = 'Failed';
        }

        $mc->dnetwork = new stdClass;
        $mc->dnetwork->name = $m->getDestinationNetwork()->getName();
        $mc->dnetwork->asn  = $m->getDestinationNetwork()->getAsn();

        $mc->atlas_out_data         = json_encode( json_decode( $m->getAtlasOutData()        ), JSON_PRETTY_PRINT );
        $mc->atlas_in_data          = json_encode( json_decode( $m->getAtlasInData()         ), JSON_PRETTY_PRINT );
        $mc->atlas_out_request_data = json_encode( json_decode( $m->getAtlasOutRequestData() ), JSON_PRETTY_PRINT );
        $mc->atlas_in_request_data  = json_encode( json_decode( $m->getAtlasInRequestData()  ), JSON_PRETTY_PRINT );

        if( $m->getResult() ) {
            $mc->result = new stdClass;
            $mc->result->routing  = $m->getResult()->getRouting();
            $mc->result->path_in  = unserialize( $m->getResult()->getPathIn() );
            $mc->result->path_out = unserialize( $m->getResult()->getPathOut() );
        } else {
            $mc->result = null;
        }

        $obj->measurements[$mc->id] = $mc;
    }

    // rewrite dates to JS
    $jobj = clone $obj;
    $jobj->created   = Carbon\Carbon::instance( $r->getCreated() )->toATOMString() . 'Z';
    $jobj->started   = $r->getStarted()   ? Carbon\Carbon::instance( $r->getStarted()   )->toATOMString()   . 'Z' : false;
    $jobj->completed = $r->getCompleted() ? Carbon\Carbon::instance( $r->getCompleted() )->toATOMString() . 'Z' : false;

    if( strtolower( $json ) == 'json' ) {
        return response()->json( $jobj );
    }

    // get a list of networks with no Atlas probe
    $networksWithoutProbes = [];
    foreach( $r->getIXP()->getNetworks() as $n ) {
        foreach( $n->getProbes() as $p ) {
            if( $r->getProtocol() == 4 && $p->getV4Enabled() ) {
                continue 2;
            }
            if( $r->getProtocol() == 6 && $p->getV6Enabled() ) {
                continue 2;
            }
        }

        $networksWithoutProbes[] = $n;
    }

    return view('result', [ 'request' => $obj, 'json' => json_encode( $jobj ), 'nonce' => $nonce, 'networksWithoutProbes' => $networksWithoutProbes ] );
})
    ->where( ['nonce' => '[\d]+\-[\w]{8,8}-[\w]{8,8}-[\w]{8,8}'] );

Route::get('/history', function () {
    // populate array of historical requests
    return view('history', [ 'requests' => Registry::getRepository('Entities\Request')->findBy([], ['created' => 'DESC']) ] );
});

Route::get('/whois/{ip}', function( $ip ) {
    if( strpos( $ip, '.' ) ) {
        $host    = reverseIPv4($ip);
        $ptrHost = reverseIPv4($ip, '.in-addr.arpa.');
    } else {
        $host = ipv6ToNibble($ip);
        $ptrHost = ipv6ToNibble($ip, '.ip6.arpa.');
    }

    $obj = new stdClass;
    $obj->error = true;

    try {
        if( count( $lu = dns_get_record( $host, DNS_TXT ) ) ) {
            $data = explode( ' | ', $lu[0]['txt'] );
            $obj->asn    = isset( $data[0] ) ? $data[0] : '??';
            $obj->prefix = isset( $data[1] ) ? $data[1] : '??';
            $obj->cc     = isset( $data[2] ) ? $data[2] : '??';
            $obj->rir    = isset( $data[3] ) ? $data[3] : '??';
            $obj->date   = isset( $data[4] ) ? $data[4] : '??';

            $host = "AS{$obj->asn}.asn.cymru.com";
            if( count( $lu = dns_get_record ( $host, DNS_TXT ) ) ) {
                $data = explode(' | ',$lu[0]['txt']);
                $obj->lir = isset( $data[4] ) ? $data[4] : '??';
            } else {
                $obj->lir = "** UNKNOWN **";
            }
            $obj->error = false;
        }

        // also do PTR
        if( count( $lu = dns_get_record ( $ptrHost, DNS_PTR ) ) ) {
            $obj->ptr = isset( $lu[0]['target'] ) ? $lu[0]['target'] : '(unknown)';
        }

    } catch( \Exception $e ) {
        // dns query failed - ignore as $obj->error pre-emptivily set to false
        $obj->errorMsg = $e->getMessage();
    }
    
    return response()->json( $obj );
})
    ->where( [
        'ip'         => '[0-9a-f\.:]+'
    ] );


function ipv6ToNibble($ipv6,$domain=null) {
    $addr = inet_pton($ipv6);
    $unpack = unpack('H*hex', $addr);
    $hex = $unpack['hex'];
    return implode('.', array_reverse(str_split($hex))) . ( $domain !== null ? $domain : '.origin6.asn.cymru.com.' );
}

function reverseIPv4($ipv4,$domain=null) {
    list( $a, $b, $c, $d ) = explode( '.', $ipv4 );
    return "{$d}.{$c}.{$b}.{$a}" . ( $domain !== null ? $domain : ".origin.asn.cymru.com." );
}
