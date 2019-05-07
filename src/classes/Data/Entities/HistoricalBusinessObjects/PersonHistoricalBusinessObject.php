<?php namespace Tranquility\Data\Entities\HistoricalBusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;

// Entity repository
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository;

class PersonHistoricalBusinessObject extends AbstractHistoricalBusinessObject {
    // Entity type
    protected $type = EntityTypeEnum::Person;

    // Entity properties
    protected $title;
    protected $firstName;
    protected $lastName;
    protected $position;

    // Related entities
    protected $user;

    // Define the set of fields that are publically accessible
    protected static $publicFields = array(
        'title',
        'firstName',
        'lastName',
        'position'
    );
    
    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
   public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        
        // Define table name
        $builder->setTable('history_entity_people');
        $builder->setCustomRepositoryClass(BusinessObjectRepository::class);
        
        // Define fields
        $builder->createField('title', 'string')->nullable()->build();
        $builder->addField('firstName', 'string');
        $builder->addField('lastName', 'string');
        $builder->addField('position', 'string');
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$entityPublicFields, self::$publicFields);
    }
}