<?php
declare(strict_types=1);

namespace Mbs\CustomerColumn\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class CustomerSetupHandler
{
    /**
     * @var CustomerSetup
     */
    private $customerSetup;
    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param $customerSetup
     */
    public function setCustomerSetup($customerSetup)
    {
        $this->customerSetup = $customerSetup;
    }

    /**
     * @param string $attributeCode
     * @param array $customerAttributeInfo
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function addStaticCustomerAttribute(string $attributeCode, array $customerAttributeInfo)
    {
        $customerAttributeInfo = $this->getValidatedCustomerInfo($customerAttributeInfo);

        $this->customerSetup->addAttribute(
            Customer::ENTITY,
            $attributeCode,
            [
                'type' => 'static',
                'label' => $customerAttributeInfo['label'],
                'input' => 'text',
                'required' => $customerAttributeInfo['required'],
                'position' => $customerAttributeInfo['position'],
                'sort_order' => $customerAttributeInfo['position'],
                'visible' => $customerAttributeInfo['visible'],
                'system' => false,
                'user_defined' => true,
                'validate_rules' => $customerAttributeInfo['validate_rules'],
                'backend_model' => $customerAttributeInfo['backend_model']
            ]
        );

        $newAttribute = $this->eavConfig->getAttribute(
            Customer::ENTITY,
            $attributeCode
        );
        $customerEntity = $this->customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $this->customerSetup->getDefaultAttributeSetId($customerEntity->getEntityTypeId());
        $attributeGroup = $this->customerSetup->getDefaultAttributeGroupId(
            $customerEntity->getEntityTypeId(),
            $attributeSetId
        );

        $this->customerSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            $attributeGroup,
            $attributeCode
        );

        $newAttribute->setData('used_in_forms', [
            'adminhtml_customer'
        ]);
        $newAttribute->save();

        if ($customerAttributeInfo['use_in_grid']) {
            $this->customerSetup->upgradeAttributes([
                'customer' => [
                    $attributeCode => [
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => true,
                    ]
                ]
            ]);
        }
    }

    /**
     * @param array $customerAttributeInfo
     * @return array
     */
    private function getValidatedCustomerInfo(array $customerAttributeInfo)
    {
        if (!isset($customerAttributeInfo['label'])) {
            throw new LocalizedException(__('The Attrinute label is required'));
        }

        if (!isset($customerAttributeInfo['required'])) {
            $customerAttributeInfo['required'] = false;
        }

        if (!isset($customerAttributeInfo['visible'])) {
            $customerAttributeInfo['visible'] = false;
        }

        if (!isset($customerAttributeInfo['position'])) {
            $customerAttributeInfo['position'] = 0;
        }

        if (!isset($customerAttributeInfo['validate_rules'])) {
            $customerAttributeInfo['validate_rules'] = '';
        }

        if (!isset($customerAttributeInfo['use_in_grid'])) {
            $customerAttributeInfo['use_in_grid'] = false;
        }

        if (!isset($customerAttributeInfo['backend_model'])) {
            $customerAttributeInfo['backend_model'] = null;
        }

        return $customerAttributeInfo;
    }
}
