<?php

namespace App\Console\Commands;

use Illuminate\Console\Command as LaravelCommand;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends LaravelCommand
{

    public function isVerbose() {
        return $this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    protected function atlasStopMeasurement( $id ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://atlas.ripe.net/api/v2/measurement/" . $id . "/?key=" . env('ATLAS_STOP_MEASUREMENT_KEY') );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
