<?php

namespace gogax\ComposerAuditPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreCommandRunEvent;
use Composer\Repository\RepositoryFactory;

class ComposerAuditPlugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {

    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::PRE_COMMAND_RUN => 'onPreCommandRun',
        ];
    }

    public function onPreCommandRun(PreCommandRunEvent $event): void
    {
        $command = $event->getCommand();

        // Если выполняется команда "audit", включаем Packagist
        if ($command === 'audit') {
            $this->enablePackagist();
        }
    }

    protected function enablePackagist(): void
    {
        $composer = $this->composer;
        $repositoryManager = $composer->getRepositoryManager();

        foreach ($composer->getRepositoryManager()->getRepositories() as $repository) {
            if ($repository->getRepoName() == "composer repo (https://repo.packagist.org)") {
                return;
            }
        }

        $repositoryManager->prependRepository(
            RepositoryFactory::createRepo($this->io, $this->composer->getConfig(), [
                'type' => 'composer',
                'url' => 'https://repo.packagist.org',
            ])
        );
    }

}