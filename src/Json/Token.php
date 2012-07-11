<?php

namespace Json;

/**
 *
 */
class Token
{
    public $value;
    public $type;

    /**
     * @param      $type
     * @param null $value
     */
    public function __construct($type, $value = null)
    {
        $this->value = $value;
        $this->type = $type;
    }
}