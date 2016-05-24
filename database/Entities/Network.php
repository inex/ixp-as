<?php

namespace Entities;

/**
 * Network
 */
class Network
{
/**
 * @var string
 */
private $name;

/**
 * @var integer
 */
private $v4asn;

/**
 * @var integer
 */
private $v6asn;

/**
 * @var integer
 */
private $id;

/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $addresses;

/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $probes;

/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $requests;

/**
 * @var \Entities\IXP
 */
private $IXP;

/**
 * Constructor
 */
public function __construct()
{
$this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
$this->probes = new \Doctrine\Common\Collections\ArrayCollection();
$this->requests = new \Doctrine\Common\Collections\ArrayCollection();
}

/**
 * Set name
 *
 * @param string $name
 *
 * @return Network
 */
public function setName($name)
{
$this->name = $name;

return $this;
}

/**
 * Get name
 *
 * @return string
 */
public function getName()
{
return $this->name;
}

/**
 * Set v4asn
 *
 * @param integer $v4asn
 *
 * @return Network
 */
public function setV4asn($v4asn)
{
$this->v4asn = $v4asn;

return $this;
}

/**
 * Get v4asn
 *
 * @return integer
 */
public function getV4asn()
{
return $this->v4asn;
}

/**
 * Set v6asn
 *
 * @param integer $v6asn
 *
 * @return Network
 */
public function setV6asn($v6asn)
{
$this->v6asn = $v6asn;

return $this;
}

/**
 * Get v6asn
 *
 * @return integer
 */
public function getV6asn()
{
return $this->v6asn;
}

/**
 * Get id
 *
 * @return integer
 */
public function getId()
{
return $this->id;
}

/**
 * Add address
 *
 * @param \Entities\Address $address
 *
 * @return Network
 */
public function addAddress(\Entities\Address $address)
{
$this->addresses[] = $address;

return $this;
}

/**
 * Remove address
 *
 * @param \Entities\Address $address
 */
public function removeAddress(\Entities\Address $address)
{
$this->addresses->removeElement($address);
}

/**
 * Get addresses
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getAddresses()
{
return $this->addresses;
}

/**
 * Add probe
 *
 * @param \Entities\Probe $probe
 *
 * @return Network
 */
public function addProbe(\Entities\Probe $probe)
{
$this->probes[] = $probe;

return $this;
}

/**
 * Remove probe
 *
 * @param \Entities\Probe $probe
 */
public function removeProbe(\Entities\Probe $probe)
{
$this->probes->removeElement($probe);
}

/**
 * Get probes
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getProbes()
{
return $this->probes;
}

/**
 * Add request
 *
 * @param \Entities\Request $request
 *
 * @return Network
 */
public function addRequest(\Entities\Request $request)
{
$this->requests[] = $request;

return $this;
}

/**
 * Remove request
 *
 * @param \Entities\Request $request
 */
public function removeRequest(\Entities\Request $request)
{
$this->requests->removeElement($request);
}

/**
 * Get requests
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getRequests()
{
return $this->requests;
}

/**
 * Set iXP
 *
 * @param \Entities\IXP $iXP
 *
 * @return Network
 */
public function setIXP(\Entities\IXP $iXP = null)
{
$this->IXP = $iXP;

return $this;
}

/**
 * Get iXP
 *
 * @return \Entities\IXP
 */
public function getIXP()
{
return $this->IXP;
}
/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $measurements;


/**
 * Add measurement
 *
 * @param \Entities\Measurement $measurement
 *
 * @return Network
 */
public function addMeasurement(\Entities\Measurement $measurement)
{
$this->measurements[] = $measurement;

return $this;
}

/**
 * Remove measurement
 *
 * @param \Entities\Measurement $measurement
 */
public function removeMeasurement(\Entities\Measurement $measurement)
{
$this->measurements->removeElement($measurement);
}

/**
 * Get measurements
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getMeasurements()
{
return $this->measurements;
}


/**
 * Get probes
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getProbesByProtocol($protocol)
{
    $enabled = $protocol == 4 ? 'getV4Enabled' : 'getV6Enabled';

    $probes = [];
    foreach( $this->getProbes() as $probe ) {
        if( $probe->$enabled() ) {
            $probes[] = $probe;
        }
    }

    return $probes;
}


/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $IXPs;


/**
 * Add iXP
 *
 * @param \Entities\IXP $iXP
 *
 * @return Network
 */
public function addIXP(\Entities\IXP $iXP)
{
$this->IXPs[] = $iXP;

return $this;
}

/**
 * Remove iXP
 *
 * @param \Entities\IXP $iXP
 */
public function removeIXP(\Entities\IXP $iXP)
{
$this->IXPs->removeElement($iXP);
}

/**
 * Get iXPs
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getIXPs()
{
return $this->IXPs;
}
/**
 * @var integer
 */
private $asn;

/**
 * @var boolean
 */
private $v4_available;

/**
 * @var boolean
 */
private $v6_available;


/**
 * Set asn
 *
 * @param integer $asn
 *
 * @return Network
 */
public function setAsn($asn)
{
$this->asn = $asn;

return $this;
}

/**
 * Get asn
 *
 * @return integer
 */
public function getAsn()
{
return $this->asn;
}

/**
 * Set v4Available
 *
 * @param boolean $v4Available
 *
 * @return Network
 */
public function setV4Available($v4Available)
{
$this->v4_available = $v4Available;

return $this;
}

/**
 * Get v4Available
 *
 * @return boolean
 */
public function getV4Available()
{
return $this->v4_available;
}

/**
 * Set v6Available
 *
 * @param boolean $v6Available
 *
 * @return Network
 */
public function setV6Available($v6Available)
{
$this->v6_available = $v6Available;

return $this;
}

/**
 * Get v6Available
 *
 * @return boolean
 */
public function getV6Available()
{
return $this->v6_available;
}

    // oh such a fucking hack
    // FIXME we're killing databases here :-(
    public function hasProtocolAtIXP( $ixp, $protocol ) {
        foreach( $this->getAddresses() as $a ) {
            if( $a->getProtocol() != $protocol ) {
                continue;
            }

            if( $a->getLAN()->getIXP()->getId() == $ixp->getId() ) {
                return true;
            }
        }

        return false;
    }


}
