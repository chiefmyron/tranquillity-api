<?php namespace Tranquility\Data\Entities;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;

abstract class AbstractEntity {
    /**
     * Create a new instance of the entity
     *
     * @var array $data     [Optional] Initial values for entity fields
     * @var array $options  [Optional] Configuration options for the object
     * @return void
     */
    public function __construct($data = array(), $options = array()) {
        // Populate common entity data
        $this->populate($data);
    }

    /**
     * Retrieves the value for an entity field
     * 
     * @param string $name  Field name
     * @throws Exception
     * @return mixed
     */
    public function __get($name) {
        $methodName = '_get'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Use custom function to retrieve value
            return $this->{$methodName}();
        } elseif (in_array($name, $this->getPublicFields())) {
            // Retrieve value directly
            return $this->$name;
        } else {
            throw new \Exception('Cannot get property value - class "'.get_class($this).'" does not have a property named "'.$name.'"');
        }
    }

    /**
     * Set the value for an entity field
     * 
     * @param string $name  Field name
     * @param mixed $value  Field value
     * @throws Exception 
     * @return void
     */
    public function __set($name, $value) {
        $methodName = '_set'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Use custom function to set value
            $this->{$methodName}($value);
        } elseif (in_array($name, $this->getPublicFields())) {
            // Store value directly
            if ($value !== '') {
                $this->$name = $value;
            }
        } else {
            throw new \Exception('Cannot set property - class "'.get_class($this).'" does not have a property named "'.$name.'"');
        }
    }

    /**
     * Checks to see if a value has been set for an entity field
     */
    public function __isset($name) {
        $methodName = '_get'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Check to see if the value returned from the custom getter is null
            $value = $this->{$methodName}();
            if (is_null($value)) {
                return false;
            } else {
                return true;
            }
        } elseif (in_array($name, $this->getPublicFields())){
            return isset($this->name);
        } else {
            return false;
        }
    }

    /**
     * Sets values for entity fields, based on the inputs provided
     * 
     * @param mixed $data  May be an array or an instance of an Entity
     * @return Tranquility\Data\Entities\AbstractEntity
     */
    public function populate($data) {
        if ($data instanceof AbstractEntity) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }
        if (!is_array($data)) {
            throw new \Exception('Initial data must be an array or instance of a Tranquility\Data\Entities\AbstractEntity object');
        }

        // Assign relevant data to the entity fields
        $entityFields = $this->getPublicFields();
        foreach ($entityFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }
        
        return $this;
    }

    /**
     * Convert the entity to an associative array. Only public fields
     * will be made available in the array
     *
     * @return array
     */
    public function toArray() {
        $data = array();
        $entityFields = $this->getPublicFields();
        foreach ($entityFields as $field) {
            if (isset($this->$field)) {
                $data[$field] = $this->$field;
            }
        }

        return $data;
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     * @abstract
     */
    abstract public function getPublicFields();
}