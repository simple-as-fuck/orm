<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Generator\Command;

use Composer\Script\Event;
use SimpleAsFuck\Orm\Config\Composer\ExtraConfig;
use SimpleAsFuck\Orm\Generator\Generator;

final class Composer
{
    public static function mysqlGenerate(Event $event): void
    {
        $config = new ExtraConfig($event->getComposer());
        $generator = Generator::createMysql($config);
        $generator->generate(static::stupidityCheck($event));
    }

    public static function mysqlCheck(Event $event): void
    {
        $config = new ExtraConfig($event->getComposer());
        $generator = Generator::createMysql($config);
        $generator->check(static::stupidityCheck($event));
    }

    /**
     * function will check developer stupidity if is necessary add some additional comments or checks into generated code
     */
    private static function stupidityCheck(Event $event): bool
    {
        if (! $event->isDevMode()) {
            if (! $event->getIO()->isInteractive()) {
                return false;
            }

            if (! $event->getIO()->askConfirmation('Running this command in not dev mode is good to not be stupid. Are you stupid? (yes/no) ', true)) {
                return false;
            }

            $event->getIO()->write('Stupidity check failed argument --no-dev is ignored.');
        }

        $arguments = $event->getArguments();
        $firstArgument = array_key_first($arguments) !== null ? $arguments[array_key_first($arguments)] : null;
        if ($firstArgument === '--i-am-not-stupid') {
            if (! $event->getIO()->isInteractive()) {
                return false;
            }

            if (! $event->getIO()->askConfirmation('You claim than you ara not stupid, so are you really stupid? (yes/no) ', true)) {
                return false;
            }

            $event->getIO()->write('Stupidity check failed argument --i-am-not-stupid is ignored.');
        }

        return true;
    }
}
