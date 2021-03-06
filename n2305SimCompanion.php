<?php

namespace n2305SimCompanion;

use n2305SimCompanion\Bootstrap\Database;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class n2305SimCompanion extends Plugin
{
    public const PLUGIN_NAME = 'n2305SimCompanion';

    public function install(InstallContext $installContext)
    {
        $database = new Database(
            $this->container->get('models')
        );

        $database->install();

        /** @var CrudServiceInterface $crudService */
        $crudService = $this->container->get('shopware_attribute.crud_service');
        $crudService->update('s_articles_attributes', 'availability', 'text');

        $this->regenerateAttributeModels(['s_articles_attributes']);
    }

    public function uninstall(UninstallContext $uninstallContext)
    {
        $database = new Database(
            $this->container->get('models')
        );

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $database->uninstall();

        /** @var CrudServiceInterface $crudService */
        $crudService = $this->container->get('shopware_attribute.crud_service');
        $crudService->delete('s_articles_attributes', 'availability');

        $this->regenerateAttributeModels(['s_articles_attributes']);
    }

    private function regenerateAttributeModels(array $attributeTables): void
    {
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();

        Shopware()->Models()->generateAttributeModels($attributeTables);
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_ProductStream' => 'extendProductStream',
        ];
    }

    public function extendProductStream(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_ProductStream $subject */
        $subject = $args->getSubject();

        $subject->View()->addTemplateDir(__DIR__ . '/Resources/views');

        $subject->View()->extendsTemplate('backend/product_stream/n2305_sim_companion_product_stream_extension.js');
    }
}
