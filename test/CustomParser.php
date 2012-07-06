<?php

/**
 *
 */
class CustomParser
{
    /**
     * @var
     */
    private $_data;

    /**
     * @var
     */
    private $_writePointer;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param $jsonPath
     */
    public function parse($jsonPath)
    {
        $this->_data = array();
        $this->_writePointer = &$this->_data;

        require_once __DIR__ . '/../package/JSONParser.php';
        $parser = new \JSONParser();

        $parser->setPropertyHandler(array($this, 'handlerProperty'));
        $parser->setScalarHandler(array($this, 'handlerScalar'));
        $parser->setArrayHandlers(array($this, 'handlerArrayStart'), array($this, 'handlerArrayEnd'));
        $parser->setObjectHandlers(array($this, 'handlerObjectStart'), array($this, 'handlerObjectEnd'));

        $parser->parseDocument(fopen($jsonPath, 'r'));
    }

    /**
     * @param $value
     */
    public function handlerScalar($value)
    {
        //printf("Scalar: %s<br/>", $value);
        if (is_array($this->_writePointer)) {
            $this->_writePointer[] = $value;
        }
    }

    /**
     * @param $name
     */
    public function handlerProperty($name)
    {
        //printf("Property: %s<br/>", $name);
        $this->_data[$name] = array();
        $this->_writePointer = &$this->_data[$name];
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerArrayStart($value, $property)
    {
        //printf("[<br/>");
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerArrayEnd($value, $property)
    {
        //printf("]<br/>");
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerObjectStart($value, $property)
    {
        //printf("{<br/>");
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerObjectEnd($value, $property)
    {
        //printf("}<br/>");
    }
}