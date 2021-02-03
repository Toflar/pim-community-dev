<?php

namespace Context;

use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectivityContext implements Context, KernelAwareContext
{
    private KernelInterface $kernel;
    private static string $kernelRootDir;

    /**
     * @inheritDoc
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        self::$kernelRootDir = $kernel->getRootDir();
    }

    /**
     * @Given /^(\d+) events of type "([^"]*)" have been raised$/
     */
    public function eventsOfTypeHaveBeenRaised(int $count, string $type): void
    {
        $query = <<<SQL
SELECT COUNT(*) FROM messenger_messages WHERE JSON_EXTRACT(body, '$[0].name') = :type;
SQL;
        $parameters = ['type' => $type];

        $result = (int)$this->kernel->getContainer()->get('database_connection')->executeQuery(
            $query,
            $parameters
        )->fetchColumn();

        Assert::assertEquals($count, $result);
    }
}

