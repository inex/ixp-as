<?php

namespace App\Console\Commands;


use Registry;
use EntityManager;

use Entities\Measurement;
use Entities\Probe;
use Entities\Request;

use Carbon\Carbon;

class CreateMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:create-measurements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process new end user requests and queue measurements';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // find new requests:
        if( !( $requests = Registry::getRepository('Entities\Request')->findBy( ['started' => null ] ) ) ) {
            if( $this->isVerbose() ) {
                $this->info("No new requests to process");
            }
            exit;
        }

        foreach( $requests as $r ) {
            $this->process( $r );
        }
    }

    private function process( Request $r ) {
        // we assume there are origin probes -> otherwise the network is unselectable in the frontend

        // find all candidate networks at this IXP. I.e. networks with probes for this protocol
        $destNetworks = $this->getNetworksWithProbes( $r->getNetwork()->getIXP()->getId(), $r->getProtocol(), $r->getNetwork() );

        foreach($destNetworks as $dn) {
            if( $this->isVerbose() ) {
                $this->info( "Creating measurement for {$r->getNetwork()->getName()} / {$dn->getName()}" );
            }

            $m = new Measurement;
            $m->setRequest($r);
            $m->setDestinationNetwork($dn);
            EntityManager::persist($m);

            $r->setStarted( new Carbon );
            EntityManager::flush();
        }
    }


    private function getNetworksWithProbes( $ixpid, $protocol, $excludeNetwork = null ) {

        $enabled = $protocol == 4 ? 'v4_enabled' : 'v6_enabled';
        $asn     = $protocol == 4 ? 'v4asn' : 'v6asn';

        $query = "SELECT n FROM Entities\\Network n
            LEFT JOIN n.IXP as i
            LEFT JOIN n.probes as p
            WHERE i.id = {$ixpid} AND p.{$enabled} = 1 AND n.{$asn} IS NOT NULL";

        if( $excludeNetwork ) {
            $query .= " AND n.id != " . $excludeNetwork->getId();
        }

        return EntityManager::createQuery( $query )->getResult();
    }
}
