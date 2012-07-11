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

        $parser = new \Json\Parser();

        $parser->setPropertyHandler(array($this, 'handlerProperty'));
        $parser->setScalarHandler(array($this, 'handlerScalar'));
        $parser->setArrayHandlers(array($this, 'handlerArrayStart'), array($this, 'handlerArrayEnd'));
        $parser->setObjectHandlers(array($this, 'handlerObjectStart'), array($this, 'handlerObjectEnd'));

        $parser->parseDocument($jsonPath);
    }

    /**
     * @param $value
     */
    public function handlerScalar($value)
    {
        //printf("Scalar: %s<br/>", $value);
        if ($this->_jsonArrayStarted) {
            $this->_jsonArray[] = $value;
        }
    }

    /**
     * @param $name
     */
    public function handlerProperty($name)
    {
        //printf("Property: %s<br/>", $name);
        $this->_lastProperty = $name;
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerArrayStart($value, $property)
    {
        //printf("[<br/>");
        $this->_lastProperty = $property;
        $this->_jsonArrayStarted = true;
        $this->_jsonArray = array();
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerArrayEnd($value, $property)
    {
        //printf("]<br/>");
        $this->_jsonArrayStarted = false;
        if ($this->_lastProperty) {
            $this->_data['data'][$this->_lastProperty] = $this->_jsonArray;
        } else {
            $this->_data['data'][] = $this->_jsonArray;
        }
    }

    /**
     * @param $value
     * @param $property
     */
    public function handlerObjectStart($value, $property)
    {
        //printf("{<br/>");
        if ($property == 'data') {
            $this->_data['data'] = array();
        }
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