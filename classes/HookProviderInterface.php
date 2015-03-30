<?php
/**
 * Hook provider interface.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */

namespace AudioTheme\Core;

use AudioTheme\Core\Plugin;

/**
 * Hook provider interface.
 *
 * @package AudioTheme\Core
 * @since 2.0.0
 */
interface HookProviderInterface {
    /**
     * Registers hooks for the given plugin.
     *
     * @param Plugin $plugin The main plugin instance.
     */
    public function register_hooks( Plugin $plugin );
}
