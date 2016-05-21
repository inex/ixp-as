<?php

namespace Entities;

/**
 * LAN
 */
class LAN
{
/**
 * @var string
 */
private $name;

/**
 * @var integer
 */
private $protocol;

/**
 * @var string
 */
private $subnet;

/**
 * @var integer
 */
private $masklen;

/**
 * @var integer
 */
private $id;

/**
 * @var \Doctrine\Common\Collections\Collection
 */
private $addresses;

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
}

/**
 * Set name
 *
 * @param string $name
 *
 * @return LAN
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
 * Set protocol
 *
 * @param integer $protocol
 *
 * @return LAN
 */
public function setProtocol($protocol)
{
$this->protocol = $protocol;

return $this;
}

/**
 * Get protocol
 *
 * @return integer
 */
public function getProtocol()
{
return $this->protocol;
}

/**
 * Set subnet
 *
 * @param string $subnet
 *
 * @return LAN
 */
public function setSubnet($subnet)
{
$this->subnet = $subnet;

return $this;
}

/**
 * Get subnet
 *
 * @return string
 */
public function getSubnet()
{
return $this->subnet;
}

/**
 * Set masklen
 *
 * @param integer $masklen
 *
 * @return LAN
 */
public function setMasklen($masklen)
{
$this->masklen = $masklen;

return $this;
}

/**
 * Get masklen
 *
 * @return integer
 */
public function getMasklen()
{
return $this->masklen;
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
 * @return LAN
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
 * Set iXP
 *
 * @param \Entities\IXP $iXP
 *
 * @return LAN
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
}
