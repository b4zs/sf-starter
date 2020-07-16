<?php

namespace DoctrineEncryptedFieldTypeBundle\Command;

use DoctrineEncryptedFieldTypeBundle\Types\EncryptedDataDateTimeType;
use DoctrineEncryptedFieldTypeBundle\Types\EncryptedDataStringType;
use DoctrineEncryptedFieldTypeBundle\Types\EncryptedDataTextType;
use DoctrineEncryptedFieldTypeBundle\Types\EncryptedDataType;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends ContainerAwareCommand
{
    private const TEMP_COL_PREFIX = 'doctrine_encrypted_field_type_temp_';

    /** @var EntityManagerInterface */
    private $em;

    /**
     * @var array An array of the personal_data column types from the entities.
     */
    private $personalDataFields = [];

    /**
     * @var bool True if encryption has been disabled in the app config.
     */
    private $encryptionDisabled;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Comparator
     */
    private $comparator;

    public function __construct()
    {
        parent::__construct();
    }

    private function initEntityManager()
    {
        $this->em         = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->connection = $this->em->getConnection();

        // TODO: remove
        $this->encryptor = $this->getContainer()->get('DoctrineEncryptedFieldTypeBundle\Encryptor\RuntimeEncryptor');
        EncryptedDataType::setEncryptor($this->encryptor);
    }

    protected function configure()
    {
        $this
            ->setName('doctrine_encrypted_field_type:update')
            ->setDescription('Command to convert a database entry to a personal data object.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initEntityManager();

        $this->comparator = new Comparator();

        // Populate the array with the entities and fields that use the personal_data column type.
        $this->getPersonalDataFields($output);


        // Create temporary data columns in each entity
        $this->createTempDataColumns($output);

        // Convert the existing data into a PersonalData object in temp column, and null original column.
        $this->createPersonalDataInTempColumn($output);

        // Change the original column to personal_data type
        $this->convertOriginalColumnDataType($output);

        // Copy the converted PersonalData back into the original column
        $this->reloadPersonalData($output);

        // Drop the temp columns
        $this->dropTempColumns($output);

    }

    /**
     * Create Temp Data Columns.
     *
     * Clones the existing database, then modifies the schema of the clone to add personal data types.
     * Compares the original schema with the new modified schema
     * Create a schema diff between the two.
     * Execute the queries to alter the original database and create temporary personal data columns.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function createTempDataColumns(OutputInterface $output)
    {

        $output->writeln('Creating temporary columns');

        $schemaManager = $this->connection->getSchemaManager();

        // Get a copy of the schema before any temp columns area created.
        $fromSchema = $schemaManager->createSchema();

        // Clone the schema to make altetions to.
        $toSchema = clone $fromSchema;

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            /** @var Table $table */
            $table = $toSchema->getTable($tableName);

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Set the existing column to nullable and no max
                $column = $table->getColumn($propertyArray['columnName']);
                $column->setNotnull(false);

                // Create a new temporary column in the table to store the existing data as a PersonalData object.
                if (!$table->hasColumn($propertyArray['tempColName'])) {
                    $table->addColumn($propertyArray['tempColName'], Type::OBJECT, ['Notnull' => false,]);
                }
            }
        }

        $platform = $schemaManager->getDatabasePlatform();

        $schemaDiff = $this->comparator->compare($fromSchema, $toSchema);

        $queries = $schemaDiff->toSql($platform); // queries to get from one to another schema.

        // Execute the queres to modify the database.
        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }

    /**
     * Create PessonalData object in a temporary column.
     *
     * Step through all the previously stored personal data columns.
     * If the original column data is already a personalData type then unserialize it and get the raw data.
     * Create a new PersonalData object using the current annotation information, serlialse and store in a temp
     * data column.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createPersonalDataInTempColumn(OutputInterface $output)
    {

        $numberOfColumns = count($this->getPersonalDataFields());
        $output->writeln("Creating personal data objects in $numberOfColumns columns");

        // Get the query builder to load existing entity data.
        $queryBuilder = $this->connection->createQueryBuilder();

        $progressBar = new ProgressBar($output, $numberOfColumns);
        $progressBar->start();

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Get all data for the current entity and field.
                $queryBuilder
                    ->select('t.id, t.' . $propertyArray['columnName'] . ' AS originalData')
                    ->from($propertyArray['tableName'], 't');

                $results = $queryBuilder->execute();

                // For each selected entity field create a personal data object and save it to the temporary field.
                foreach ($results as $result) {

                    // Get the original data from the query.
                    $originalValue  = $result['originalData'];
                    $encryptedValue = $this->encryptor->encrypt($originalValue);

                    // Update the database with the temporary data, set the original data to null.
                    $this->connection->update(
                        $tableName,
                        array(
                            $propertyArray['columnName']  => null,
                            $propertyArray['tempColName'] => $encryptedValue,
                        ),
                        array('id' => $result['id'])
                    );
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(".");
    }

    /**
     * Convert Original Column data type to personal_data
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function convertOriginalColumnDataType(OutputInterface $output)
    {
        $output->writeln('Converting original columns to new personal data column type');

        // Alter the existing column to object data type and nullable.

        $schemaManager = $this->connection->getSchemaManager();
        $fromSchema    = $schemaManager->createSchema();

        $toSchema = clone $fromSchema;

        foreach ($this->getPersonalDataFields() as $entityClass => $field) {
            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Find the entity table in the schema.
            $table = $toSchema->getTable($tableName);

            foreach ($field as $propertyArray) {

                $origionalColName = $propertyArray['columnName'];

                $column = $table->getColumn($origionalColName);

                $type = Type::getType(EncryptedDataStringType::NAME);

                $column
                    ->setType($type)
                    ->setNotnull(false)
                    ->setLength(null);
            }
        }

        $schemaDiff = $this->comparator->compare($fromSchema, $toSchema);
        $platform   = $schemaManager->getDatabasePlatform();
        $queries    = $schemaDiff->toSql($platform); // queries to get from one to another schema.

        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }


    /**
     * Reload PersonalData
     *
     * Move the PersonalData object from the temporary column back to the original column.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     */
    private function reloadPersonalData(OutputInterface $output)
    {
        $output->writeln('Reload personal data to original columns');

        // Get the query builder to load existing entity data.
        $queryBuilder = $this->connection->createQueryBuilder();

        $progressBar = new ProgressBar($output, count($this->getPersonalDataFields()));
        $progressBar->start();

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Get all data for the current entity and field.
                $queryBuilder
                    ->select('t.id, t.' . $propertyArray['tempColName'] . ' AS newPersonalData')
                    ->from($propertyArray['tableName'], 't');

                $results = $queryBuilder->execute();

                // For each selected entity field create a personal data object and save it to the temporary field.
                foreach ($results as $result) {

                    // Get the copied personal_data from the query.
                    $personalData = $result['newPersonalData'];

                    // Update the database with the temporary data, set the original data to null.
                    $this->connection->update(
                        $tableName,
                        array(
                            $propertyArray['columnName'] => $personalData,
                        ),
                        array('id' => $result['id'])
                    );
                }
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln(".");

    }

    /**
     * Drop the temporary columns from the schema and remove from the database.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function dropTempColumns(OutputInterface $output)
    {
        $output->writeln('Dropping temporary columns');

        $schemaManager = $this->connection->getSchemaManager();

        // Get a copy of the schema before any temp columns area created.
        $fromSchema = $schemaManager->createSchema();

        // Clone the schema to make altetions to.
        $toSchema = clone $fromSchema;

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Find the entity table in the schema.
            $table = $toSchema->getTable($tableName);

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {
                // Drop the temporary column.
                if ($table->hasColumn($propertyArray['tempColName'])) {
                    $table->dropColumn($propertyArray['tempColName']);
                }

            }
        }

        $platform   = $schemaManager->getDatabasePlatform();
        $schemaDiff = $this->comparator->compare($fromSchema, $toSchema);
        $queries    = $schemaDiff->toSql($platform); // Queries to get from one to another schema.

        // Run the queries.
        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }

    /**
     * Get PersonalData fields.
     *
     * Visit every entity and identify fields that contain a personal_data annotation.
     * Store the field to an array for processing.
     *
     * @return array
     */
    private function getPersonalDataFields()
    {
        $managedEntities = $this->em->getMetadataFactory()->getAllMetadata();

        /** @var ClassMetadata $managedEntity */
        foreach ($managedEntities as $managedEntity) {
            // Ignore mapped superclass entities.
            if (property_exists($managedEntity, 'isMappedSuperclass') && $managedEntity->isMappedSuperclass === true) {
                continue;
            }

            $entityClass = $managedEntity->getName();
            $metadata    = $this->em->getClassMetadata($entityClass);
            $ref         = new \ReflectionClass($entityClass);

            foreach ($metadata->getFieldNames() as $fieldName) {
                $fieldMetadata = $metadata->getFieldMapping($fieldName);
                if (!in_array($fieldMetadata['type'], [
                    EncryptedDataStringType::NAME,
                    EncryptedDataDateTimeType::NAME,
                    EncryptedDataTextType::NAME,
                ])) {
                    continue;
                }

                $columnName  = $managedEntity->getColumnName($fieldName);
                $refProperty = $ref->getProperty($fieldName);

                // Store the field data information for later use.
                $this->personalDataFields[$entityClass][$fieldName] = [
                    'tableName'   => $managedEntity->getTableName(),
                    'columnName'  => $columnName,
                    'tempColName' => $this->getTempColumnName($columnName),
                    'refProperty' => $refProperty,
                    'annotation'  => [
                        //....
                    ],
                ];
            }
        }

        return $this->personalDataFields;
    }

    private function getTempColumnName($originalColumnName)
    {
        return self::TEMP_COL_PREFIX . $originalColumnName;
    }
}
