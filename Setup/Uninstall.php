<?php

namespace Salvopruiti\ShippingLimitedProducts\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Module uninstall code
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        /** @var EavSetup $eav_setup */
        $eav_setup = $this->eavSetupFactory->create();

        $eav_setup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "shipping_limited");

        $setup->endSetup();
    }
}
