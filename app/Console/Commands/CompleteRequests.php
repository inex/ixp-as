<?php

namespace App\Console\Commands;


use Registry;
use EntityManager;

use Entities\Measurement;
use Entities\Probe;
use Entities\Request;

use Carbon\Carbon;

class CompleteRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:complete-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark reuests complete if all measurements are complete';

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
        if( $this->isVerbose() ) { $this->info("---- COMPLETE REQUESTS START ----"); }

        // find new requests:
        if( !( $requests = Registry::getRepository('Entities\Request')->findBy( ['completed' => null ] ) ) ) {
            if( $this->isVerbose() ) {
                $this->info("No open requests to process");
            }
            exit;
        }

        foreach( $requests as $r ) {
            $this->process( $r );
        }

        if( $this->isVerbose() ) { $this->info("---- COMPLETE REQUESTS STOP  ----"); }
    }

    private function process( Request $r ) {
        foreach( $r->getMeasurements() as $m ) {
            if( !$m->getAtlasInStop() || !$m->getAtlasOutStop() ) {
                return;
            }
        }

        // otherwise all measurements complete, set request as complete
        if( $this->isVerbose() ) {
            $this->info("Marking request {$r->getId()} comeplete");
        }

        $r->setCompleted( new Carbon );
        EntityManager::flush();
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
