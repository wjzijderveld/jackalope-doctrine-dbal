<?php

require_once __DIR__.'/../phpcr-api/inc/AbstractLoader.php';

/**
 * Implementation loader for jackalope-doctrine-dbal
 */
class ImplementationLoader extends \PHPCR\Test\AbstractLoader
{
    private static $instance = null;

    protected function __construct()
    {
        parent::__construct('Jackalope\RepositoryFactoryDoctrineDBAL');

        $this->unsupportedChapters = array(
                    'PermissionsAndCapabilities',
                    'Import',
                    'Observation',
                    'WorkspaceManagement',
                    'ShareableNodes',
                    'Versioning',
                    'AccessControlManagement',
                    'Locking',
                    'LifecycleManagement',
                    'RetentionAndHold',
                    'Transactions',
                    'SameNameSiblings',
                    'OrderableChildNodes',
        );

        $this->unsupportedCases = array(
                    'Writing\\MoveMethodsTest',
        );

        $this->unsupportedTests = array(
                    'Connecting\\RepositoryTest::testLoginException', //TODO: figure out what would be invalid credentials
                    'Connecting\\RepositoryTest::testNoLogin',
                    'Connecting\\RepositoryTest::testNoLoginAndWorkspace',

                    'Reading\\SessionReadMethodsTest::testImpersonate', //TODO: Check if that's implemented in newer jackrabbit versions.
                    'Reading\\SessionNamespaceRemappingTest::testSetNamespacePrefix',
                    'Reading\\NodeReadMethodsTest::testGetSharedSetUnreferenced', // TODO: should this be moved to 14_ShareableNodes

                    'Query\QueryManagerTest::testGetQuery',
                    'Query\QueryManagerTest::testGetQueryInvalid',

                    'Writing\\NamespaceRegistryTest::testRegisterUnregisterNamespace',
                    'Writing\\CopyMethodsTest::testCopyUpdateOnCopy',
        );

    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new ImplementationLoader();
        }
        return self::$instance;
    }

    public function getRepositoryFactoryParameters()
    {
        global $dbConn; // initialized in bootstrap_doctrine.php
        return array('jackalope.doctrine_dbal_connection' => $dbConn);
    }

    public function getCredentials() {
        return new \PHPCR\SimpleCredentials($GLOBALS['phpcr.user'], $GLOBALS['phpcr.pass']);
    }

    public function getInvalidCredentials() {
        return new \PHPCR\SimpleCredentials('nonexistinguser', '');
    }

    public function getRestrictedCredentials()
    {
        return new \PHPCR\SimpleCredentials('anonymous', 'abc');
    }

    public function getUserId()
    {
        return $GLOBALS['phpcr.user'];
    }

    function getRepository()
    {
        global $dbConn;

        $dbConn->insert('phpcr_workspaces', array('name' => 'tests'));
        $transport = new \Jackalope\Transport\DoctrineDBAL\Client(new \Jackalope\Factory, $dbConn);
        $GLOBALS['pdo'] = $dbConn->getWrappedConnection();
        return new \Jackalope\Repository(null, $transport);
    }

    function getFixtureLoader()
    {
        require_once "DoctrineFixtureLoader.php";
        return new \DoctrineFixtureLoader($GLOBALS['pdo'], __DIR__ . "/../fixtures/doctrine/");
    }
}