<?php

namespace Praxigento\Accounting\Ui\DataProvider\Grid\Type\Asset;

use Praxigento\Accounting\Repo\Data\Type\Asset as ETypeAsset;

class Query
    extends \Praxigento\Core\App\Ui\DataProvider\Grid\Query\Builder
{
    /**#@+ Tables aliases for external usage ('camelCase' naming) */
    const AS_TYP_ASSET = 'tas';
    /**#@- */
    const A_CODE = 'code';
    /**#@+
     * Aliases for data attributes.
     */
    const A_ID_ASSET = 'id';
    const A_NOTE = 'note';

    /**#@- */

    protected function getMapper()
    {
        if (is_null($this->mapper)) {
            $map = [
                self::A_ID_ASSET => self::AS_TYP_ASSET . '.' . ETypeAsset::A_ID,
                self::A_CODE => self::AS_TYP_ASSET . '.' . ETypeAsset::A_CODE,
                self::A_NOTE => self::AS_TYP_ASSET . '.' . ETypeAsset::A_NOTE
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
        $asTypAsset = self::AS_TYP_ASSET;

        /* SELECT FROM prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(ETypeAsset::ENTITY_NAME);
        $as = $asTypAsset;
        $cols = [
            self::A_ID_ASSET => ETypeAsset::A_ID,
            self::A_CODE => ETypeAsset::A_CODE,
            self::A_NOTE => ETypeAsset::A_NOTE
        ];
        $result->from([$as => $tbl], $cols);
        return $result;
    }

    protected function getQueryTotal()
    {
        /* get query to select items */
        /** @var \Magento\Framework\DB\Select $result */
        $result = $this->getQueryItems();
        /* ... then replace "columns" part with own expression */
        $value = 'COUNT(' . self::AS_TYP_ASSET . '.' . ETypeAsset::A_ID . ')';

        /**
         * See method \Magento\Framework\DB\Select\ColumnsRenderer::render:
         */
        /**
         * if ($column instanceof \Zend_Db_Expr) {...}
         */
        $exp = new \Praxigento\Core\App\Repo\Query\Expression($value);
        /**
         *  list($correlationName, $column, $alias) = $columnEntry;
         */
        $entry = [null, $exp, null];
        $cols = [$entry];
        $result->setPart('columns', $cols);
        return $result;
    }
}