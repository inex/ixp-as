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
        if( $this->isVerbose() ) { $this->info("---- IXP UPDATE START ----"); }

        if( !count(config('ixps')) ) {
            $this->error("No IXPs defined in configs/ixps.php");
            exit -1;
        }

        $ixf_ids_processed = [];

        foreach( config('ixps') as $source ) {

            $importer = new IXPImporter($source);

            if( !in_array( $importer->getData()->version, [ '0.5', '0.6' ] ) ) {
                $this->error( "Cannot import {$source['shortname']} as it has schema version {$importer->getData()->version} and we only support 0.5/0.6" );
            }

            foreach( $importer->getIXPs() as $id => $ixp ) {
                if( !$ixp->ixf_id ) {
                    $this->error( "Cannot import {$source['shortname']} with IXP ID {$ixp->ixp_id} as it has no IXF ID defined" );
                    continue;
                }

                $this->process( $importer, $ixp );
            }

        }
        if( $this->isVerbose() ) { $this->info("---- IXP UPDATE STOP  ----"); }
    }

    private function process( $importer, $ixp ) {
        // does it exist in the database already?
        if( !( $ixe = Registry::getRepository('Entities\IXP')->findOneBy( ['ixf_id' => $ixp->ixf_id ] ) ) ) {
            if( $this->isVerbose() ) {
                $this->info("{$ixp->shortname} does not exist in database -> adding");
            }

            $ixe = new IXP();
            $ixe->setIxfId(     $ixp->ixf_id    );
            $ixe->setCreated(   new Carbon      );
            EntityManager::persist($ixe);
        }

        // update
        $ixe->setName(      $ixp->name      );
        $ixe->setShortname( $ixp->shortname );
        $ixe->setCountry(   $ixp->country   );
        EntityManager::flush();

        $vlans = $this->updateLANs( $importer, $ixp, $ixe );

        $members = $this->updateNetworks( $importer, $ixp, $ixe, $vlans );
    }

    private function updateNetworks( $importer, $ixp, $ixe, $vlans ) {
        foreach( $importer->getMembers() as $member ) {
            // does it exist in the database already?
            if( !( $me = Registry::getRepository('Entities\Network')->findOneBy( [ 'asn' => $member->asnum ] ) ) ) {

                if( $this->isVerbose() ) {
                    $this->info("  - {Network $member->name} does not exist in database -> adding");
                }

                $me = new Network();

                $ixe->addNetwork($me);
                $me->addIXP($ixe);

                $me->setAsn( $member->asnum );

                EntityManager::persist( $me );
            }

            $me->setName( $member->name );

            // Addresses...
            foreach( $member->connection_list as $conn ) {
                if( $conn->ixp_id != $ixp->ixp_id || $conn->state != 'active' ) {
                    continue;
                }

                foreach( $conn->vlan_list as $vl ) {

                    foreach( [ 4, 6 ] as $protocol ) {
                        $af = "ipv{$protocol}";

                        if( !isset( $vl->$af ) ) {
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
                        $a->setAddress( ($vl->$af)->address );
                        EntityManager::persist($a);
                    }
                }
            }
        }

        EntityManager::flush();
    }

    private function updateLANs( $importer, $ixp, $ixe ) {
        $vlans = [];
        foreach( $ixp->vlan as $vlan ) {
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
            }

            EntityManager::flush();

            // indexed by IXP VLAN ID:
            $vlans[$vlan->id] = $vlane;
        }
        return $vlans;
    }
}
