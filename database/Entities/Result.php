<?php

namespace Entities;

/**
 * Result
 */
class Result
{
/**
 * @var string
 */
private $routing;

/**
 * @var string
 */
private $path_out;

/**
 * @var string
 */
private $path_in;

/**
 * @var integer
 */
private $id;

/**
 * @var \Entities\Measurement
 */
private $measurement;


/**
 * Set routing
 *
 * @param string $routing
 *
 * @return Result
 */
public function setRouting($routing)
{
$this->routing = $routing;

return $this;
}

/**
 * Get routing
 *
 * @return string
 */
public function getRouting()
{
return $this->routing;
}

/**
 * Set pathOut
 *
 * @param string $pathOut
 *
 * @return Result
 */
public function setPathOut($pathOut)
{
$this->path_out = $pathOut;

return $this;
}

/**
 * Get pathOut
 *
 * @return string
 */
public function getPathOut()
{
return $this->path_out;
}

/**
 * Set pathIn
 *
 * @param string $pathIn
 *
 * @return Result
 */
public function setPathIn($pathIn)
{
$this->path_in = $pathIn;

return $this;
}

/**
 * Get pathIn
 *
 * @return string
 */
public function getPathIn()
{
return $this->path_in;
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
 * Set measurement
 *
 * @param \Entities\Measurement $measurement
 *
 * @return Result
 */
public function setMeasurement(\Entities\Measurement $measurement = null)
{
$this->measurement = $measurement;

return $this;
}

/**
 * Get measurement
 *
 * @return \Entities\Measurement
 */
public function getMeasurement()
{
return $this->measurement;
}
}

