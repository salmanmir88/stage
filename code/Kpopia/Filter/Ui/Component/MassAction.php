<?php

namespace Kpopia\Filter\Ui\Component;

class MassAction extends \Magento\Ui\Component\MassAction
{
    /** @var array  */
    protected $removeType = ['pdfdocs_order', 'pdfinvoices_order', 'pdfshipments_order', 'pdfcreditmemos_order'];

    /** @var array  */
    protected $toRemoveAction = ['cancel', 'hold_order', 'unhold_order'];

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getConfiguration();

        // Add child components to the configuration
        foreach ($this->getChildComponents() as $actionComponent) {
            $config['actions'][] = $actionComponent->getConfiguration();
        }

        // Merge the original config with the current one
        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        // New array for actions after filtering
        $newConfigActions = [];

        // Loop through each action in the configuration
        foreach ($config['actions'] as $configItem) {
            // Check if 'type' exists and remove based on either $removeType or $toRemoveAction
            if (
                (isset($configItem['type']) && in_array($configItem['type'], $this->removeType)) ||
                (isset($configItem['type']) && in_array($configItem['type'], $this->toRemoveAction))
            ) {
                // Skip the action if it should be removed
                continue;
            }

            // Keep the action if it doesn't match the removal conditions
            $newConfigActions[] = $configItem;
        }

        // Update the configuration with filtered actions
        $config['actions'] = $newConfigActions;
        // Set the new configuration
        $this->setData('config', $config);
        $this->components = [];

        // Call parent's prepare method
        parent::prepare();
    }
}
