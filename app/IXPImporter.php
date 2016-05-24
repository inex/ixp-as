<?php

namespace App;

class IXPImporter
{
    private $source = null;
    private $data   = null;
    private $ixps   = null;

    public function __construct( $source ) {
        $this->source = $source;
        $this->data   = json_decode( file_get_contents( $source['schema'] ) );
    }

    public function getData(){
        return $this->data;
    }

    public function getSource() {
        return $this->source;
    }


    public function getMembers(){
        return $this->data->member_list;
    }

    public function getIXPs() {
        if( $this->ixps ) {
            return $this->ixps;
        }

        foreach( $this->data->ixp_list as $ixp ) {
            $ixp->name      = $ixp->name      ? $ixp->name      : $this->source['name'];
            $ixp->shortname = $ixp->shortname ? $ixp->shortname : $this->source['shortname'];
            $ixp->country   = $ixp->country   ? $ixp->country   : $this->source['country'];
            $ixp->ixf_id    = $ixp->ixf_id    ? $ixp->ixf_id    : null;
        }

        $this->ixps[$ixp->ixp_id] = $ixp;
        return $this->ixps;
    }

}
