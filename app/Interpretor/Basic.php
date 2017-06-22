<?php

namespace App\Interpretor;

use Entities\Measurement;
use Entities\Result;
use Entities\Network;
use Entities\IXP;

class Basic
{

    /**
     * The measurement ORM entity
     * @var Measurement
     */
    private $measurement;


    /**
     * Basic constructor.
     * @param Measurement $m
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
        $srcAddrs = $this->getAddressesFromNetwork( $m->getRequest()->getIXP(), $m->getRequest()->getNetwork(), $m->getRequest()->getProtocol() );
        $dstAddrs = $this->getAddressesFromNetwork( $m->getRequest()->getIXP(), $m->getDestinationNetwork(),    $m->getRequest()->getProtocol() );

        $atlasOut = json_decode( $m->getAtlasOutData() );
        $atlasIn  = json_decode( $m->getAtlasInData() );

        $pathOut = $this->parsePath( $atlasOut );
        $pathIn  = $this->parsePath( $atlasIn );

        $viaLanOut = $this->queryPassesThrough( $pathOut, $dstAddrs[ 'lan' ] );
        $viaLanIn  = $this->queryPassesThrough( $pathIn,  $srcAddrs[ 'lan' ] );

        $r = new Result();

        if( $viaLanOut && $viaLanIn ) {
            $r->setRouting( 'IXP_LAN_SYM' );
        } else {
            $viaIxpOut = $this->queryPassesThrough( $pathOut, $dstAddrs[ 'ixp' ] );
            $viaIxpIn = $this->queryPassesThrough( $pathIn, $srcAddrs[ 'ixp' ] );

            if( ( $viaIxpOut && $viaIxpIn ) || ( $viaIxpOut && $viaLanIn ) || ( $viaLanOut && $viaIxpIn ) ) {
                $r->setRouting( 'IXP_SYM' );
            } else if( !$viaIxpOut && $viaIxpIn ) {
                $r->setRouting( 'IXP_ASYM_OUT' );
            } else if( $viaIxpOut && !$viaIxpIn ) {
                $r->setRouting( 'IXP_ASYM_IN' );
            } else {
                $r->setRouting( 'NON_IXP' );
            }
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
     * @return array The path
     */
    private function parsePath( array $tracert ) {
        $path = [
            'hops' => [],
            'ixpx' => [],  // point of intersection with IXP
        ];

        foreach( $tracert[0]->result as $hop ) {
            // three iterations means each hop has three results:
            $results = [];
            foreach( $hop->result as $result ) {

                if( !isset( $result->from ) ) {
                    continue;
                }

                if( in_array( $result->from, $results ) ) {
                    continue;
                }

                $results[] = $result->from;
            }

            if( count($results) ) {
                $path['hops'][] = $results;
            } else {
                $path['hops'][] = [ '*' ];
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
    private function queryPassesThrough( array &$path, array $addrs ) {

        foreach( $path['hops'] as $ipset ) {
            foreach( $ipset as $ip ) {
                if( in_array( $ip, $addrs ) ) {
                    $path['ixpx'][] = $ip;
                }
            }
        }

        return count( $path['ixpx'] );
    }

    /**
     * For a given network (ORM) and protocol, find their IXP assigned IP addresses
     *
     * @param IXP $ixp
     * @param Network $n The IXP customer object
     * @param int $p The protocol
     * @return array Array of addresses
     */
    private function getAddressesFromNetwork( IXP $ixp, Network $n, $p ) {
        $addrs = [
            'lan' => [], // same LAN
            'ixp' => [], // same IXP
        ];

        foreach( $n->getAddresses() as $a ) {
            if( $a->getProtocol() != $p ) {
                continue;
            }

            if( $a->getLAN()->getIXP()->getId() == $ixp->getId() ) {
                $addrs['lan'][] = $a->getAddress();
            } else if( $a->getLAN()->getIXP()->getOrganisation() == $ixp->getOrganisation() ) {
                $addrs['ixp'][] = $a->getAddress();
            }
        }

        return $addrs;
    }

}
