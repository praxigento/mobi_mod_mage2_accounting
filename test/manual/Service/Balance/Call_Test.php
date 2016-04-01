<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Accounting\Lib\Service\Balance;

use Praxigento\Core\Lib\Context;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Call_ManualTest extends \Praxigento\Core\Lib\Test\BaseTestCase {
    const ASSET_TYPE_ID = 1;

    public function test_getLastDate() {
        $obm = Context::instance()->getObjectManager();
        /** @var  $call \Praxigento\Accounting\Lib\Service\Balance\Call */
        $call = $obm->get('Praxigento\Accounting\Lib\Service\Balance\Call');
        $req = new Request\GetLastDate();
        $req->assetTypeId = self::ASSET_TYPE_ID;
        /** @var  $resp Response\GetLastDate */
        $resp = $call->getLastDate($req);
        $this->assertTrue($resp->isSucceed());
        $period = $resp->getLastDate();
        $this->assertNotNull($period);
    }

    public function test_getBalancesOnDate() {
        $obm = Context::instance()->getObjectManager();
        /** @var  $call \Praxigento\Accounting\Lib\Service\Balance\Call */
        $call = $obm->get('Praxigento\Accounting\Lib\Service\Balance\Call');
        $req = new Request\GetBalancesOnDate();
        $req->setData(Request\GetBalancesOnDate::ASSET_TYPE_ID, self::ASSET_TYPE_ID);
        $req->setData(Request\GetBalancesOnDate::DATE, '20151117');
        /** @var  $resp Response\GetBalancesOnDate */
        $resp = $call->getBalancesOnDate($req);
        $this->assertTrue($resp->isSucceed());
        $data = $resp->getData();
        $this->assertNotNull($data);
    }

    public function test_calc() {
        $obm = Context::instance()->getObjectManager();
        /** @var  $call \Praxigento\Accounting\Lib\Service\Balance\Call */
        $call = $obm->get('Praxigento\Accounting\Lib\Service\Balance\Call');
        $req = new Request\Calc();
        $req->assetTypeId = self::ASSET_TYPE_ID;
        $req->dateTo = '20161117';
        /** @var  $resp Response\Calc */
        $resp = $call->calc($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_reset() {
        $obm = Context::instance()->getObjectManager();
        /** @var  $call \Praxigento\Accounting\Lib\Service\Balance\Call */
        $call = $obm->get('Praxigento\Accounting\Lib\Service\Balance\Call');
        $req = new Request\Reset();
        $req->setData(Request\Reset::DATE_FROM, '20151111');
        /** @var  $resp Response\Reset */
        $resp = $call->reset($req);
        $this->assertTrue($resp->isSucceed());
    }
}