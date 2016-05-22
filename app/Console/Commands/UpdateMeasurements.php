<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        // iterate over in traces
        foreach( Registry::getRepository('Entities\Measurement')->findBy( [ 'atlas_in_stop' => null ] ) as $m ) {
            $this->process( $m, 'In' );
        }

        foreach( Registry::getRepository('Entities\Measurement')->findBy( [ 'atlas_out_stop' => null ] ) as $m ) {
            $this->process( $m, 'Out' );
        }
    }


    private function process( $m, $dir ) {
        $getAtlasIdFn = "getAtlas{$dir}Id";
        $setAtlasStoppedFn = "setAtlas{$dir}Stop";
        $setAtlasDataFn = "setAtlas{$dir}Data";

        $measurement = json_decode( file_get_contents( "https://atlas.ripe.net/api/v1/measurement/" . $m->$getAtlasIdFn() ) );

        if( $measurement->status->name == "Stopped" ) {
            $m->$setAtlasStoppedFn( new Carbon() );
            $m->$setAtlasDataFn( file_get_contents( "https://atlas.ripe.net/api/v1/measurement/" . $m->$getAtlasIdFn() . '/result' ) );
        }

        EntityManager::flush();

        // if both in about out is complete, emit an event
        if( $m->getAtlasInStop() && $m->getAtlasOutStop() ) {
            event( new MeasurementComplete( $m ) );
        }
    }

}
