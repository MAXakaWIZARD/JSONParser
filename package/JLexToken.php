<?php

/**
 *
 */
class JLexToken
{
    public $line;
    public $col;
    public $value;
    public $type;

    /**
     * @param      $type
     * @param null $value
     * @param null $line
     * @param null $col
     */
    public function __construct($type, $value = null, $line = null, $col = null)
    {
        $this->line = $line;
        $this->col = $col;
        $this->value = $value;
        $this->type = $type;
    }
}