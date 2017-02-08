<?php

namespace App\Console\Commands;

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
        if( $this->isVerbose() ) { $this->info("---- PROBES UPDATE START ----"); }

        // iterate over networks
        foreach( Registry::getRepository('Entities\Network')->findAll() as $network ) {
            foreach( [ 4, 6 ] as $protocol ) {
                // get the network's probes from RIPE Atlas
                $probes = $this->queryAtlasForProbes( $network->getAsn(), $protocol );

                // fn names for later:
                $fnGet = "getV{$protocol}Enabled";
                $fnSet = "setV{$protocol}Enabled";
                $fnIpSet = "setV{$protocol}Address";

                if( $probes === false ) {
                    $this->error("Probe Atlas API request failed for {$network->getName()}/IPv{$protocol}" );
                } else if( sizeof( $probes->results ) == 0 ) {
                    // no probes - delete any if they exist
                    foreach( $network->getProbes() as $p ) {
                        if( $p->$fnGet() ) {
                            $p->$fnSet(false);
                            $this->comment("Removed 'gone away' probe {$p->getAtlasId()} for {$network->getName()} - IPv{$protocol}" );
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
                                break;
                            }
                        }

                        if( !isset( $p ) || $p->getAtlasId() != $probe->id ) {
                            // probe not in database
                            $p = new Probe;
                            $p->setNetwork( $network );
                            $p->setAtlasId( $probe->id );

                            // default to no support
                            $p->setV4Enabled( false );
                            $p->setV6Enabled( false );

                            EntityManager::persist( $p );

                            $this->info("Adding probe {$p->getAtlasId()} for {$network->getName()} - IPv{$protocol}" );
                        }

                        // record status before we change it
                        $old = $p->$fnGet();

                        // so we have a probe that we found by searching the V6 ASN. Let's say v6 is supported unless
                        // we later find otherwise.
                        $p->$fnSet( true );
                        $key = 'address_v' . $protocol;
                        $p->$fnIpSet( $probe->$key );

                        // now make sure it /really/ works
                        foreach( $probe->tags as $tag ) {
                            if( $tag->slug == "system-ipv{$protocol}-doesnt-work" ) {
                                $p->$fnSet( false );
                                break;
                            }
                        }

                        if( $old != $p->$fnGet() ) {
                            $this->comment("Updated probe {$p->getAtlasId()} for {$network->getName()} - IPv{$protocol}" );
                        }
                    } // foreach found probe

                } // if have probes
            } // protocols

            EntityManager::flush();
        } // networks
        if( $this->isVerbose() ) { $this->info("---- PROBES UPDATE STOP ----"); }
    }


    private function queryAtlasForProbes( $asn, $protocol ) {
        $json = file_get_contents( sprintf( "https://atlas.ripe.net/api/v2/probes/?asn_v%d=%d&is_public=true&status=1", $protocol, $asn ) );

        if( $json && strlen( $json ) ) {
            return json_decode( $json );
        }
        return $json;
    }
}
