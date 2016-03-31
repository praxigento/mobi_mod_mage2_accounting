<?php
/**
 * Create DB schema.
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Accounting\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Praxigento\Accounting\Lib\Entity\Account as Account;
use Praxigento\Accounting\Lib\Entity\Balance as Balance;
use Praxigento\Accounting\Lib\Entity\Operation as Operation;
use Praxigento\Accounting\Lib\Entity\Transaction as Transaction;
use Praxigento\Accounting\Lib\Entity\Type\Asset as TypeAsset;
use Praxigento\Accounting\Lib\Entity\Type\Operation as TypeOperation;
use Praxigento\Core\Lib\Setup\Db as Db;

class InstallSchema extends \Praxigento\Core\Setup\Schema\Base
{

    protected function _setup(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** Read and parse JSON schema. */
        $pathToFile = __DIR__ . '/../etc/dem.json';
        $pathToNode = '/dBEAR/package/Praxigento/package/Accounting';
        $demPackage = $this->_toolDem->readDemPackage($pathToFile, $pathToNode);

        /* Type Asset */
        $entityAlias = TypeAsset::ENTITY_NAME;
        $demEntity = $demPackage['package']['Type']['entity']['Asset'];
        $this->_toolDem->createEntity($entityAlias, $demEntity);

        /* Type Operation */
        $entityAlias = TypeOperation::ENTITY_NAME;
        $demEntity = $demPackage['package']['Type']['entity']['Operation'];
        $this->_toolDem->createEntity($entityAlias, $demEntity);

        /* Account */
        $entityAlias = Account::ENTITY_NAME;
        $demEntity = $demPackage['entity']['Account'];
        $this->_toolDem->createEntity($entityAlias, $demEntity);

        /* Operation */
        $entityAlias = Operation::ENTITY_NAME;
        $demEntity = $demPackage['entity']['Operation'];
        $this->_toolDem->createEntity($entityAlias, $demEntity);

        /* Transaction */
        $entityAlias = Transaction::ENTITY_NAME;
        $demEntity = $demPackage['entity']['Transaction'];
        $this->_toolDem->createEntity($entityAlias, $demEntity);

        /* Balance */
        $entityAlias = Balance::ENTITY_NAME;
        $demEntity = $demPackage['entity']['Balance'];
        $this->_toolDem->createEntity($entityAlias, $demEntity);
    }
}