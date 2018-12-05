<?php

namespace Praxigento\Accounting\Ui\DataProvider\Grid\Transaction;

use Praxigento\Accounting\Config as Cfg;
use Praxigento\Accounting\Repo\Data\Account as EAccount;
use Praxigento\Accounting\Repo\Data\Log\Change\Admin as ELogAdmin;
use Praxigento\Accounting\Repo\Data\Log\Change\Customer as ELogCustomer;
use Praxigento\Accounting\Repo\Data\Transaction as ETransaction;
use Praxigento\Accounting\Repo\Data\Type\Asset as ETypeAsset;
use Praxigento\Core\App\Repo\Query\Expression as AnExpress;

class Query
    extends \Praxigento\Core\App\Ui\DataProvider\Grid\Query\Builder
{

    /**#@+ Tables aliases for external usage ('camelCase' naming) */
    const AS_ACC_CREDIT = 'paa_cr';
    const AS_ACC_DEBIT = 'paa_db';
    const AS_ASSET = 'pata';
    const AS_BY_ADMIN = 'admin';
    const AS_BY_CUST = 'cust';
    const AS_CUST_CREDIT = 'ce_cr';
    const AS_CUST_DEBIT = 'ce_db';
    const AS_LOG_ADMIN = 'logAdmin';
    const AS_LOG_CUST = 'logCust';
    const AS_TRANS = 'pat';
    /**#@- */

    /**#@+
     * Aliases for data attributes.
     */
    const A_ASSET = 'asset';
    const A_CREDIT = 'credit';
    const A_CUSTOMER = 'customer';
    const A_DATE_APPLIED = 'dateApplied';
    const A_DEBIT = 'debit';
    const A_ID_OPER = 'operId';
    const A_ID_TRANS = 'transId';
    const A_NOTE = 'note';
    const A_USER = 'user';
    const A_VALUE = 'value';
    /**#@- */

    protected function getMapper()
    {
        $expCust = $this->expressCustomer();
        if (is_null($this->mapper)) {
            $map = [
                self::A_ASSET => self::AS_ASSET . '.' . ETypeAsset::A_CODE,
                self::A_CREDIT => self::AS_CUST_CREDIT . '.' . Cfg::E_CUSTOMER_A_EMAIL,
                self::A_CUSTOMER => $expCust,
                self::A_DATE_APPLIED => self::AS_TRANS . '.' . ETransaction::A_DATE_APPLIED,
                self::A_DEBIT => self::AS_CUST_DEBIT . '.' . Cfg::E_CUSTOMER_A_EMAIL,
                self::A_ID_OPER => self::AS_TRANS . '.' . ETransaction::A_OPERATION_ID,
                self::A_ID_TRANS => self::AS_TRANS . '.' . ETransaction::A_ID,
                self::A_NOTE => self::AS_TRANS . '.' . ETransaction::A_NOTE,
                self::A_USER => self::AS_BY_ADMIN . '.' . Cfg::E_ADMIN_USER_A_USERNAME,
                self::A_VALUE => self::AS_TRANS . '.' . ETransaction::A_VALUE
            ];
            $this->mapper = new \Praxigento\Core\App\Repo\Query\Criteria\Def\Mapper($map);
        }
        $result = $this->mapper;
        return $result;
    }

    protected function getQueryItems()
    {
        $result = $this->conn->select();
        /* define tables aliases for internal usage (in this method) */
        $asAccCredit = self::AS_ACC_CREDIT;
        $asAccDebit = self::AS_ACC_DEBIT;
        $asAsset = self::AS_ASSET;
        $asByAdmin = self::AS_BY_ADMIN;
        $asByCust = self::AS_BY_CUST;
        $asCustCredit = self::AS_CUST_CREDIT;
        $asCustDebit = self::AS_CUST_DEBIT;
        $asLogAdmin = self::AS_LOG_ADMIN;
        $asLogCust = self::AS_LOG_CUST;
        $asTrans = self::AS_TRANS;

        /* SELECT FROM prxgt_acc_transaction */
        $tbl = $this->resource->getTableName(ETransaction::ENTITY_NAME);
        $as = $asTrans;
        $cols = [
            self::A_ID_TRANS => ETransaction::A_ID,
            self::A_ID_OPER => ETransaction::A_OPERATION_ID,
            self::A_DATE_APPLIED => ETransaction::A_DATE_APPLIED,
            self::A_VALUE => ETransaction::A_VALUE,
            self::A_NOTE => ETransaction::A_NOTE
        ];
        $result->from([$as => $tbl], $cols);

        /* LEFT JOIN prxgt_acc_account DEBIT */
        $tbl = $this->resource->getTableName(EAccount::ENTITY_NAME);
        $as = $asAccDebit;
        $cond = $asAccDebit . '.' . EAccount::A_ID . '=' . $asTrans . '.' . ETransaction::A_DEBIT_ACC_ID;
        $cols = [];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN customer_entity */
        $tbl = $this->resource->getTableName(Cfg::ENTITY_MAGE_CUSTOMER);
        $as = $asCustDebit;
        $cond = $asCustDebit . '.' . Cfg::E_CUSTOMER_A_ENTITY_ID . '=' . $asAccDebit . '.' . EAccount::A_CUST_ID;
        $cols = [
            self::A_DEBIT => Cfg::E_CUSTOMER_A_EMAIL
        ];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_account CREDIT */
        $tbl = $this->resource->getTableName(EAccount::ENTITY_NAME);
        $as = $asAccCredit;
        $cond = $asAccCredit . '.' . EAccount::A_ID . '=' . $asTrans . '.' . ETransaction::A_CREDIT_ACC_ID;
        $cols = [];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN customer_entity */
        $tbl = $this->resource->getTableName(Cfg::ENTITY_MAGE_CUSTOMER);
        $as = $asCustCredit;
        $cond = $asCustCredit . '.' . Cfg::E_CUSTOMER_A_ENTITY_ID . '=' . $asAccCredit . '.' . EAccount::A_CUST_ID;
        $cols = [
            self::A_CREDIT => Cfg::E_CUSTOMER_A_EMAIL
        ];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(ETypeAsset::ENTITY_NAME);
        $as = $asAsset;
        $cond = $asAsset . '.' . ETypeAsset::A_ID . '=' . $asAccDebit . '.' . EAccount::A_ASSET_TYPE_ID;
        $cols = [
            self::A_ASSET => ETypeAsset::A_CODE
        ];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_log_change_admin */
        $tbl = $this->resource->getTableName(ELogAdmin::ENTITY_NAME);
        $as = $asLogAdmin;
        $cond = "$as." . ELogAdmin::A_OPER_REF . '=' . $asTrans . '.' . ETransaction::A_OPERATION_ID;
        $cols = [];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN admin_user */
        $tbl = $this->resource->getTableName(Cfg::ENTITY_MAGE_ADMIN_USER);
        $as = $asByAdmin;
        $cond = "$as." . Cfg::E_ADMIN_USER_A_USER_ID . '=' . $asLogAdmin . '.' . ELogAdmin::A_USER_REF;
        $cols = [
            self::A_USER => Cfg::E_ADMIN_USER_A_USERNAME
        ];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_acc_log_change_customer */
        $tbl = $this->resource->getTableName(ELogCustomer::ENTITY_NAME);
        $as = $asLogCust;
        $cond = "$as." . ELogCustomer::A_OPER_REF . '=' . $asTrans . '.' . ETransaction::A_OPERATION_ID;
        $cols = [];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN customer_entity */
        $tbl = $this->resource->getTableName(Cfg::ENTITY_MAGE_CUSTOMER);
        $as = $asByCust;
        $cond = "$as." . Cfg::E_CUSTOMER_A_ENTITY_ID . '=' . $asLogCust . '.' . ELogCustomer::A_CUST_REF;
        $exp = $this->expressCustomer();
        $cols = [
            self::A_CUSTOMER => $exp
        ];
        $result->joinLeft([$as => $tbl], $cond, $cols);

        return $result;
    }

    private function expressCustomer()
    {
        $first = self::AS_BY_CUST . '.' . Cfg::E_CUSTADDR_A_FIRSTNAME;
        $last = self::AS_BY_CUST . '.' . Cfg::E_CUSTADDR_A_LASTNAME;
        $exp = "CONCAT($first,' ',$last)";
        $result = new AnExpress($exp);
        return $result;
    }

    protected function getQueryTotal()
    {
        /* get query to select items */
        /** @var \Magento\Framework\DB\Select $result */
        $result = $this->getQueryItems();
        /* ... then replace "columns" part with own expression */
        $value = 'COUNT(' . self::AS_TRANS . '.' . ETransaction::A_ID . ')';

        /**
         * See method \Magento\Framework\DB\Select\ColumnsRenderer::render:
         */
        /**
         * if ($column instanceof \Zend_Db_Expr) {...}
         */
        $exp = new AnExpress($value);
        /**
         *  list($correlationName, $column, $alias) = $columnEntry;
         */
        $entry = [null, $exp, null];
        $cols = [$entry];
        $result->setPart('columns', $cols);
        return $result;
    }
}