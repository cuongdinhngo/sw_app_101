<?php declare(strict_types=1);

namespace FreeGift;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use FreeGift\Service\CustomFieldsInstaller;
use FreeGift\Service\CustomFieldsCleaner;

class FreeGift extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        // Do stuff such as creating a new payment method

        $this->getCustomFieldsInstaller()->install($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        // Remove or deactivate the data created by the plugin
        $this->getCustomFieldsCleaner()->execute($uninstallContext->getContext());
    }

    public function activate(ActivateContext $activateContext): void
    {
        // Activate entities, such as a new payment method
        // Or create new entities here, because now your plugin is installed and active for sure

        $this->getCustomFieldsInstaller()->addRelations($activateContext->getContext());
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        // Deactivate entities, such as a new payment method
        // Or remove previously created entities
    }

    public function update(UpdateContext $updateContext): void
    {
        // Update necessary stuff, mostly non-database related
    }

    public function postInstall(InstallContext $installContext): void
    {
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    private function getCustomFieldsInstaller(): CustomFieldsInstaller
    {
        if ($this->container->has(CustomFieldsInstaller::class)) {
            return $this->container->get(CustomFieldsInstaller::class);
        }

        return new CustomFieldsInstaller(
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field_set_relation.repository')
        );
    }

    private function getCustomFieldsCleaner(): CustomFieldsCleaner
    {
        if ($this->container->has(CustomFieldsCleaner::class)) {
            return $this->container->get(CustomFieldsCleaner::class);
        }

        return new CustomFieldsCleaner(
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field_set_relation.repository'),
            $this->container->get('custom_field.repository')
        );
    }
}
