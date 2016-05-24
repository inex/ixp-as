<?php

namespace App\Listeners;

use App\Events\MeasurementComplete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Entities\Measurement;

use EntityManager;

use App\Interpretor\Basic as BasicInterpretor;

class MeasurementCompleteListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MeasurementComplete  $event
     * @return void
     */
    public function handle(MeasurementComplete $event)
    {
        // get the measurement ORM entity
        $m = $event->measurement;

        // if there's already a result, delete it as this is a rerun
        if( $r = $m->getResult() ) {
            $m->setResult(null);
            EntityManager::remove($r);
            EntityManager::flush();
        }

        // interpret the measurement
        $interpretor = new BasicInterpretor($m);
        $result = $interpretor->interpret();
        EntityManager::persist($result);
        $result->setMeasurement( $m );

        EntityManager::flush();
    }
}
