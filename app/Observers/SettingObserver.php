<?php

namespace App\Observers;

use App\Support\SettingManager;

class SettingObserver
{
    /**
     * The setting manager instance.
     *
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * Constructor.
     */
    public function __construct(SettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * Saved.
     */
    public function saved()
    {
        $this->settingManager->forget();
    }
}
