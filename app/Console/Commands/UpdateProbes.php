<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Registry;
use EntityManager;

use Entities\Probe;

class UpdateProbes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:update-probes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update probes for each network';

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
        // iterate over networks
        foreach( Registry::getRepository('Entities\Network')->findAll() as $network ) {
            foreach( [ 4, 6 ] as $protocol ) {
                // get the network's probes from RIPE Atlas
                $probes = $this->queryAtlasForProbes( $protocol == 4 ? $network->getV4ASN() : $network->getV6ASN(), $protocol );

                // fn names for later:
                $fnGet = "getV{$protocol}Enabled";
                $fnSet = "getV{$protocol}Enabled";
                $fnIpSet = "setV{$protocol}Address";

                if( $probes === false ) {
                    $this->error("Probe Atlas API request failed for {$network->getName()}/IPv{$protocol}" );
                } else if( sizeof( $probes->results ) == 0 ) {
                    // no probes - delete any if they exist
                    foreach( $network->getProbes() as $p ) {
                        if( $p->$fnGet() ) {
                            $p->fnSet(false);
                        }

                        if( !$p->getV4Enabled() && !$p->getV6Enabled() ) {
                            EntityManager::remove($p);
                        }
                    }
                } else {

                    // have probes -> update/insert
                    foreach( $probes->results as $probe ) {
                        foreach( $network->getProbes() as $p ) {
                            if( $p->getAtlasId() == $probe->id ) {
                                if( !$p->$fnGet() ) {
                                    $p->$fnSet( true );
                                    $key = 'address_v' . $protocol;
                                    $p->fnIpSet( $probe->$key );
                                }
                                continue 2;
                            }
                        }

                        // probe not in database
                        $p = new Probe;
                        $p->setNetwork( $network );
                        $p->setAtlasId( $probe->id );
                        $p->setV4Enabled( $probe->address_v4 != null );
                        $p->setV4Address( $probe->address_v4 );
                        $p->setV6Enabled( $probe->address_v6 != null );
                        $p->setV6Address( $probe->address_v6 );
                        EntityManager::persist( $p );
                    }
                }
            } // protocols

            EntityManager::flush();
        } // networks
    }


    private function queryAtlasForProbes( $asn, $protocol ) {
        $json = file_get_contents( sprintf( "https://atlas.ripe.net/api/v2/probes/?asn_v%d=%d&is_public=true", $protocol, $asn ) );

        if( $json && strlen( $json ) ) {
            return json_decode( $json );
        }
        return $json;
    }
}
