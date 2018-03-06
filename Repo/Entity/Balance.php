<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Accounting\Repo\Entity;

class Balance
    extends \Praxigento\Core\App\Repo\Def\Entity
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Repo\IGeneric $repoGeneric
    )
    {
        parent::__construct($resource, $repoGeneric, \Praxigento\Accounting\Repo\Entity\Data\Balance::class);
    }

    /**
     * @param array|\Praxigento\Accounting\Repo\Entity\Data\Balance $data
     * @return int
     */
    public function create($data)
    {
        $result = parent::create($data);
        return $result;
    }

    /**
     * @param int $id
     * @return \Praxigento\Accounting\Repo\Entity\Data\Balance|bool
     */
    public function getById($id)
    {
        $result = parent::getById($id);
        return $result;
    }

    /**
     * Get maximal datestamp for existing balance by asset type id or null if no data is found.
     *
     * SELECT
     * `b`.`date`
     * FROM `prxgt_acc_account` AS `a`
     * LEFT JOIN `prxgt_acc_balance` AS `b`
     * ON a.id = b.account_id
     * WHERE (a.id = :typeId)
     * ORDER BY `b`.`date` DESC
     *
     * @param int $assetTypeId
     * @return string YYYYMMDD
     */
    public function getMaxDate($assetTypeId = null)
    {
        $asAccount = 'a';
        $asBalance = 'b';
        $tblAccount = $this->resource->getTableName(\Praxigento\Accounting\Repo\Entity\Data\Account::ENTITY_NAME);
        $tblBalance = $this->resource->getTableName(\Praxigento\Accounting\Repo\Entity\Data\Balance::ENTITY_NAME);
        /* select from account */
        $query = $this->conn->select();
        $query->from([$asAccount => $tblAccount], []);
        /* join balance */
        $on = $asAccount . '.' . \Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_ID . '='
            . $asBalance . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_ACCOUNT_ID;
        $query->joinLeft([$asBalance => $tblBalance], $on, [\Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE]);
        /* where */
        $query->where($asAccount . '.' . \Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_ASSET_TYPE_ID . '=:typeId');
        $bind = ['typeId' => $assetTypeId];
        /* order by */
        $query->order([$asBalance . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE . ' DESC']);
        /* perform query */
        $result = $this->conn->fetchOne($query, $bind);
        return $result;
    }

    /**
     * Get balances on concrete date.
     *
     * @param $assetTypeId
     * @param $yyyymmdd
     *
     * @return array
     *
     * Select balances on date by asset type.
     *
     * SELECT
     * `acc`.`id`,
     * `acc`.`customer_id`,
     * `bal`.`date`,
     * `bal`.`opening_balance`,
     * `bal`.`total_debit`,
     * `bal`.`total_credit`,
     * `bal`.`closing_balance`
     * FROM `prxgt_acc_account` AS `acc`
     * LEFT JOIN (SELECT
     * `bal4Max`.`account_id`,
     * MAX(`bal4Max`.`date`) AS date_max
     * FROM `prxgt_acc_balance` AS `bal4Max`
     * WHERE (bal4Max.date <= :date)
     * GROUP BY `bal4Max`.`account_id`) AS `balMax`
     * ON balMax.account_id = acc.id
     * INNER JOIN `prxgt_acc_balance` AS `bal`
     * ON balMax.account_id = bal.account_id
     * AND balMax.date_max = bal.date
     * WHERE (acc.asset_type_id = :asset_type_id)
     * AND (bal.date IS NOT NULL)
     *
     * @param $assetTypeId
     * @param $yyyymmdd
     */
    public function getOnDate($assetTypeId, $yyyymmdd)
    {
        $result = [];
        $conn = $this->conn;
        $bind = [];
        /* see MOBI-112 */
        $asAccount = 'acc';
        $asBal4Max = 'bal4Max';
        $asMax = 'balMax';
        $asBal = 'bal';
        $tblAccount = $this->resource->getTableName(\Praxigento\Accounting\Repo\Entity\Data\Account::ENTITY_NAME);
        $tblBalance = $this->resource->getTableName(\Praxigento\Accounting\Repo\Entity\Data\Balance::ENTITY_NAME);
        /* select MAX(date) from prxgt_acc_balance (internal select) */
        $q4Max = $conn->select();
        $colDateMax = 'date_max';
        $expMaxDate = 'MAX(`' . $asBal4Max . '`.`' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE
            . '`) as ' . $colDateMax;
        $q4Max->from(
            [$asBal4Max => $tblBalance],
            [\Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_ACCOUNT_ID, $expMaxDate]
        );
        $q4Max->group($asBal4Max . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_ACCOUNT_ID);
        $q4Max->where($asBal4Max . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE . '<=:date');
        $bind['date'] = $yyyymmdd;
        //        $sql4Max = (string)$q4Max;
        /* select from prxgt_acc_account */
        $query = $conn->select();
        $query->from(
            [$asAccount => $tblAccount],
            [
                \Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_ID,
                \Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_CUST_ID
            ]
        );
        /* left join $q4Max */
        $on = $asMax . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_ACCOUNT_ID . '='
            . $asAccount . '.' . \Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_ID;
        $cols = [];
        $query->joinLeft([$asMax => $q4Max], $on, $cols);
        /* MOBI-688: join prxgt_acc_balance again (ON pab.account_id = m.account_id AND pab.date = m.date_max) */
        $on = $asBal . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_ACCOUNT_ID . '='
            . $asMax . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_ACCOUNT_ID;
        $on .= ' AND ' . $asBal . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE . '='
            . $asMax . '.' . $colDateMax;
        $cols = [
            \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE,
            \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_BALANCE_OPEN,
            \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_TOTAL_DEBIT,
            \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_TOTAL_CREDIT,
            \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_BALANCE_CLOSE
        ];
        $query->joinLeft([$asBal => $tblBalance], $on, $cols);
        /* where */
        $whereByAssetType = $asAccount . '.' . \Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_ASSET_TYPE_ID
            . '=:asset_type_id';
        $whereByDate = $asBal . '.' . \Praxigento\Accounting\Repo\Entity\Data\Balance::ATTR_DATE . ' IS NOT NULL';
        $query->where("$whereByAssetType AND $whereByDate");
        $bind['asset_type_id'] = (int)$assetTypeId;
        // $sql = (string)$qMain;
        $rows = $conn->fetchAll($query, $bind);
        foreach ($rows as $one) {
            $result[$one[\Praxigento\Accounting\Repo\Entity\Data\Account::ATTR_ID]] = $one;
        }
        return $result;
    }

    /**
     * @param $updateData
     * @return mixed
     */
    public function updateBalances($updateData)
    {
        $this->conn->beginTransaction();
        $tbl = $this->resource->getTableName(\Praxigento\Accounting\Repo\Entity\Data\Balance::ENTITY_NAME);
        foreach ($updateData as $accountId => $byDate) {
            foreach ($byDate as $date => $data) {
                $this->conn->insert($tbl, $data);
            }
        }
        $this->conn->commit();
    }

}