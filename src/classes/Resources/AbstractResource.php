<?php namespace Tranquility\Resources;

// Validation classes
use Valitron\Validator as Validator;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility data entities
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;
use Tranquility\System\Enums\TransactionSourceEnum as TransactionSourceEnum;

abstract class AbstractResource {
    /**
     * Doctrine Entity Manager
     * 
     * @var Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Array of validation rules used to validate the data entity associated with the resource
     * 
     * @var array
     */
    protected $validationRuleGroups = array();

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $prefix  String to use as database table name prefix
     * @return void
     */
    public function __construct(EntityManagerInterface $em) {
        // Create entity manager for interface to repositories and entities
        $this->entityManager = $em;
    }

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Define standard validation rules that are required for all entities
        /*$this->validationRuleGroups['default'][] = array('field' => 'updateDateTime',    'ruleType' => 'required',     'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'updateDateTime',    'ruleType' => 'dateFormat',   'message' => MessageCodes::ValidationInvalidDateTimeFormat, 'params' => ['Y-m-d H:i:s']);
        $this->validationRuleGroups['default'][] = array('field' => 'transactionSource', 'ruleType' => 'required',     'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'transactionSource', 'ruleType' => 'in',           'message' => MessageCodes::ValidationInvalidTransactionSource, 'params' => [TransactionSourceEnum::getValues()]);
        $this->validationRuleGroups['default'][] = array('field' => 'updateUserId'     , 'ruleType' => 'entityExists', 'message' => MessageCodes::ValidationInvalidAuditTrailUser, 'params' => [User::class]);*/
    }

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @abstract
     * @return string
     */
    abstract public function getEntityClassname();

    /**
     * Validate a data array against the defined rules for the resource
     * 
     * @param  array  $data
     * @param  array  $groups  The set of validation groups to use when validating. Runs rules in the 'default' group unless otherwise specified.
     * @return mixed  True if valid, an array of messages if invalid
     */
    public function validate($data, $groups = array('default')) {
        // Create validator instance for the input data
        $validator = new Validator($data);
        
        // Get rules from the specified validation groups
        $rules = array();
        foreach ($groups as $group) {
            if (isset($this->validationRuleGroups[$group])) {
                $rules = array_merge($rules, $this->validationRuleGroups[$group]);
            }
        }

        // Add validation rules
        foreach ($rules as $rule) {
            $params = Utility::extractValue($rule, 'params', array());
            if (is_array($params)) {
                // Handle multiple parameters for a validation rule
                $params = array_merge(array($rule['ruleType'], $rule['field']), $params);
                $validationRule = call_user_func_array(array($validator, 'rule'), $params);
            } else if (is_null($params)) {
                // No parameters
                $validationRule = $validator->rule($rule['ruleType'], $rule['field']);
            }
            
            // Add message to rule
            if (isset($rule['message'])) {
                $validationRule->message($rule['message']);
            }
        }

        // Perform the validation
        $result = $validator->validate();

        // If validation fails, create the error response
        if ($result === false) {
            $errors = $validator->errors();
            $errorCollection = array();
            foreach ($errors as $field => $messages) {
                foreach ($messages as $code) {
                    // Get message details for error code
                    $messageDetails = MessageCodes::getMessageDetails($code);

                    // Build JSON API compliant error                     
                    $errorDetail = array();
                    $errorDetail['source'] = ["pointer" => "/data/attributes/".$field];
                    $errorDetail['status'] = $messageDetails['httpStatusCode'];
                    $errorDetail['code'] = $code;
                    $errorDetail['title'] = $messageDetails['titleMessage'];
                    if ($messageDetails['detailMessage'] != '') {
                        $errorDetail['detail'] = $messageDetails['detailMessage'];
                    }
                    $errorCollection[] = $errorDetail;
                }
            }
            return $errorCollection;
        }

        // Validation has passed, return true
        return true;
    }

    /**
	 * Perform a text search on the entity
	 *
	 * @param  mixed  $searchTerm        Either a search term string, or an array of search term strings
	 * @param  array  $orderConditions   Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage    If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex  Index of the record to start the result set from. Defaults to zero.
	 * @return array
	 */
	public function search($searchTerms, $orderConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0) {
		$fields = $this->_getSearchableFields();

		// Handle multiple search terms
		if (is_string($searchTerms)) {
			$searchTerms = array($searchTerms);
		}

		// Set up search terms
		$filterConditions = array();
		foreach ($fields as $fieldName) {
			foreach ($searchTerms as $term) {
				$filterConditions[] = array($fieldName, 'LIKE', '%'.$term.'%', 'OR');
			}
        }

		return $this->all($filterConditions, $orderConditions, $resultsPerPage, $startRecordIndex);
	}

    /**
	 * Retrieve all entities of this type
	 *
	 * @param  array  $filter            Used to specify additional filters to the set of results
	 * @param  array  $order             Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage    If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex  Index of the record to start the result set from. Defaults to zero.
	 * @return array
	 */
	public function all($filterConditions = array(), $orderConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0) {
		// If a 'deleted' filter has not been specified, default to select only records that have not been deleted
		/*$deleted = $this->_checkForFilterCondition($filterConditions, 'deleted');
		if ($deleted === false) {
			$filterConditions[] = array('deleted', '=', 0);
		}*/
				
        // Retrieve list of entities from repository
        $results = $this->getRepository()->all($filterConditions, $orderConditions, $resultsPerPage, $startRecordIndex);
        return $results;
    }

    /**
	 * Find a single entity by ID
	 *
	 * @param  int  $id  Entity ID of the object to retrieve
	 * @return Tranquility\Data\Entities\AbstractEntity
	 */
	public function find($id) {
        return $this->findBy('id', $id);
    }
    
    /**
     * Find one or more entities by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @return Tranquility\Data\Entities\AbstractEntity
     */
	public function findBy($fieldName, $fieldValue) {
        $searchOptions = array($fieldName => $fieldValue);

        // Retrieve entity from repository
        $entities = $this->getRepository()->findBy($searchOptions);
		return $entities;
    }

    /**
     * Find a single entity by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @return Tranquility\Data\Entities\AbstractEntity
     */
	public function findOneBy($fieldName, $fieldValue) {
        $searchOptions = array($fieldName => $fieldValue);

        // Retrieve entity from repository
        $entity = $this->getRepository()->findOneBy($searchOptions);
		return $entity;
    }
    
    /**
     * Create a new record for an entity
     * 
     * @var  array       $data   Data used to create the new entity record
     * @var  AuditTrail  $audit  Audit trail object 
     * @return Tranquility\Data\Entities\AbstractEntity
     */
    public function create(array $data, AuditTrail $audit) {
        // Get input attributes from data
        $attributes = $data['attributes'];

        // Validate input
        $validationRuleGroups = array('default', 'create');
        $result = $this->validate($attributes, $validationRuleGroups);
        if ($result === true) {
            // Data is valid - create the entity
            $entity = $this->getRepository()->create($attributes, $audit);
            return $entity;
        } else {
            // Data is not valid - return error messages
            return $result;
        }
    }

    /**
     * Update an existing record for the specified entity
     * 
     * @var  int         $id     Record ID for the entity to update
     * @var  array       $data   New data to update against the existing record
     * @var  AuditTrail  $audit  Audit trail object 
     * @return  Tranquility\Data\Entities\AbstractEntity
     */
    public function update(int $id, array $data, AuditTrail $audit) {
        // Validate input
        $validationRuleGroups = array('default', 'update');
        $result = $this->validate($data, $validationRuleGroups);
        if ($result === true) {
            // Data is valid - update the entity
            $entity = $this->getRepository()->update($id, $data, $audit);
            return $entity;
        } else {
            // Data is not valid - return error messages
            return $result;
        }
    }

    /**
     * Mark an existing entity record as deleted
     * 
     * @var  int    $id    Record ID for the entity to delete
     * @var  array  $data  Audit trail details to be attached to the deleted record
     * @var  AuditTrail  $audit  Audit trail object 
     * @return boolean
     */
    public function delete(int $id, array $data, AuditTrail $audit) {
        // Validate input
        $validationRuleGroups = array('default', 'delete');
        $result = $this->validate($data, $validationRuleGroups);
        if ($result === true) {
            // Data is valid - delete the entity
            $entity = $this->getRepository()->delete($id, $data, $audit);
            return $entity;
        } else {
            // Data is not valid - return error messages
            return $result;
        }
    }

    /**
     * Get the Repository associated with the Entity for this resource
     * 
     * @return Tranquility\Data\Repositories\Repository
     */
    protected function getRepository() {
        return $this->entityManager->getRepository($this->getEntityClassname());
    }
}