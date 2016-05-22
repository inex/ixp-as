<?php

namespace App\Interpretor;

use Entities\Measurement;
use Entities\Result;
use Entities\Network;

class Basic
{

    /**
     * The measurement ORM entity
     * @var Entities\Measurement
     */
    private $measurement;


    /**
     * Constructor
     */
    public function __construct( Measurement $m ) {
        $this->measurement = $m;
    }


    /**
     * Basic Interpretor
     */
    public function interpret(): Result {
        $m = $this->measurement;

        // what is the source network's peering addresses?
        $srcAddrs = $this->getAddressesFromNetwork( $m->getRequest()->getNetwork(), $m->getRequest()->getProtocol() );
        $dstAddrs = $this->getAddressesFromNetwork( $m->getDestinationNetwork(),    $m->getRequest()->getProtocol() );

        $atlasOut = json_decode($m->getAtlasOutData());
        $atlasIn  = json_decode($m->getAtlasInData() );

        $pathOut = $this->parsePath( $atlasOut );
        $pathIn  = $this->parsePath( $atlasIn  );

        $viaIxpOut = $this->queryPassesThrough( $pathOut, $dstAddrs );
        $viaIxpIn  = $this->queryPassesThrough( $pathIn,  $srcAddrs );

        $r = new Result();

        if( $viaIxpOut && $viaIxpIn ) {
            $r->setRouting( 'IXP_SYM' );
        } else if( !$viaIxpOut && $viaIxpIn ) {
            $r->setRouting( 'IXP_ASYM_OUT' );
        } else if( $viaIxpOut && !$viaIxpIn ) {
            $r->setRouting( 'IXP_ASYM_IN' );
        } else {
            $r->setRouting( 'NON_IXP' );
        }

        $r->setPathOut( serialize( $pathOut ) );
        $r->setPathIn(  serialize( $pathIn ) );

        return $r;
    }

    /**
     * Take a RIPE Atles traceroute result and extract the path
     *
     * NB: FIXME?: Assumes no ECMP... takes only one IP per hop.
     *
     * @param array $tracert Raw RIPE Atles JSON result as PHP
     * @return path The path
     */
    private function parsePath( array $tracert ): array {
        $path = [];

        foreach( $tracert[0]->result as $e ) {
            foreach( $e->result as $hop ) {
                if( !isset( $hop->from ) ) {
                    continue;
                }

                // assuming every hop is the same (e.g. no ECMP)
                $path[] = $hop->from;
                break;
            }
        }

        return $path;
    }

    /**
     * For a given path of IP addresses, see if another list of addresses appears in the path
     *
     * @param array $path Path of IP addresses
     * @param array $addrs List of addresses to find in $path
     * @return bool
     */
    private function queryPassesThrough( array $path, array $addrs ): bool {

        foreach( $path as $ip ) {
            if( in_array( $ip, $addrs ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * For a given network (ORM) and protocol, find their IXP assigned IP addresses
     *
     * @param Entities\Network $n The IXP customer object
     * @param int $p The protocol
     * @return array Array of addresses
     */
    private function getAddressesFromNetwork( Network $n, int $p ): array {
        $addrs = [];
        foreach( $n->getAddresses() as $a ) {
            if( $a->getProtocol() != $p ) {
                continue;
            }
            $addrs[] = $a->getAddress();
        }
        return $addrs;
    }

}
