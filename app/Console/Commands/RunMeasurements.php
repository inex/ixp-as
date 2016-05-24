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
        if( $this->isVerbose() ) { $this->info("---- RUN MEASUREMENTS START ----"); }

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

        if( $this->isVerbose() ) { $this->info("---- RUN MEASUREMENTS STOP  ----"); }
    }

    private function process( Measurement $m ) {
        $getAddress = $m->getRequest()->getProtocol() == 4 ? 'getV4Address' : 'getV6Address';

        $sprobe = ($m->getRequest()->getNetwork()->getProbesByProtocol( $m->getRequest()->getProtocol() ))[0];
        $dprobe = ($m->getDestinationNetwork()->getProbesByProtocol($m->getRequest()->getProtocol() ))[0];

        $sourceIP = $sprobe->$getAddress();
        $sourceAS = $m->getRequest()->getNetwork()->getAsn();
        $targetIP = $dprobe->$getAddress();
        $targetAS = $dprobe->getNetwork()->getAsn();

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
