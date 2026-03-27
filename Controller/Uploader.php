<?php
/**
 * @description Uploader controller
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace Bina\CustomerFile\Controller;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\Request\Http as HttpRequest;
use Bina\CustomerFile\Api\FileManagementInterface;

class Uploader extends Action implements HttpPostActionInterface
{
    /**
     * @var FileManagementInterface
     */
    protected $_fileManagement;

    /**
     * @var string
     */
    protected $_attributeCode;

    /**
     * Constructor
     *
     * @param FileManagementInterface $fileManagement
     * @param string                  $attributeCode
     * @param Context                 $context
     */
    public function __construct(
        FileManagementInterface $fileManagement,
                                $attributeCode,
        Context                 $context
    ) {
        $this->_fileManagement = $fileManagement;
        $this->_attributeCode  = $attributeCode;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Json
     * @throws Exception
     */
    public function execute()
    {
        try {
            /** @var HttpRequest $request */
            $request = $this->getRequest();

            if (!$request->isAjax()) {
                throw new Exception(__('Invalid request.'));
            }

            $result = $this->_fileManagement->upload($this->_attributeCode, 'customer');
        }
        catch (Exception $e) {
            $result = [
                'error'     => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
