<?php

use \LCI\Blend\Migrations;

class InstallOrchestrator extends Migrations
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. MODX namespace
        $orchestratorNamespace = $this->modx->getObject('modNamespace', 'orchestrator');

        if (!$orchestratorNamespace) {
            /** @var \modNamespace $orchestratorNamespace */
            $orchestratorNamespace = $this->modx->newObject('modNamespace');
            $orchestratorNamespace->set('name', 'orchestrator');
            $orchestratorNamespace->set('path', '{core_path}orchestrator/');
            $orchestratorNamespace->set('assets_path', '{assets_path}orchestrator/');

            if ($orchestratorNamespace->save()) {
                $this->blender->outSuccess('The modNamespace orchestrator has been created');
            } else {
                $this->blender->out('The modNamespace orchestrator was not created', true);
            }
        }

        // 2. Media source
        $orchestratorMediaSource = $this->blender->getBlendableMediaSource('orchestrator');
        $saved = $orchestratorMediaSource
            ->setFieldDescription('Orchestrator packages')
            ->setPropertyBasePath('core/components/orchestrator/vendor/')
            ->setPropertyBaseUrl('core/components/orchestrator/vendor/')
            ->blend();

        if ($saved) {
            $this->blender->outSuccess('orchestrator MediaSource has been installed');
        } else  {
            $this->blender->out('orchestrator MediaSource was not installed', true);
        }

        // 3. System setting
        /** @var \LCI\Blend\Blendable\SystemSetting $systemSetting */
        $systemSetting = $this->blender->getBlendableSystemSetting('orchestrator.vendor_path');
        $saved = $systemSetting
            ->setSeedsDir($this->getSeedsDir())
            ->setFieldValue(MODX_CORE_PATH.'components/orchestrator/vendor/')
            ->setFieldArea('Composer')
            ->setFieldNamespace('orchestrator')
            ->blend();

        if ($saved) {
            $this->blender->outSuccess('orchestrator.vendor_path System Setting has been created');
        } else {
            $this->blender->out('orchestrator.vendor_path System Setting was not created', true);
        }

        // 4. plugin
        $plugin = $this->blender->getBlendablePlugin('requireComposerAutoloader');

        $saved = $plugin
            ->setFieldCategory('Orchestrator')
            ->setFieldDescription('Will load the composer autoloader.php file inside of MODX')
            ->setAsStatic('lci/orchestrator/src/elements/plugins/requireComposerAutoloader.php', 'Orchestrator')
            ->attachOnEvent('OnInitCulture')
            ->blend();

        if ($saved) {
            $this->blender->outSuccess('requireComposerAutoloader Plugin has been installed');
        } else {
            $this->blender->out('requireComposerAutoloader Plugin has been installed', true);
        }

        $this->modx->cacheManager->refresh();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // remove DB Table:
        $manager = $this->modx->getManager();

        // 4. plugin
        $plugin = $this->blender->getBlendablePlugin('requireComposerAutoloader');

        if ($plugin->revertBlend()) {
            $this->blender->outSuccess('requireComposerAutoloader Plugin has been reverted');
        } else {
            $this->blender->out('requireComposerAutoloader Plugin could not be reverted', true);
        }

        // 3. System setting
        /** @var \LCI\Blend\Blendable\SystemSetting $systemSetting */
        $systemSetting = $this->blender->getBlendableSystemSetting('orchestrator.vendor_path');
        if ($systemSetting->revertBlend()) {
            $this->blender->outSuccess('orchestrator.vendor_path System Setting has been reverted');
        } else {
            $this->blender->out('orchestrator.vendor_path System Setting could not be reverted', true);
        }

        // 2. Media source
        $orchestratorMediaSource = $this->blender->getBlendableMediaSource('orchestrator');
        if ($orchestratorMediaSource->revertBlend()) {
            $this->blender->outSuccess('orchestrator MediaSource has been reverted');
        } else {
            $this->blender->out('orchestrator MediaSource could not be reverted', true);
        }

        // 1. MODX namespace
        /** @var \modNamespace $orchestratorNamespace */
        $orchestratorNamespace = $this->modx->getObject('modNamespace', 'orchestrator');

        if ($orchestratorNamespace instanceof \modNamespace) {
            if ($orchestratorNamespace->remove()) {
                $this->blender->outSuccess('orchestrator modNamespace has been removed');
            } else {
                $this->blender->out('orchestrator modNamespace could not be removed', true);
            }
        }

        $this->modx->cacheManager->refresh();
    }

    /**
     * Method is called on construct, please fill me in
     */
    protected function assignDescription()
    {
        $this->description = 'Install Orchestrator';
    }

    /**
     * Method is called on construct, please fill me in
     */
    protected function assignVersion()
    {
        $this->version = $this->blender->getVersion();
    }

    /**
     * Method is called on construct, can change to only run this migration for those types
     */
    protected function assignType()
    {
        $this->type = 'master';
    }

    /**
     * Method is called on construct, Child class can override and implement this
     */
    protected function assignSeedsDir()
    {
        $this->seeds_dir = '2018_09_03_020202';
    }
}