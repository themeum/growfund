<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Supports\Assets;

$config = Assets::get_growfund_config_script();

?>

<div id="growfund-root" style="position: relative;"></div>

<?php if (growfund_is_dev_mode()) : ?>
    <script type="module">
        import RefreshRuntime from 'http://localhost:5173/@react-refresh';
        RefreshRuntime.injectIntoGlobalHook(window);
        window.$RefreshReg$ = () => {};
        window.$RefreshSig$ = () => (type) => type;
        window.__vite_plugin_react_preamble_installed__ = true;
    </script>
    <script type="module" src="http://localhost:5173/@vite/client"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- ignore in development mode ?>
    <script type="module" src="http://localhost:5173/growfund/src/main.tsx"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- ignore in development mode ?>
    
    <?php if (growfund_app_features()->is_pro()) : ?>
        <script type="module" src="http://localhost:5173/growfund-pro/src/index.ts"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
    <?php endif; ?>

    <script>
        <?php echo $config; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is already escaped. this is intentional ?>
    </script>
<?php endif; ?>