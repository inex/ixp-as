<?php

namespace App\Interpretor;

use Entities\Measurement;
use Entities\Result;

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
    public function interpret() {
        $m = $this->measurement;

        // what is the source network's peering addresses?
        $srcAddrs = [];
        foreach( $m->getRequest()->getNetwork()->getAddresses() as $a ) {
            if( $a->getProtocol() != $m->getRequest()->getProtocol() ) {
                continue;
            }
            $srcAddrs[] = $a->getAddress();
        }

        // what is the destination network's peering addresses?
        $dstAddrs = [];
        foreach( $m->getDestinationNetwork()->getAddresses() as $a ) {
            if( $a->getProtocol() != $m->getRequest()->getProtocol() ) {
                continue;
            }
            $dstAddrs[] = $a->getAddress();
        }

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

    private function parsePath( $tracert ) {
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

    private function queryPassesThrough( $path, $addrs ) {

        foreach( $path as $ip ) {
            if( in_array( $ip, $addrs ) ) {
                return true;
            }
        }

        return false;
    }

}
