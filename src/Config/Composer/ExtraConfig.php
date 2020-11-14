<?php

declare(strict_types=1);

namespace SimpleAsFuck\Orm\Config\Composer;

use Composer\Composer;
use SimpleAsFuck\Orm\Config\Abstracts\Config;

class ExtraConfig extends Config
{
    /** @var Composer */
    private Composer $composer;
    private Config $defaults;

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
        $this->defaults = new \SimpleAsFuck\Orm\Config\Defaults\Config();
    }

    /**
     * @return mixed
     */
    protected function getValue(string $key)
    {
        $extra = $this->composer->getPackage()->getExtra();
        if (
            ! array_key_exists('orm-generator-config', $extra)
            ||
            ! is_array($extra['orm-generator-config'])
            ||
            ! array_key_exists($key, $extra['orm-generator-config'])
        ) {
            return $this->defaults->getValue($key);
        }

        return $extra['orm-generator-config'][$key];
    }
}
