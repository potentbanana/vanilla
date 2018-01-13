<?php
/**
 * Created by PhpStorm.
 * User: William Cliffe
 * Date: 1/12/2018
 * Time: 3:22 PM
 */

namespace Vanilla\Persist;


abstract class AbstractPersist
{
    /**
     * @var String
     */
    private $lastError;

    /**
     * @return String
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param $error
     */
    public function setLastError($error)
    {
        $this->lastError = $error;
    }


}