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

    public function testIPv4AsymmetricOut()
    {
        $m = $this->generateIPv4AsymmetricOut();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'IXP_ASYM_OUT');
    }

    public function testIPv4AsymmetricIn()
    {
        $m = $this->generateIPv4AsymmetricIn();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'IXP_ASYM_IN');
    }

    public function testIPv4NonIXP()
    {
        $m = $this->generateIPv4NonIXP();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'NON_IXP');
    }



    public function testIPv6Symmetric()
    {
        $m = $this->generateIPv6Symmetric();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'IXP_SYM');
    }

    public function testIPv6AsymmetricOut()
    {
        $m = $this->generateIPv6AsymmetricOut();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'IXP_ASYM_OUT');
    }

    public function testIPv6AsymmetricIn()
    {
        $m = $this->generateIPv6AsymmetricIn();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'IXP_ASYM_IN');
    }

    public function testIPv6NonIXP()
    {
        $m = $this->generateIPv6NonIXP();

        $interpretor = new App\Interpretor\Basic($m);
        $result = $interpretor->interpret();

        $this->assertTrue($result->getRouting() == 'NON_IXP');
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

    public function generateIPv4AsymmetricOut() {

        $m = $this->generateIPv4Symmetric();

        $out = json_decode( $m->getAtlasOutData() );
        unset( $out[0]->result[2] );
        $m->setAtlasOutData( json_encode( $out ) );

        return $m;
    }

    public function generateIPv4AsymmetricIn() {

        $m = $this->generateIPv4Symmetric();

        $in = json_decode( $m->getAtlasInData() );
        unset( $in[0]->result[6] );
        $m->setAtlasInData( json_encode( $in ) );

        return $m;
    }

    public function generateIPv4NonIXP() {

        $m = $this->generateIPv4Symmetric();

        $in = json_decode( $m->getAtlasInData() );
        unset( $in[0]->result[6] );
        $m->setAtlasInData( json_encode( $in ) );

        $out = json_decode( $m->getAtlasOutData() );
        unset( $out[0]->result[2] );
        $m->setAtlasOutData( json_encode( $out ) );

        return $m;
    }



    public function generateIPv6Symmetric() {

        $n = new Network();
        $n->setName('Viatel');
        $n->setV4ASN('31122');

        $l = new LAN();
        $l->setName('Peering LAN1');
        $l->setProtocol(6);
        $l->setSubnet('2001:7f8:18::');
        $l->setMasklen(64);

        $a = new Address();
        $a->setProtocol(6);
        $a->setAddress('2001:7f8:18::20');
        $a->setNetwork($n);
        $n->addAddress($a);
        $a->setLAN($l);

        $r = new Request();
        $r->setNetwork($n);
        $r->setProtocol(6);

        $on = new Network();
        $on->setName('Eircom');
        $on->setV4ASN('5466');

        $oa = new Address();
        $oa->setProtocol(6);
        $oa->setAddress('2001:7f8:18::4:0:2');
        $oa->setNetwork($on);
        $on->addAddress($oa);
        $oa->setLAN($l);

        $m = new Measurement;
        $m->setRequest($r);
        $m->setDestinationNetwork($on);

        $m->setAtlasOutData( file_get_contents("https://atlas.ripe.net/api/v1/measurement/3809200/result") );
        $m->setAtlasInData( file_get_contents("https://atlas.ripe.net/api/v1/measurement/3809219/result") );

        return $m;

    }

    public function generateIPv6AsymmetricOut() {

        $m = $this->generateIPv6Symmetric();

        $out = json_decode( $m->getAtlasOutData() );
        unset( $out[0]->result[3] );
        $m->setAtlasOutData( json_encode( $out ) );

        return $m;
    }

    public function generateIPv6AsymmetricIn() {

        $m = $this->generateIPv6Symmetric();

        $in = json_decode( $m->getAtlasInData() );
        unset( $in[0]->result[3] );
        $m->setAtlasInData( json_encode( $in ) );

        return $m;
    }


    public function generateIPv6NonIXP() {

        $m = $this->generateIPv6Symmetric();

        $out = json_decode( $m->getAtlasOutData() );
        unset( $out[0]->result[3] );
        $m->setAtlasOutData( json_encode( $out ) );

        $in = json_decode( $m->getAtlasInData() );
        unset( $in[0]->result[3] );
        $m->setAtlasInData( json_encode( $in ) );

        return $m;
    }


}
