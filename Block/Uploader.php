<?php
/**
 * @description Uploader block
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace Bina\CustomerFile\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Bina\CustomerFile\Api\FileManagementInterface;

class Uploader extends Template
{
    /**
     * @var FileManagementInterface
     */
    protected $_fileManagement;

    /**
     * @var CustomerMetadataInterface
     */
    protected $_attribute;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var CustomerInterface|null
     */
    protected $_customer = null;

    /**
     * @var string|null
     */
    protected $_uploaderId = null;

    /**
     * @var string|null
     */
    protected $_uploadUrl = null;

    /**
     * @var string
     */
    protected $_template = 'Bina_CustomerFile::form/element/uploader.phtml';

    /**
     * Constructor
     *
     * @param FileManagementInterface   $fileManagement
     * @param string                    $attributeCode
     * @param CustomerMetadataInterface $customerMetadataService
     * @param Json                      $json
     * @param Context                   $context
     * @param array                     $data
     */
    public function __construct(
        FileManagementInterface   $fileManagement,
                                  $attributeCode,
        CustomerMetadataInterface $customerMetadataService,
        Json                      $json,
        Context                   $context,
        array                     $data = []
    ) {
        $this->_json           = $json;
        $this->_fileManagement = $fileManagement;
        $this->_attribute      = $customerMetadataService->getAttributeMetadata(
            $attributeCode
        );
        parent::__construct($context, $data);
    }

    /**
     * Init customer
     *
     * @param  CustomerInterface $customer
     * @return $this
     */
    public function initCustomer(CustomerInterface $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Init uploader ID
     *
     * @param  string $id
     * @return $this
     */
    public function initUploaderId($id)
    {
        $this->_uploaderId = sprintf('%s-uploader', $id);
        return $this;
    }

    /**
     * Init upload URL
     *
     * @param  string $uri
     * @return $this
     */
    public function initUploadUrl($uri)
    {
        $this->_uploadUrl = $this->_urlBuilder->getUrl($uri);
        return $this;
    }

    /**
     * Get customer
     *
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Get uploader ID
     *
     * @return string|null
     */
    public function getUploaderId()
    {
        return $this->_uploaderId;
    }

    /**
     * Get upload URL
     *
     * @return string|null
     */
    public function getUploadUrl()
    {
        return $this->_uploadUrl;
    }

    /**
     * Get attribute data in JSON format
     *
     * @return string
     */
    public function getAttributeDataJson()
    {
        return $this->_json->serialize($this->_getAttributeData());
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_attribute->getAttributeCode();
    }

    /**
     * Get attribute label
     *
     * @return string
     */
    public function getAttributeLabel()
    {
        return __($this->_attribute->getFrontendLabel());
    }

    /**
     * Get attribute allowed extensions in JSON format
     *
     * @return string
     */
    public function getAttributeAllowedExtensionsJson()
    {
        return $this->_json->serialize(
            $this->_fileManagement->getAllowedExtensions(
                $this->getAttributeCode()
            )
        );
    }

    /**
     * Get attribute data
     *
     * @return array
     */
    private function _getAttributeData()
    {
        $data      = [];
        $attribute = $this->getCustomer()->getCustomAttribute(
            $this->_attribute->getAttributeCode()
        );

        if (!is_null($attribute)) {
            if (!is_null($value = $attribute->getValue())) {
                $data[] = [
                    'file' => $value,
                    'name' => $this->getAttributeLabel(),
                    'url'  => $this->_fileManagement->getFileUrl(
                        $this->getAttributeCode(), $value
                    )
                ];
            }
        }

        return $data;
    }
}