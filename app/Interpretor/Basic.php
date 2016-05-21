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

        $r->setPathOut( $pathOut );
        $r->setPathIn(  $pathIn  );

        return $r;
    }

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

    private function queryPassesThrough( array $path, array $addrs ): bool {

        foreach( $path as $ip ) {
            if( in_array( $ip, $addrs ) ) {
                return true;
            }
        }

        return false;
    }


    private function getAddressesFromNetwork( Network $n, int $requestProtocol ): array {
        $addrs = [];
        foreach( $n->getAddresses() as $a ) {
            if( $a->getProtocol() != $requestProtocol ) {
                continue;
            }
            $addrs[] = $a->getAddress();
        }
        return $addrs;
    }

}
