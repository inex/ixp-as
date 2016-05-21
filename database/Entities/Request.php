<?php

namespace Entities;

/**
 * Request
 */
class Request
{
/**
 * @var string
 */
private $nonce;

/**
 * @var integer
 */
private $protocol;

/**
 * @var \DateTime
 */
private $created;

/**
 * @var \DateTime
 */
private $started;

/**
 * @var \DateTime
 */
private $completed;

/**
 * @var integer
 */
private $id;

/**
 * @var \Entities\Network
 */
private $network;


/**
 * Set nonce
 *
 * @param string $nonce
 *
 * @return Request
 */
public function setNonce($nonce)
{
$this->nonce = $nonce;

return $this;
}

/**
 * Get nonce
 *
 * @return string
 */
public function getNonce()
{
return $this->nonce;
}

/**
 * Set protocol
 *
 * @param integer $protocol
 *
 * @return Request
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
 * Set created
 *
 * @param \DateTime $created
 *
 * @return Request
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
 * Set started
 *
 * @param \DateTime $started
 *
 * @return Request
 */
public function setStarted($started)
{
$this->started = $started;

return $this;
}

/**
 * Get started
 *
 * @return \DateTime
 */
public function getStarted()
{
return $this->started;
}

/**
 * Set completed
 *
 * @param \DateTime $completed
 *
 * @return Request
 */
public function setCompleted($completed)
{
$this->completed = $completed;

return $this;
}

/**
 * Get completed
 *
 * @return \DateTime
 */
public function getCompleted()
{
return $this->completed;
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
 * @return Request
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
}
