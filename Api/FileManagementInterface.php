<?php
/**
 * @description File management interface
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace D3p1\CustomerFile\Api;

use Magento\Framework\Exception\LocalizedException;

interface FileManagementInterface
{
    /**
     * Get allowed extensions
     *
     * @param string $attributeCode
     *
     * @return array
     */
    public function getAllowedExtensions($attributeCode);

    /**
     * Upload file
     *
     * @param  string $attributeCode
     * @param  string $scope
     * @return array
     * @throws LocalizedException
     */
    public function upload($attributeCode, $scope);

    /**
     * Get file URL
     *
     * @param  string $attributeCode
     * @param  string $file
     * @return string
     */
    public function getFileUrl($attributeCode, $file);

    /**
     * Get file absolute path
     *
     * @param  string $file
     * @return string
     */
    public function getFileAbsolutePath($file);
}
