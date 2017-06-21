<?php

namespace App\Console\Commands;

use App\IXPImporter;


use Registry;
use EntityManager;

use Entities\Address;
use Entities\IXP;
use Entities\LAN;
use Entities\Network;

use Carbon\Carbon;

class UpdateIXPs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ixps:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and update IXPs via JSON export schema';

    /**
     * Create a new command instance.
     *
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
        if( $this->isVerbose() ) { $this->info("---- IXP UPDATE START ----"); }

        if( !count(config('ixps')) ) {
            $this->error("No IXPs defined in configs/ixps.php");
            return(-1);
        }

        $ixf_ids_processed = [];

        foreach( config('ixps') as $source ) {

            $importer = new IXPImporter($source);

            if( !in_array( $importer->getData()->version, [ '0.5', '0.6' ] ) ) {
                $this->error( "Cannot import {$source['shortname']} as it has schema version {$importer->getData()->version} and we only support 0.5/0.6" );
                continue;
            }

            foreach( $importer->getIXPs() as $id => $schemaIXP ) {
                if( !$schemaIXP->ixf_id ) {
                    $this->error( "Cannot import {$source['shortname']} with IXP ID {$schemaIXP->ixp_id} as it has no IXF ID defined" );
                    continue;
                }

                if( in_array( $schemaIXP->ixf_id, $ixf_ids_processed ) ) {
                    $this->error( "{$source['shortname']} with IXP ID {$schemaIXP->ixp_id} already imports - defined twice in configs/ixps.php?" );
                    continue;
                }

                $this->process( $importer, $schemaIXP );
                $ixf_ids_processed[] = $schemaIXP->ixf_id;
            }
        }

        if( $this->isVerbose() ) { $this->info("---- IXP UPDATE STOP  ----"); }
        return(0);
    }

    private function process( $importer, $schemaIXP ) {
        // does it exist in the database already?
        if( !( $ixe = Registry::getRepository('Entities\IXP')->findOneBy( ['ixf_id' => $schemaIXP->ixf_id ] ) ) ) {
            if( $this->isVerbose() ) {
                $this->info("{$schemaIXP->shortname} does not exist in database -> adding");
            }

            $ixe = new IXP();
            $ixe->setIxfId(     $schemaIXP->ixf_id    );
            $ixe->setCreated(   new Carbon      );
            EntityManager::persist($ixe);
        }

        // update
        $ixe->setName(      $schemaIXP->name      );
        $ixe->setShortname( $schemaIXP->shortname );
        $ixe->setCountry(   $schemaIXP->country   );
        EntityManager::flush();

        $vlans = $this->updateLANs( $schemaIXP, $ixe );

        $this->updateNetworks( $importer, $schemaIXP, $ixe, $vlans );

        $ixe->setLastUpdated( new Carbon );
        EntityManager::flush();
    }

    /**
     * @param IXPImporter $importer
     * @param object $schemaIXP
     * @param IXP $ixe
     * @param array $vlans
     */
    private function updateNetworks( $importer, $schemaIXP, $ixe, $vlans ) {
        foreach( $importer->getMembers() as $member ) {

            // does it exist in the database already?
            if( !( $me = Registry::getRepository('Entities\Network')->findOneBy( [ 'asn' => $member->asnum ] ) ) ) {

                if( $this->isVerbose() ) {
                    $this->info("  - {Network $member->name} does not exist in database -> adding");
                }

                $me = new Network();
                $me->setAsn( $member->asnum );
                EntityManager::persist( $me );
            }

            $joinedToIXP = false;
            foreach( $me->getIXPs() as $ii ) {
                if( $ii->getId() == $ixe->getId() ) {
                    $joinedToIXP = true;
                    break;
                }
            }

            // should it be?
            $shouldBeJoinedToIXP = false;
            foreach( $member->connection_list as $cl ) {
                if( $cl->ixp_id == $schemaIXP->schemaId ) {
                    $shouldBeJoinedToIXP = true;
                    break;
                }
            }

            if( !$joinedToIXP && $shouldBeJoinedToIXP ) {
                $this->info("  - {Network $member->name} does not exist in IXP -> adding");
                $ixe->addNetwork($me);
                $me->addIXP($ixe);
            } else if( $joinedToIXP && !$shouldBeJoinedToIXP ) {
                $this->info("  - {Network $member->name} exists in IXP but shouldn't -> removing");
                $ixe->removeNetwork($me);
                $me->removeIXP($ixe);
            }

            $me->setName( $member->name );

            // Addresses...
            foreach( $member->connection_list as $conn ) {
                if( $conn->ixp_id != $schemaIXP->schemaId || $conn->state != 'active' ) {
                    continue;
                }

                foreach( $conn->vlan_list as $vl ) {

                    foreach( [ 4, 6 ] as $protocol ) {
                        $af = "ipv{$protocol}";

                        if( !isset( $vl->$af ) ) {
                            // does the address already exist?
                            foreach( $me->getAddresses() as $a ) {
                                if( $a->getProtocol() == $protocol && $a->getLAN()->getIxpVlanId() == $vl->vlan_id ) {
                                    EntityManager::remove($a);
                                    EntityManager::flush();
                                }
                            }
                            continue;
                        }

                        // does the address already exist?
                        foreach( $me->getAddresses() as $a ) {
                            if( $a->getAddress() == $vl->$af->address && $a->getProtocol() == $protocol && $a->getLAN()->getIxpVlanId() == $vl->vlan_id ) {
                                continue 2;
                            }
                        }

                        // is it valid? e.g. ignore private VLANs
                        if( !isset( $vlans[ $vl->vlan_id ] ) ) {
                            continue;
                        }

                        // add address:
                        if( $this->isVerbose() ) {
                            $this->info("  - {Network $member->name} address [{$vl->$af->address}] does not exist in database -> adding");
                        }
                        $a = new Address();
                        $a->setNetwork( $me );
                        $a->setLAN( $vlans[ $vl->vlan_id ] );
                        $a->setProtocol( $protocol );
                        $a->setAddress( $vl->$af->address );
                        EntityManager::persist($a);
                    }
                }
            }
        }

        EntityManager::flush();
    }

    /**
     * @param object $schemaIXP
     * @param IXP $ixe
     * @return array
     */
    private function updateLANs( $schemaIXP, $ixe ) {
        $vlans = [];

        foreach( $schemaIXP->vlan as $vlan ) {

            // does it exist in the database already?
            foreach( [ 4, 6 ] as $protocol ) {
                $af = "ipv{$protocol}";
                if( !isset( $vlan->$af ) ) {
                    continue;
                }

                if( !( $vlane = Registry::getRepository('Entities\LAN')->findOneBy(
                            [ 'IXP' => $ixe, 'ixp_vlan_id' => $vlan->id, 'protocol' => $protocol ] ) ) ) {

                    if( $this->isVerbose() ) {
                        $this->info("  - {VLAN $vlan->name} does not exist in database -> adding");
                    }

                    $vlane = new LAN();
                    $vlane->setIXP(      $ixe );
                    $vlane->setIxpVlanId( $vlan->id );
                    EntityManager::persist( $vlane );
                }

                // update
                $vlane->setName(     $vlan->name );
                $vlane->setProtocol( $protocol );
                $vlane->setSubnet(   $vlan->$af->prefix );
                $vlane->setMasklen(  $vlan->$af->mask_length );

                // indexed by IXP VLAN ID:
                $vlans[$vlan->id] = $vlane;
                EntityManager::flush();
            }
        }

        return $vlans;
    }
}
