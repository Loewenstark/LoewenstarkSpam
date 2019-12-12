<?php

namespace LoewenstarkSpam\Tests;

use LoewenstarkSpam\LoewenstarkSpam as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'LoewenstarkSpam' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['LoewenstarkSpam'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
