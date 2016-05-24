<?php

namespace App\Console\Commands;


use Registry;
use EntityManager;

use Entities\Measurement;
use Entities\Probe;
use Entities\Request;

use Carbon\Carbon;

class RunMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:run-measurements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run queued measurements';

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
        // find queued measurements:
        foreach( [ 'atlas_out_id', 'atlas_in_id' ] as $id ) {
            if( !( $measurements = Registry::getRepository('Entities\Measurement')->findBy( [$id => null ] ) ) ) {
                if( $this->isVerbose() ) {
                    $this->info("No queued measurements to process ({$id})");
                }
                continue;
            }

            foreach( $measurements as $m ) {
                $this->process( $m );
            }
        }
    }

    private function process( Measurement $m ) {
        $getAddress = $m->getRequest()->getProtocol() == 4 ? 'getV4Address' : 'getV6Address';
        $getASN     = $m->getRequest()->getProtocol() == 4 ? 'getV4asn'     : 'getV6asn';

        $sprobe = ($m->getRequest()->getNetwork()->getProbesByProtocol( $m->getRequest()->getProtocol() ))[0];
        $dprobe = ($m->getDestinationNetwork()->getProbesByProtocol($m->getRequest()->getProtocol() ))[0];

        $sourceIP = $sprobe->$getAddress();
        $sourceAS = $m->getRequest()->getNetwork()->$getASN();
        $targetIP = $dprobe->$getAddress();
        $targetAS = $dprobe->getNetwork()->$getASN();

        if( $this->isVerbose() ) {
            $this->info( "Requesting measurement for {$m->getRequest()->getNetwork()->getName()} / {$m->getDestinationNetwork()->getName()}: {$sourceAS}/{$targetIP} and {$targetAS}/{$sourceIP}" );
        }

        if( !$m->getAtlasOutId() && ( $id = $this->requestAtlasTraceroute($sourceAS,$targetIP,$m->getRequest()->getProtocol()) ) ) {
            $m->setAtlasOutId( $id );
            $m->setAtlasOutStart( new Carbon );
        }

        if( !$m->getAtlasInId()  && ( $id = $this->requestAtlasTraceroute($targetAS,$sourceIP,$m->getRequest()->getProtocol()) ) ) {
            $m->setAtlasInId( $id );
            $m->setAtlasInStart(  new Carbon );
        }

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

    private function requestAtlasTraceroute( $fromASN, $target, $protocol ) {
        $uri = 'https://atlas.ripe.net/api/v1/measurement/?key=' . env('ATLAS_CREATE_MEASUREMENT_KEY');

        $query = [
            'definitions' => [ [
                'target'      => $target,
                'description' => 'IXP Asymmentric routing detector',
                'type'        => 'traceroute',
                'af'          => $protocol,
                'protocol'    => 'ICMP',
                'is_oneoff'   => true,
                'is_public'   => true
            ] ],
            'probes' => [ [
                'requested' => 1,
                'type' => 'asn',
                'value' => $fromASN
            ] ]
        ];

        // use key 'http' even if you send the request to https://...
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json",
                'method'  => 'POST',
                'content' => json_encode($query)
            ]
        ];

        $context = stream_context_create($options);

        try {
            $result = file_get_contents($uri, false, $context);
            $response = json_decode($result);
            return $response->measurements[0];
        } catch( \Exception $e ) {
            if( $this->isVerbose() ) {
                $this->error( "  - FAILED: " . json_encode( $query ) );
            }

            return null;
        }
    }
}
