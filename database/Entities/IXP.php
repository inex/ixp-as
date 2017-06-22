<?php

namespace Entities;

/**
 * IXP
 */
class IXP
{
/**
 * @var string
 */
private $name;

/**
 * @var string
 */
private $shortname;

/**
 * @var string
 */
private $country;

/**
 * @var integer
 */
private $id;

/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $LANs;

/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $networks;

/**
 * Constructor
 */
public function __construct()
{
$this->LANs = new \Doctrine\Common\Collections\ArrayCollection();
$this->networks = new \Doctrine\Common\Collections\ArrayCollection();
}

/**
 * Set name
 *
 * @param string $name
 *
 * @return IXP
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
 * Set shortname
 *
 * @param string $shortname
 *
 * @return IXP
 */
public function setShortname($shortname)
{
$this->shortname = $shortname;

return $this;
}

/**
 * Get shortname
 *
 * @return string
 */
public function getShortname()
{
return $this->shortname;
}

/**
 * Set country
 *
 * @param string $country
 *
 * @return IXP
 */
public function setCountry($country)
{
$this->country = $country;

return $this;
}

/**
 * Get country
 *
 * @return string
 */
public function getCountry()
{
return $this->country;
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
 * Add lAN
 *
 * @param \Entities\LAN $lAN
 *
 * @return IXP
 */
public function addLAN(\Entities\LAN $lAN)
{
$this->LANs[] = $lAN;

return $this;
}

/**
 * Remove lAN
 *
 * @param \Entities\LAN $lAN
 */
public function removeLAN(\Entities\LAN $lAN)
{
$this->LANs->removeElement($lAN);
}

/**
 * Get lANs
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getLANs()
{
return $this->LANs;
}

/**
 * Add network
 *
 * @param \Entities\Network $network
 *
 * @return IXP
 */
public function addNetwork(\Entities\Network $network)
{
$this->networks[] = $network;

return $this;
}

/**
 * Remove network
 *
 * @param \Entities\Network $network
 */
public function removeNetwork(\Entities\Network $network)
{
$this->networks->removeElement($network);
}

/**
 * Get networks
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getNetworks()
{
return $this->networks;
}
/**
 * @var \DateTime
 */
private $created;

/**
 * @var \DateTime
 */
private $lastupdated;


/**
 * Set created
 *
 * @param \DateTime $created
 *
 * @return IXP
 */
public function setCreated($created)
{
$this->created = $created;

return $this;
}

/**
 * Get created
 *
 * @return \DateTime
 */
public function getCreated()
{
return $this->created;
}

/**
 * Set lastupdated
 *
 * @param \DateTime $lastupdated
 *
 * @return IXP
 */
public function setLastupdated($lastupdated)
{
$this->lastupdated = $lastupdated;

return $this;
}

/**
 * Get lastupdated
 *
 * @return \DateTime
 */
public function getLastupdated()
{
return $this->lastupdated;
}
/**
 * @var integer
 */
private $ixf_id;


/**
 * Set ixfId
 *
 * @param integer $ixfId
 *
 * @return IXP
 */
public function setIxfId($ixfId)
{
$this->ixf_id = $ixfId;

return $this;
}

/**
 * Get ixfId
 *
 * @return integer
 */
public function getIxfId()
{
return $this->ixf_id;
}
/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $request;


/**
 * Add request
 *
 * @param \Entities\Request $request
 *
 * @return IXP
 */
public function addRequest(\Entities\Request $request)
{
$this->request[] = $request;

return $this;
}

/**
 * Remove request
 *
 * @param \Entities\Request $request
 */
public function removeRequest(\Entities\Request $request)
{
$this->request->removeElement($request);
}

/**
 * Get request
 *
 * @return \Doctrine\Common\Collections\Collection
 */
public function getRequest()
{
return $this->request;
}
/**
 * @var string
 */
private $organisation;


/**
 * Set organisation
 *
 * @param string $organisation
 *
 * @return IXP
 */
public function setOrganisation($organisation)
{
$this->organisation = $organisation;

return $this;
}

/**
 * Get organisation
 *
 * @return string
 */
public function getOrganisation()
{
return $this->organisation;
}
}
