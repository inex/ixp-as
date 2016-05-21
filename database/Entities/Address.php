<?php

namespace Entities;

/**
 * Address
 */
class Address
{
/**
 * @var integer
 */
private $protocol;

/**
 * @var string
 */
private $address;

/**
 * @var integer
 */
private $id;

/**
 * @var \Entities\Network
 */
private $network;

/**
 * @var \Entities\LAN
 */
private $LAN;


/**
 * Set protocol
 *
 * @param integer $protocol
 *
 * @return Address
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
 * Set address
 *
 * @param string $address
 *
 * @return Address
 */
public function setAddress($address)
{
$this->address = $address;

return $this;
}

/**
 * Get address
 *
 * @return string
 */
public function getAddress()
{
return $this->address;
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
 * Set network
 *
 * @param \Entities\Network $network
 *
 * @return Address
 */
public function setNetwork(\Entities\Network $network = null)
{
$this->network = $network;

return $this;
}

/**
 * Get network
 *
 * @return \Entities\Network
 */
public function getNetwork()
{
return $this->network;
}

/**
 * Set lAN
 *
 * @param \Entities\LAN $lAN
 *
 * @return Address
 */
public function setLAN(\Entities\LAN $lAN = null)
{
$this->LAN = $lAN;

return $this;
}

/**
 * Get lAN
 *
 * @return \Entities\LAN
 */
public function getLAN()
{
return $this->LAN;
}
}
