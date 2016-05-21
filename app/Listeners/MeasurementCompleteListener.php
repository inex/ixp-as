<?php

namespace App\Listeners;

use App\Events\MeasurementComplete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Entities\Measurement;

use EntityManager;

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

        // interpret the measurement
        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();
        $m->setResult($result);

        EntityManager::persist();
    }
}
