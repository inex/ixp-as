<?php

namespace Entities;

/**
 * Measurement
 */
class Measurement
{
/**
 * @var integer
 */
private $atlas_out_id;

/**
 * @var integer
 */
private $atlas_in_id;

/**
 * @var \DateTime
 */
private $atlas_out_start;

/**
 * @var \DateTime
 */
private $atlas_out_stop;

/**
 * @var \DateTime
 */
private $atlas_in_start;

/**
 * @var \DateTime
 */
private $atlas_in_stop;

/**
 * @var string
 */
private $atlas_out_data;

/**
 * @var string
 */
private $atlas_in_data;

/**
 * @var integer
 */
private $id;

/**
 * @var \Entities\Result
 */
private $result;

/**
 * @var \Entities\Request
 */
private $request;

/**
 * @var \Entities\Network
 */
private $destinationNetwork;


/**
 * Set atlasOutId
 *
 * @param integer $atlasOutId
 *
 * @return Measurement
 */
public function setAtlasOutId($atlasOutId)
{
$this->atlas_out_id = $atlasOutId;

return $this;
}

/**
 * Get atlasOutId
 *
 * @return integer
 */
public function getAtlasOutId()
{
return $this->atlas_out_id;
}

/**
 * Set atlasInId
 *
 * @param integer $atlasInId
 *
 * @return Measurement
 */
public function setAtlasInId($atlasInId)
{
$this->atlas_in_id = $atlasInId;

return $this;
}

/**
 * Get atlasInId
 *
 * @return integer
 */
public function getAtlasInId()
{
return $this->atlas_in_id;
}

/**
 * Set atlasOutStart
 *
 * @param \DateTime $atlasOutStart
 *
 * @return Measurement
 */
public function setAtlasOutStart($atlasOutStart)
{
$this->atlas_out_start = $atlasOutStart;

return $this;
}

/**
 * Get atlasOutStart
 *
 * @return \DateTime
 */
public function getAtlasOutStart()
{
return $this->atlas_out_start;
}

/**
 * Set atlasOutStop
 *
 * @param \DateTime $atlasOutStop
 *
 * @return Measurement
 */
public function setAtlasOutStop($atlasOutStop)
{
$this->atlas_out_stop = $atlasOutStop;

return $this;
}

/**
 * Get atlasOutStop
 *
 * @return \DateTime
 */
public function getAtlasOutStop()
{
return $this->atlas_out_stop;
}

/**
 * Set atlasInStart
 *
 * @param \DateTime $atlasInStart
 *
 * @return Measurement
 */
public function setAtlasInStart($atlasInStart)
{
$this->atlas_in_start = $atlasInStart;

return $this;
}

/**
 * Get atlasInStart
 *
 * @return \DateTime
 */
public function getAtlasInStart()
{
return $this->atlas_in_start;
}

/**
 * Set atlasInStop
 *
 * @param \DateTime $atlasInStop
 *
 * @return Measurement
 */
public function setAtlasInStop($atlasInStop)
{
$this->atlas_in_stop = $atlasInStop;

return $this;
}

/**
 * Get atlasInStop
 *
 * @return \DateTime
 */
public function getAtlasInStop()
{
return $this->atlas_in_stop;
}

/**
 * Set atlasOutData
 *
 * @param string $atlasOutData
 *
 * @return Measurement
 */
public function setAtlasOutData($atlasOutData)
{
$this->atlas_out_data = $atlasOutData;

return $this;
}

/**
 * Get atlasOutData
 *
 * @return string
 */
public function getAtlasOutData()
{
return $this->atlas_out_data;
}

/**
 * Set atlasInData
 *
 * @param string $atlasInData
 *
 * @return Measurement
 */
public function setAtlasInData($atlasInData)
{
$this->atlas_in_data = $atlasInData;

return $this;
}

/**
 * Get atlasInData
 *
 * @return string
 */
public function getAtlasInData()
{
return $this->atlas_in_data;
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
 * Set result
 *
 * @param \Entities\Result $result
 *
 * @return Measurement
 */
public function setResult(\Entities\Result $result = null)
{
$this->result = $result;

return $this;
}

/**
 * Get result
 *
 * @return \Entities\Result
 */
public function getResult()
{
return $this->result;
}

/**
 * Set request
 *
 * @param \Entities\Request $request
 *
 * @return Measurement
 */
public function setRequest(\Entities\Request $request = null)
{
$this->request = $request;

return $this;
}

/**
 * Get request
 *
 * @return \Entities\Request
 */
public function getRequest()
{
return $this->request;
}

/**
 * Set destinationNetwork
 *
 * @param \Entities\Network $destinationNetwork
 *
 * @return Measurement
 */
public function setDestinationNetwork(\Entities\Network $destinationNetwork = null)
{
$this->destinationNetwork = $destinationNetwork;

return $this;
}

/**
 * Get destinationNetwork
 *
 * @return \Entities\Network
 */
public function getDestinationNetwork()
{
return $this->destinationNetwork;
}
}
