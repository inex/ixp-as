<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Entities\Request;
use Entities\Measurement;
use Entities\Network;
use Entities\LAN;
use Entities\Address;


class BasicInterpretorTest extends TestCase
{

    public function testIPv4Symmetric()
    {
        $m = $this->generateIPv4Symmetric();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'IXP_SYM');
    }




    public function generateIPv4Symmetric() {

        $n = new Network();
        $n->setName('Cablecomm');
        $n->setV4ASN('44384');

        $l = new LAN();
        $l->setName('Peering LAN1');
        $l->setProtocol(4);
        $l->setSubnet('193.242.111.0');
        $l->setMasklen(25);

        $a = new Address();
        $a->setProtocol(4);
        $a->setAddress('193.242.111.87');
        $a->setNetwork($n);
        $n->addAddress($a);
        $a->setLAN($l);

        $r = new Request();
        $r->setNetwork($n);
        $r->setProtocol(4);

        $on = new Network();
        $on->setName('Eircom');
        $on->setV4ASN('5466');

        $oa = new Address();
        $oa->setProtocol(4);
        $oa->setAddress('193.242.111.82');
        $oa->setNetwork($on);
        $on->addAddress($oa);
        $oa->setLAN($l);

        $m = new Measurement;
        $m->setRequest($r);
        $m->setDestinationNetwork($on);

        $m->setAtlasOutData( file_get_contents("https://atlas.ripe.net/api/v1/measurement/3806499/result") );
        $m->setAtlasInData( file_get_contents("https://atlas.ripe.net/api/v1/measurement/3806501/result") );

        return $m;

    }


}
