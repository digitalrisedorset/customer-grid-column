<?php
declare(strict_types=1);

namespace Mbs\CustomerColumn\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mbs\CustomerColumn\Model\CustomerSetupHandler;
use Magento\Customer\Setup\CustomerSetupFactory;

class MobileNumberCustomerAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var CustomerSetupHandler
     */
    private $setupHandler;

    /**
     * MobileNumberCustomerAttribute constructor.
     * @param Setup $setupHandler
     */
    public function __construct(
        CustomerSetupHandler $setupHandler,
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setupHandler = $setupHandler;
    }

    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->setupHandler->setCustomerSetup($customerSetup);

        $this->setupHandler->addStaticCustomerAttribute(
            'mobile_number',
            [
                'label' => 'Customer Mobile Number',
                'required' => true,
                'position' => 30,
                'validate_rules' => '{"max_text_length":255,"min_text_length":1}',
                'use_in_grid' => true,
                'visible' => true
            ]
        );
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}

