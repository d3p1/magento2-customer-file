<?php
/**
 * @description File management model
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace Bina\CustomerFile\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileUploaderFactory;
use Magento\Customer\Model\FileUploader;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\FileProcessor;
use Bina\CustomerFile\Api\FileManagementInterface;

class FileManagement implements FileManagementInterface
{
    /**
     * @var AttributeMetadataInterface
     */
    protected $_customerMetadataService;

    /**
     * @var FileUploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var FileProcessorFactory
     */
    protected $_fileProcessorFactory;

    /**
     * @var ReadInterface
     */
    protected $_mediaDirectory;

    /**
     * Constructor
     *
     * @param CustomerMetadataInterface $customerMetadataService
     * @param FileUploaderFactory       $fileUploaderFactory
     * @param FileProcessorFactory      $fileProcessorFactory
     * @param Filesystem                $filesystem
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadataService,
        FileUploaderFactory       $fileUploaderFactory,
        FileProcessorFactory      $fileProcessorFactory,
        Filesystem                $filesystem
    ) {
        $this->_customerMetadataService = $customerMetadataService;
        $this->_fileUploaderFactory     = $fileUploaderFactory;
        $this->_fileProcessorFactory    = $fileProcessorFactory;
        $this->_mediaDirectory          = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllowedExtensions($attributeCode)
    {
        $allowedExtensions = [];
        $validationRules   = $this->_getAttributeMetadata($attributeCode)
                                  ->getValidationRules();

        foreach ($validationRules as $validationRule) {
            if ($validationRule->getName() == 'file_extensions') {
                $allowedExtensions = explode(',', $validationRule->getValue());

                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });

                break;
            }
        }

        return $allowedExtensions;
    }

    /**
     * {@inheritDoc}
     */
    public function upload($attributeCode, $scope)
    {
        /**
         * @var FileUploader $fileUploader
         */
        $fileUploader = $this->_fileUploaderFactory->create([
            'attributeMetadata' => $this->_getAttributeMetadata($attributeCode),
            'entityTypeCode'    => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'scope'             => $scope
        ]);

        $errors = $fileUploader->validate();
        if (true !== $errors) {
           throw new LocalizedException(__(implode('-', $errors)));
        }

        $result = $fileUploader->upload();

        /**
         * @note Add file URL
         * @note Fix file URL. For some reason, it is used the file name
         *       instead of the file path uploaded.
         *       If the customer uploads a file with a name of a
         *       file already used, the first file that uses this name is shown
         * @see  FileUploader::upload()
         */
        $result['url'] = $this->getFileUrl(
            $attributeCode, FileProcessor::TMP_DIR . '/' . ltrim($result['file'], '/')
        );

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getFileUrl($attributeCode, $file)
    {
        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->_fileProcessorFactory->create(
            ['entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER]
        );
        return $fileProcessor->getViewUrl(
            $file,
            $this->_getAttributeMetadata($attributeCode)->getFrontendInput()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFileAbsolutePath($file)
    {
        $filename = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER .
                    DIRECTORY_SEPARATOR                             .
                    ltrim($file, DIRECTORY_SEPARATOR);
        return $this->_mediaDirectory->getAbsolutePath($filename);
    }

    /**
     * Get attribute metadata
     *
     * @param  string $attributeCode
     * @return AttributeMetadataInterface
     */
    private function _getAttributeMetadata($attributeCode)
    {
        return $this->_customerMetadataService->getAttributeMetadata(
            $attributeCode
        );
    }
}