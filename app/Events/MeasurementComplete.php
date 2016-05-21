<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use Entities\Measurement;

class MeasurementComplete extends Event
{
    use SerializesModels;

    // measurement ORM entity
    public $measurement;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( Measurement $measurement )
    {
        $this->measurement = $measurement;
    }

}
