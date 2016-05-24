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

Route::get('/', function () {
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
            $n['v4']      = $network->getV4ASN() ? true : false;
            $n['v6']      = $network->getV6ASN() ? true : false;
            $n['v4asn']   = $network->getV4ASN();
            $n['v6asn']   = $network->getV6ASN();
            $i['networks'][] = $n;
        }

        $ixps[] = $i;
    }

    return view('index', [ 'ixps' => json_encode( $ixps ) ] );
});

Route::get('/request/{network_id}/{protocol}/{json?}',function( $network_id, $protocol, $json = false ){
    if( !( $n = Registry::getRepository('Entities\Network')->find( $network_id ) ) ) {
        App::abort(404);
    }

    $r = new Entities\Request;
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
        'network_id' => '[0-9]+',
        'protocol'   => '[46]{1,1}'
    ] );


Route::get('/result/{nonce}/{json?}', function($nonce,$json=false) {
    if( !( $r = Registry::getRepository('Entities\Request')->findOneBy( ['nonce' => $nonce ] ) ) ) {
        App::abort(404);
    }

    // build up JSON object for the result
    $obj = new stdClass;
    $obj->protocol  = $r->getProtocol();
    $obj->created   = Carbon\Carbon::instance( $r->getCreated() )->toATOMString() . 'Z';
    $obj->started   = $r->getStarted()   ? Carbon\Carbon::instance( $r->getStarted()   )->toATOMString()   . 'Z' : false;
    $obj->completed = $r->getCompleted() ? Carbon\Carbon::instance( $r->getCompleted() )->toATOMString() . 'Z' : false;

    $obj->snetwork = new stdClass;
    $obj->snetwork->name = $r->getNetwork()->getName();
    $obj->snetwork->asn  = $r->getNetwork()->getV4ASN();

    $obj->snetwork->ixp = new stdClass;
    $obj->snetwork->ixp->shortname = $r->getNetwork()->getIXP()->getShortname();

    $obj->measurements = [];

    foreach( $r->getMeasurements() as $m ) {
        $mc = new stdClass;
        $mc->id = $m->getId();
        $mc->dnetwork = new stdClass;
        $mc->dnetwork->name = $m->getDestinationNetwork()->getName();
        $mc->dnetwork->asn  = $m->getDestinationNetwork()->getV4ASN();

        if( $m->getResult() ) {
            $mc->result = new stdClass;
            $mc->result->routing  = $m->getResult()->getRouting();
            $mc->result->path_in  = unserialize( $m->getResult()->getPathIn() );
            $mc->result->path_out = unserialize( $m->getResult()->getPathOut() );
        } else {
            $mc->result = null;
        }

        $obj->measurements[] = $mc;
    }

    if( strtolower( $json ) == 'json' ) {
        return response()->json( $obj );
    }

    return view('result', [ 'request' => $obj, 'nonce' => $nonce ] );
})
    ->where( ['nonce' => '[\d]+\-[\w]{8,8}-[\w]{8,8}-[\w]{8,8}'] );
