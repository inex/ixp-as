<?php

namespace Entities;

/**
 * Probe
 */
class Probe
{
/**
 * @var integer
 */
private $atlas_id;

/**
 * @var boolean
 */
private $v4_enabled;

/**
 * @var boolean
 */
private $v6_enabled;

/**
 * @var integer
 */
private $id;

/**
 * @var \Entities\Network
 */
private $network;


/**
 * Set atlasId
 *
 * @param integer $atlasId
 *
 * @return Probe
 */
public function setAtlasId($atlasId)
{
$this->atlas_id = $atlasId;

return $this;
}

/**
 * Get atlasId
 *
 * @return integer
 */
public function getAtlasId()
{
return $this->atlas_id;
}

/**
 * Set v4Enabled
 *
 * @param boolean $v4Enabled
 *
 * @return Probe
 */
public function setV4Enabled($v4Enabled)
{
$this->v4_enabled = $v4Enabled;

return $this;
}

/**
 * Get v4Enabled
 *
 * @return boolean
 */
public function getV4Enabled()
{
return $this->v4_enabled;
}

/**
 * Set v6Enabled
 *
 * @param boolean $v6Enabled
 *
 * @return Probe
 */
public function setV6Enabled($v6Enabled)
{
$this->v6_enabled = $v6Enabled;

return $this;
}

/**
 * Get v6Enabled
 *
 * @return boolean
 */
public function getV6Enabled()
{
return $this->v6_enabled;
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
 * @return Probe
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
 * @var string
 */
private $v4_address;

/**
 * @var string
 */
private $v6_address;


/**
 * Set v4Address
 *
 * @param string $v4Address
 *
 * @return Probe
 */
public function setV4Address($v4Address)
{
$this->v4_address = $v4Address;

return $this;
}

/**
 * Get v4Address
 *
 * @return string
 */
public function getV4Address()
{
return $this->v4_address;
}

/**
 * Set v6Address
 *
 * @param string $v6Address
 *
 * @return Probe
 */
public function setV6Address($v6Address)
{
$this->v6_address = $v6Address;

return $this;
}

/**
 * Get v6Address
 *
 * @return string
 */
public function getV6Address()
{
return $this->v6_address;
}
}
