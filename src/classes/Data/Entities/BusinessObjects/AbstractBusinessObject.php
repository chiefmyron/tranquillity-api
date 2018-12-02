<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository as BusinessObjectRepository;

abstract class AbstractBusinessObject extends AbstractEntity {
    // Entity properties
    protected $id;
    protected $version;
    protected $type;
    protected $subType;
    protected $deleted;
    protected $locks;

    // Related extension data objects
    protected $audit;
    protected $tagCollection;

    // Define the set of fields that are publically accessible
    protected $entityPublicFields = array(
        'id',
        'version',
        'type',
        'subType',
        'deleted',
        'locks'
    );

    /**
     * Create a new instance of the entity
     *
     * @var array $data     [Optional] Initial values for entity fields
     * @var array $options  [Optional] Configuration options for the object
     * @return void
     */
    public function __construct($data = array(), $options = array()) {
        // Set values for valid properties
        parent::__construct($data, $options);

        // Ensure version and deleted properties are initialised
        if (!isset($this->version)) {
            $this->version = 1;
        }
        if (!isset($this->deleted)) {
            $this->deleted = 0;
        }
    }

    /**
     * Set the audit trail details for an entity
     *
     * @param AuditTrail $audit
     * @return void
     */
    protected function _setAudit($audit) {
        if (!($audit instanceof AuditTrail)) {
            throw new \Exception('Audit trail information must be provided as a Tranquility\Data\Entities\Extensions\AuditTrailExtension object');
        }
        
        $this->audit = $audit;
    }
    
    /**
     * Retrieve audit trail details for the entity as an array
     *
     * @return Tranquility\Data\Entities\Extensions\AuditTrailExtension
     */
    protected function _getAudit() {
        return $this->audit;
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     * @abstract
     */
    abstract public function getPublicFields();

    /**
     * Returns the name of the class used to model the historical records for this business object
     *
     * @return string
     */
    abstract public static function getHistoricalEntityClass();

    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        // Define table name
        $builder->setTable('entity');
        $builder->setCustomRepositoryClass(BusinessObjectRepository::class);
        
        // Define inheritence
        $builder->setJoinedTableInheritance();
        $builder->setDiscriminatorColumn('type');
        $builder->addDiscriminatorMapClass(EntityTypeEnum::User, User::class);

        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Person, Person::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Account, Account::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Address, Address::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::AddressPhysical, AddressPhysical::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('version', 'integer');
        $builder->addField('deleted', 'boolean');
        
        // Add relationships
        $builder->createOneToOne('audit', AuditTrail::class)->addJoinColumn('transactionId','transactionId')->build();
        //$builder->createManyToMany('tags', Tag::class)->inversedBy('entities')->setJoinTable('entity_tags_xref')->addJoinColumn('entityId', 'id')->addInverseJoinColumn('tagId', 'id')->build();
        //$builder->createManyToMany('relatedEntities', BusinessObject::class)->setJoinTable('entity_entity_xref')->addJoinColumn('parentId', 'id')->addInverseJoinColumn('childId', 'id')->build();
    }
}