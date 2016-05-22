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

Route::get('request_traceroute', 'RequestTracerouteController@showForm');

Route::get(
  'request/asn/{asn}/proto/{protocol}/ixp/{ixp}',
  'RequestTracerouteController@requestTrace'
);

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

Route::get('/result/{nonce}/{json?}', function($nonce,$json=false) {
    if( !( $r = Registry::getRepository('Entities\Request')->findOneBy( ['nonce' => $nonce ] ) ) ) {
        App::abort(404);
    }

    // tmp
    // $r = createRequest();

    // build up JSON object for the result
    $obj = new stdClass;
    $obj->protocol  = $r->getProtocol();
    $obj->created   = Carbon\Carbon::parse( $r->getCreated() )->toATOMString() . 'Z';
    $obj->started   = $r->getStarted()   ? Carbon\Carbon::parse( $r->getStarted()   )->toATOMString() . 'Z' : null;
    $obj->completed = $r->getCompleted() ? Carbon\Carbon::parse( $r->getCompleted() )->toATOMString() . 'Z' : null;

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
    ->where( ['nonce' => '[0-9]+'] );


function createRequest() {
    $n = new Entities\Network();
    $n->setName('Cablecomm');
    $n->setV4ASN('44384');

    $ixp = new Entities\IXP;
    $ixp->setShortname('INEX');
    $n->setIXP($ixp);

    $l = new Entities\LAN();
    $l->setName('Peering LAN1');
    $l->setProtocol(4);
    $l->setSubnet('193.242.111.0');
    $l->setMasklen(25);

    $a = new Entities\Address();
    $a->setProtocol(4);
    $a->setAddress('193.242.111.87');
    $a->setNetwork($n);
    $n->addAddress($a);
    $a->setLAN($l);

    $r = new Entities\Request();
    $r->setNetwork($n);
    $r->setProtocol(4);
    $r->setCreated( '2016-05-22 10:50:02' );
    $r->setStarted( '2016-05-22 10:50:02' );
    // $r->setCompleted( '2016-05-22 10:50:02' );

    $on = new Entities\Network();
    $on->setName('Eircom');
    $on->setV4ASN('5466');

    $oa = new Entities\Address();
    $oa->setProtocol(4);
    $oa->setAddress('193.242.111.82');
    $oa->setNetwork($on);
    $on->addAddress($oa);
    $oa->setLAN($l);

    $m = new Entities\Measurement;
    $m->setRequest($r);
    $m->setDestinationNetwork($on);
    $r->addMeasurement($m);
    $m->setAtlasOutData( file_get_contents("https://atlas.ripe.net/api/v1/measurement/3806499/result") );
    $m->setAtlasInData( file_get_contents("https://atlas.ripe.net/api/v1/measurement/3806501/result") );

    $rs = new Entities\Result;
    $rs->setRouting( 'IXP_SYM' );
    $rs->setPathIn(  [ '3.2.3.4', '3.6.7.8', '3.8.7.6', '3.4.3.2' ] );
    $rs->setPathOut( [ '4.2.3.4', '4.6.7.8', '4.8.7.6', '4.4.3.2' ] );
    $m->setResult( $rs );

    return $r;
}
