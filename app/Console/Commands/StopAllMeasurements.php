<?php

namespace App\Console\Commands;

class StopAllMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:stop-all-measurements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stops all outstanding measurements for the API key in use';

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
        // get all measurements
        if( $json = json_decode( file_get_contents( 'https://atlas.ripe.net/api/v2/measurements/my/?key=' . env('ATLAS_CREATE_MEASUREMENT_KEY') . '&status=2&page_size=500' ) ) ) {

            $this->info( $json->count . " on going measurements found");

            foreach( $json->results as $m ) {
                $this->atlasStopMeasurement( $m->id );
                $this->info("Stop requested for {$m->id}");
            }

        } else {
            $this->error('Could not query RIPE Atlas API');
        }


    }
}
