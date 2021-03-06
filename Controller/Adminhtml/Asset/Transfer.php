<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Accounting\Controller\Adminhtml\Asset;

use Praxigento\Accounting\Api\Web\Account\Asset\Transfer\Request as ARequest;
use Praxigento\Accounting\Api\Web\Account\Asset\Transfer\Response as AResponse;

/**
 * This is controller to transfer assets from adminhtml.
 * See "\Praxigento\Accounting\Api\Web\Account\Asset\TransferInterface" for frontend.
 */
class Transfer
    extends \Praxigento\Core\App\Action\Back\Api\Base
{
    /** @var \Praxigento\Accounting\Api\Service\Account\Asset\Transfer */
    private $servAssetTransfer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Praxigento\Accounting\Api\Service\Account\Asset\Transfer $servAssetTransfer
    ) {
        parent::__construct($context);
        $this->servAssetTransfer = $servAssetTransfer;
    }

    protected function getInDataType(): string
    {
        return ARequest::class;
    }

    protected function getOutDataType(): string
    {
        return AResponse::class;
    }

    protected function process($request)
    {
        assert($request instanceof ARequest);
        /** define local working data */
        $data = $request->getData();
        $amount = $data->getAmount();
        $comment = $data->getComment();
        $assetTypeId = $data->getAssetId();
        $counterPartyId = $data->getCounterPartyId();
        $custId = $data->getCustomerId();
        $isDirect = $data->getIsDirect();

        /* get currently logged in users */
        $auth = $this->getAuthenticator();
        $userId = $auth->getCurrentUserId($request);

        /* prepare input data */
        $amount = $this->filterAmount($amount);

        /** perform processing */
        $req = new \Praxigento\Accounting\Api\Service\Account\Asset\Transfer\Request();
        $req->setAmount($amount);
        $req->setAssetId($assetTypeId);
        $req->setCounterPartyId($counterPartyId);
        $req->setCustomerId($custId);
        $req->setIsDirect($isDirect);
        $req->setUserId($userId);
        $req->setNote($comment);
        $resp = $this->servAssetTransfer->exec($req);

        /** compose result */
        $result = new AResponse();
        $result->setData($resp);
        return $result;
    }

    /**
     * Remove all spaces from amount value.
     *
     * @param mixed $amount
     * @return string
     */
    private function filterAmount($amount)
    {
        $result = str_replace(' ', '', $amount);
        return $result;
    }
}