<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Accounting\Repo\Def;

use Praxigento\Accounting\Data\Entity\Transaction as EntityTransaction;
use Praxigento\Accounting\Repo\ITransaction;
use Praxigento\Core\Repo\Def\Base;

class Transaction extends Base implements ITransaction
{
    /** @var \Praxigento\Core\Repo\IBasic */
    protected $_repoBasic;

    /**
     * Account constructor.
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $rsrcConn,
        \Praxigento\Core\Repo\IBasic $repoBasic
    ) {
        parent::__construct($rsrcConn);
        $this->_repoBasic = $repoBasic;
    }

    public function create($data)
    {
        $result = null;
        $entity = EntityTransaction::ENTITY_NAME;
        $id = $this->_repoBasic->addEntity($entity, $data);
        if ($id) {
            $result = $data;
            $result[EntityTransaction::ATTR_ID] = $id;
        }
        return $result;
    }

}