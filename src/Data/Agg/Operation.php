<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Accounting\Data\Agg;

/**
 * Aggregate for operations grid.
 */
class Operation
    extends \Flancer32\Lib\DataObject
{
    /**#@+
     * Aliases for data attributes.
     */
    const AS_DATE_PERFORMED = 'DatePerformed';
    const AS_ID = 'Id';
    const AS_NOTE = 'Note';
    const AS_TYPE = 'Type';
    /**#@- */

    /** @return string */
    public function getDatePerformed()
    {
        $result = parent::getData(self::AS_DATE_PERFORMED);
        return $result;
    }

    /** @return int */
    public function getId()
    {
        $result = parent::getData(self::AS_ID);
        return $result;
    }

    /** @return string */
    public function getNote()
    {
        $result = parent::getData(self::AS_NOTE);
        return $result;
    }

    /** @return string */
    public function getType()
    {
        $result = parent::getData(self::AS_TYPE);
        return $result;
    }

    public function setDatePerformed($data)
    {
        parent::setData(self::AS_DATE_PERFORMED, $data);
    }

    public function setId($data)
    {
        parent::setData(self::AS_ID, $data);
    }

    public function setNote($data)
    {
        parent::setData(self::AS_NOTE, $data);
    }

    public function setType($data)
    {
        parent::setData(self::AS_TYPE, $data);
    }

}