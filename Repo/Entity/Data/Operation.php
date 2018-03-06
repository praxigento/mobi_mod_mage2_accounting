<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Accounting\Repo\Entity\Data;

class Operation
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const ATTR_DATE_PREFORMED = 'date_performed';
    const ATTR_ID = 'id';
    const ATTR_NOTE = 'note';
    const ATTR_TYPE_ID = 'type_id';
    const ENTITY_NAME = 'prxgt_acc_operation';

    /** @return string */
    public function getDatePerformed()
    {
        $result = parent::get(self::ATTR_DATE_PREFORMED);
        return $result;
    }

    /** @return int */
    public function getId()
    {
        $result = parent::get(self::ATTR_ID);
        return $result;
    }

    /** @return string */
    public function getNote()
    {
        $result = parent::get(self::ATTR_NOTE);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::ATTR_ID];
    }

    /** @return int */
    public function getTypeId()
    {
        $result = parent::get(self::ATTR_TYPE_ID);
        return $result;
    }

    /** @param string $data */
    public function setDatePerformed($data)
    {
        parent::set(self::ATTR_DATE_PREFORMED, $data);
    }

    /** @param int $data */
    public function setId($data)
    {
        parent::set(self::ATTR_ID, $data);
    }

    /** @param string $data */
    public function setNote($data)
    {
        parent::set(self::ATTR_NOTE, $data);
    }

    /** @param int $data */
    public function setTypeId($data)
    {
        parent::set(self::ATTR_TYPE_ID, $data);
    }
}