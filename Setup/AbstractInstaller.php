<?php
/**
 * @description Customer file attribute installer
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
namespace Bina\CustomerFile\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Model\Customer;

abstract class AbstractInstaller implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $_customerSetupFactory;

    /**
     * @var SetFactory
     */
    private $_attributeSetFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $_moduleDataSetup;

    /**
     * Constructor
     *
     * @param CustomerSetupFactory     $customerSetupFactory
     * @param SetFactory               $attributeSetFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        CustomerSetupFactory     $customerSetupFactory,
        SetFactory               $attributeSetFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->_customerSetupFactory = $customerSetupFactory;
        $this->_attributeSetFactory  = $attributeSetFactory;
        $this->_moduleDataSetup      = $moduleDataSetup;
    }

    /**
     * Get attribute code
     *
     * @return string
     */
    abstract public function getAttributeCode();

    /**
     * Get attribute label
     *
     * @return string
     */
    abstract public function getAttributeLabel();

    /**
     * Get attribute allowed extensions
     *
     * @return array
     */
    abstract public function getAttributeAllowedExtensions();

    /**
     * {@inheritDoc}
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->_customerSetupFactory->create(['setup' => $this->_moduleDataSetup]);

        $attributeCode  = $this->getAttributeCode();
        $attributeLabel = $this->getAttributeLabel();
        $fileExtensions = implode(',', $this->getAttributeAllowedExtensions());

        $customerSetup->addAttribute(
            Customer::ENTITY,
            $attributeCode,
            [
                'type'           => 'varchar',
                'label'          => $attributeLabel,
                'input'          => 'file',
                'validate_rules' => '{"file_extensions":"' . $fileExtensions . '"}',
                'required'       => false,
                'system'         => false,
                'sort_order'     => 100
            ]
        );

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var Set $attributeSet */
        $attributeSet     = $this->_attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        /**
         * @note Add attribute set ID and attribute group ID to attribute
         * @note Associate attribute to forms
         */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode)
                                                   ->addData([
                                                        'attribute_set_id'   => $attributeSetId,
                                                        'attribute_group_id' => $attributeGroupId,
                                                        'used_in_forms'      => ['adminhtml_customer', 'customer_account_edit']
                                                   ]);

        $attribute->save();
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return [];
    }
}