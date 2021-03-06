<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Accounting\Service\Balance\Get;

use Praxigento\Accounting\Api\Service\Balance\Get\Turnover\Request as ARequest;
use Praxigento\Accounting\Api\Service\Balance\Get\Turnover\Response as AResponse;
use Praxigento\Accounting\Config as Cfg;
use Praxigento\Accounting\Repo\Query\Balance\OnDate\Closing\ByAsset as QBalanceClose;

class Turnover
    implements \Praxigento\Accounting\Api\Service\Balance\Get\Turnover
{
    /** @var  \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\Accounting\Repo\Query\Balance\OnDate\Closing\ByAsset */
    private $qbBalClose;
    /** @var \Praxigento\Accounting\Repo\Dao\Type\Asset */
    private $daoTypeAsset;

    public function __construct(
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Accounting\Repo\Query\Balance\OnDate\Closing\ByAsset $qbBalClose,
        \Praxigento\Accounting\Repo\Dao\Type\Asset $daoTypeAsset
    ) {
        $this->hlpPeriod = $hlpPeriod;
        $this->qbBalClose = $qbBalClose;
        $this->daoTypeAsset = $daoTypeAsset;

    }

    public function exec($request)
    {
        assert($request instanceof ARequest);
        $result = new AResponse();
        $assetTypeId = $request->assetTypeId;
        $assetTypeCode = $request->assetTypeCode;
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;

        /* analyze conditions */
        if (is_null($assetTypeId)) {
            $assetTypeId = $this->daoTypeAsset->getIdByCode($assetTypeCode);
        }
        $dateFromBefore = $this->hlpPeriod->getPeriodPrev($dateFrom);

        /* perform action */

        /* get balances on the end of the previous period */
        $qCloseBegin = $this->qbBalClose->build();
        $conn = $qCloseBegin->getConnection();
        $bind = [
            QBalanceClose::BND_ASSET_TYPE_ID => $assetTypeId,
            QBalanceClose::BND_MAX_DATE => $dateFromBefore
        ];
        $rowsBegin = $conn->fetchAll($qCloseBegin, $bind);

        /* get balances on the end of this period */
        $qCloseBegin = $this->qbBalClose->build();
        $bind = [
            QBalanceClose::BND_ASSET_TYPE_ID => $assetTypeId,
            QBalanceClose::BND_MAX_DATE => $dateTo
        ];
        $rowsEnd = $conn->fetchAll($qCloseBegin, $bind);

        /* compose asset delta for period */
        $entries = [];
        $sum = 0;
        /* process all balances for the end of period */
        foreach ($rowsEnd as $row) {
            $customerId = $row[QBalanceClose::A_CUST_ID];
            $accId = $row[QBalanceClose::A_ACC_ID];
            $balanceClose = $row[QBalanceClose::A_BALANCE];
            $data = new \Praxigento\Accounting\Api\Service\Balance\Get\Turnover\Response\Entry();
            $data->accountId = $accId;
            $data->customerId = $customerId;
            $data->balanceClose = $balanceClose;
            $data->balanceOpen = 0;
            $data->turnover = $balanceClose;
            $entries[$customerId] = $data;
            $sum += $balanceClose;
        }
        /* add opening balance and calc turnover  */
        foreach ($rowsBegin as $row) {
            $customerId = $row[QBalanceClose::A_CUST_ID];
            $balanceOpen = $row[QBalanceClose::A_BALANCE];
            /** @var \Praxigento\Accounting\Api\Service\Balance\Get\Turnover\Response\Entry $data */
            $data = $entries[$customerId];
            $balanceClose = $data->balanceClose;
            $turnover = ($balanceClose - $balanceOpen);
            $data->balanceOpen = $balanceOpen;
            $data->turnover = $turnover;
            $entries[$customerId] = $data;
            $sum -= $balanceOpen;
        }
        if (abs($sum) > Cfg::DEF_ZERO) {
            throw new \Exception("Balances are not consistent. Total turnover should be equal to zero.");
        }
        $result->entries = $entries;
        return $result;
    }
}