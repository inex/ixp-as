<?php

namespace App\Console\Commands;

use Registry;
use EntityManager;

use Carbon\Carbon;

use App\Events\MeasurementComplete;

class UpdateMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:update-measurements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update any pending measurements';

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
        if( $this->isVerbose() ) { $this->info("---- UPDATE MEASUREMENTS START ----"); }
        // find uncompleted measurements:
        foreach( [ 'atlas_in_stop', 'atlas_out_stop' ] as $id ) {
            foreach( Registry::getRepository('Entities\Measurement')->findBy( [ $id => null ] ) as $m ) {
                $this->process( $m, $id == 'atlas_in_stop' ? 'In' : 'Out' );
            }
        }
        if( $this->isVerbose() ) { $this->info("---- UPDATE MEASUREMENTS STOP  ----"); }
    }


    private function process( $m, $dir ) {
        $getAtlasIdFn          = "getAtlas{$dir}Id";
        $setAtlasStoppedFn     = "setAtlas{$dir}Stop";
        $setAtlasDataFn        = "setAtlas{$dir}Data";
        $setAtlasRequestDataFn = "setAtlas{$dir}RequestData";
        $getAtlasRequestDataFn = "getAtlas{$dir}RequestData";
        $setAtlasState         = "setAtlas{$dir}State";

        if( !$m->$getAtlasIdFn() ) {
            return;
        }

        if( $this->isVerbose() ) {
            $this->info( 'Checking result for measurement ' . $m->$getAtlasIdFn() );
        }

        $m->$setAtlasRequestDataFn( file_get_contents( "https://atlas.ripe.net/api/v1/measurement/" . $m->$getAtlasIdFn() ) );
        $measurement = json_decode( $m->$getAtlasRequestDataFn() );

        if( isset( $measurement->status->name ) ) {
            $m->$setAtlasState($measurement->status->name);

            if( $measurement->status->name == "Stopped" ) {
                $m->$setAtlasStoppedFn( new Carbon() );
                $m->$setAtlasDataFn( file_get_contents( "https://atlas.ripe.net/api/v1/measurement/" . $m->$getAtlasIdFn() . '/result' ) );
            } else if( in_array( $measurement->status->name, [ "Failed", "No suitable probes" ] ) ) {
                $m->$setAtlasStoppedFn( new Carbon() );
            }
        }

        EntityManager::flush();

        // if both in about out is complete with data, emit an event
        if( $m->getAtlasInStop() && $m->getAtlasOutStop() && $m->getAtlasInData() && $m->getAtlasOutData() ) {
            if( $this->isVerbose() ) {
                $this->info( 'Emitting measurement complete event for measurement ' . $m->$getAtlasIdFn() );
            }
            event( new MeasurementComplete( $m ) );
        }

        // after an hour, consider outstanding measurements as dead
        $current = Carbon::now();

        if( $m->getAtlasInStart() && !$m->getAtlasInStop() && Carbon::instance( $m->getAtlasInStart() )->diffInHours($current) <= -1 ) {
            if( $this->isVerbose() ) {
                $this->info( 'Expiring in measurement ' . $m->$getAtlasIdFn() );
            }
            $m->setAtlasInStop( new Carbon );
            $m->setAtlasInState('ABANDONNED');
            $this->atlasStopMeasurement( $m->getAtlasInId() );
        }

        if( $m->getAtlasOutStart() && !$m->getAtlasOutStop() && Carbon::instance( $m->getAtlasOutStart() )->diffInHours($current) <= -1 ) {
            if( $this->isVerbose() ) {
                $this->info( 'Expiring out measurement ' . $m->$getAtlasIdFn() );
            }
            $m->setAtlasOutStop( new Carbon );
            $m->setAtlasOutState('ABANDONNED');
            $this->atlasStopMeasurement( $m->getAtlasOutId() );
        }

    }

}
