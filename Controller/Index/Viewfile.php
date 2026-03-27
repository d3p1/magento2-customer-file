<?php
/**
 * @description View file
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace Bina\CustomerFile\Controller\Index;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Customer\Api\CustomerMetadataInterface;
use Bina\CustomerFile\Api\FileManagementInterface;

class Viewfile extends Action
{
    /**
     * @var FileManagementInterface
     */
    protected $_fileManagement;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var Storage
     */
    protected $_storage;

    /**
     * @var RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var DecoderInterface
     */
    protected $_urlDecoder;

    /**
     * Constructor
     *
     * @param FileManagementInterface $fileManagement
     * @param Filesystem              $filesystem
     * @param FileFactory             $fileFactory
     * @param Storage                 $storage
     * @param RawFactory              $resultRawFactory
     * @param DecoderInterface        $urlDecoder
     * @param Context                 $context
     */
    public function __construct(
        FileManagementInterface $fileManagement,
        Filesystem              $filesystem,
        FileFactory             $fileFactory,
        Storage                 $storage,
        RawFactory              $resultRawFactory,
        DecoderInterface        $urlDecoder,
        Context                 $context
    ) {
        $this->_fileManagement   = $fileManagement;
        $this->_filesystem       = $filesystem;
        $this->_fileFactory      = $fileFactory;
        $this->_storage          = $storage;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_urlDecoder       = $urlDecoder;

        parent::__construct($context);
    }

    /**
     * View action
     *
     * @return Raw|void
     * @throws NotFoundException
     */
    public function execute()
    {
        list($file, $plain) = $this->_getFileParams();
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $filename = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER .
                    '/'                                             .
                    ltrim($file, '/');
        $path = $this->_fileManagement->getFileAbsolutePath($file);

        /**
         * @note Validate page exists
         */
        if (mb_strpos($path, '..') !== false || (!$directory->isFile($filename) && !$this->_storage->processStorageFile($path))) {
            throw new NotFoundException(__('Page not found.'));
        }

        if ($plain) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            $stat          = $directory->stat($filename);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /**
             * @var Raw $resultRaw
             */
            $resultRaw = $this->_resultRawFactory->create();

            $resultRaw->setHttpResponseCode(200)->setHeader('Pragma', 'public', true)
                                                ->setHeader('Content-type', $contentType, true)
                                                ->setHeader('Content-Length', $contentLength)
                                                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($filename));
            return $resultRaw;
        }
        else {
            $name = pathinfo($path, PATHINFO_BASENAME);
            $this->_fileFactory->create($name, ['type' => 'filename', 'value' => $filename], DirectoryList::MEDIA);
        }
    }

    /**
     * Get parameters from request
     *
     * @return array
     * @throws NotFoundException
     */
    private function _getFileParams()
    {
        $file  = null;
        $plain = false;

        if ($this->getRequest()->getParam('file')) {
            $file = $this->_urlDecoder->decode($this->getRequest()->getParam('file'));
        }
        elseif ($this->getRequest()->getParam('image')) {
            $file  = $this->_urlDecoder->decode($this->getRequest()->getParam('image'));
            $plain = true;
        }
        else {
            throw new NotFoundException(__('Page not found.'));
        }

        return [$file, $plain];
    }
}