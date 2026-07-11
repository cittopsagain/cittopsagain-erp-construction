<!-- Main Centralized Layout with Ext JS Border Layout matching screenshot -->
<script type="text/javascript">
    Ext.onReady(function () {
        Ext.tip.QuickTipManager.init();
        var navStore = Ext.create('Ext.data.TreeStore', {
            root: {
                expanded: true,
                children: [
                    <?php foreach ($navigation as $app): ?>
                    {
                        text: <?php echo $this->escapeJs($app['text']); ?>,
                        expanded: true,
                        iconCls: <?php echo $this->escapeJs($app['iconCls'] ?? 'x-fa fa-folder'); ?>,
                        children: [
                            <?php foreach ($app['modules'] as $module): ?>
                            <?php if (isset($module['isGroup']) && $module['isGroup']): ?>
                            {
                                text: <?php echo $this->escapeJs($module['text']); ?>,
                                expanded: true,
                                iconCls: <?php echo $this->escapeJs($module['iconCls'] ?? 'x-fa fa-folder'); ?>,
                                children: [
                                    <?php foreach ($module['children'] as $child): ?>
                                    {
                                        text: <?php echo $this->escapeJs($child['text']); ?>,
                                        leaf: true,
                                        iconCls: <?php echo $this->escapeJs($child['iconCls'] ?? 'x-fa fa-file-o'); ?>,
                                        moduleUrl: <?php echo $this->escapeJs(rtrim(BASE_URL, '/') . '/' . $app['app'] . '/' . $child['id']); ?>
                                    },
                                    <?php endforeach; ?>
                                ]
                            },
                            <?php else: ?>
                            {
                                text: <?php echo $this->escapeJs($module['text']); ?>,
                                leaf: true,
                                iconCls: <?php echo $this->escapeJs($module['iconCls'] ?? 'x-fa fa-file-o'); ?>,
                                moduleUrl: <?php echo $this->escapeJs(rtrim(BASE_URL, '/') . '/' . $app['app'] . '/' . $module['id']); ?>
                            },
                            <?php endif; ?>
                            <?php endforeach; ?>
                        ]
                    },
                    <?php endforeach; ?>
                ]
            }
        });

        Ext.create('Ext.container.Viewport', {
            layout: 'border',
            items: [{
                region: 'west',
                collapsible: true,
                split: true,
                title: 'Navigation',
                width: 250,
                minWidth: 150,
                maxWidth: 400,
                xtype: 'treepanel',
                id: 'nav-tree',
                rootVisible: false,
                store: navStore,
                listeners: {
                    itemclick: function (view, record) {
                        if (record.isLeaf()) {
                            var centerRegion = Ext.getCmp('main-center-region');
                            var breadcrumb = Ext.getCmp('main-breadcrumb');

                            breadcrumb.setSelection(record);

                            centerRegion.mask('Loading...');

                            Ext.Ajax.request({
                                url: record.get('moduleUrl'),
                                params: {
                                    ajax: 1
                                },
                                success: function (response) {
                                    centerRegion.unmask();
                                    var data = Ext.decode(response.responseText);

                                    // Clear current content
                                    centerRegion.removeAll();

                                    // If there is HTML content, we need to process it.
                                    // If it contains <script> tags, we need to make sure they are executed.
                                    if (data.content_html) {
                                        var div = document.createElement('div');
                                        div.innerHTML = data.content_html;
                                        var scripts = div.getElementsByTagName('script');
                                        for (var i = 0; i < scripts.length; i++) {
                                            eval(scripts[i].text);
                                        }
                                    }

                                    var newItems = [];
                                    if (data.content_xtype) {
                                        newItems.push({
                                            xtype: data.content_xtype,
                                            anchor: '100%'
                                        });
                                    } else if (data.content_html) {
                                        newItems.push({
                                            html: data.content_html,
                                            anchor: '100%'
                                        });
                                    }

                                    centerRegion.add({
                                        xtype: 'container',
                                        padding: 20,
                                        layout: 'anchor',
                                        items: [
                                            {
                                                xtype: 'box',
                                                // autoEl: {tag: 'p', html: 'This module was loaded via AJAX'},
                                                style: 'color: #666;',
                                                margin: '0 0 20 0'
                                            }
                                        ].concat(newItems)
                                    });

                                    if (data.title) {
                                        // centerRegion.setTitle(data.title); // This will change the center title based on the module name
                                    }
                                },
                                failure: function () {
                                    centerRegion.unmask();
                                    Ext.Msg.alert('Error', 'Failed to load module.');
                                }
                            });
                        }
                    }
                }
            }, {
                region: 'center',
                xtype: 'panel',
                title: '<?php echo APP_NAME; ?> - Information System',
                layout: 'fit',
                id: 'main-center-region',
                tools: [
                    {
                        xtype: 'label',
                        text: 'Welcome, <?php echo isset($_SESSION['user_name']) ? $this->escape($_SESSION['user_name']) : 'Guest'; ?>',
                        margin: '0 10 0 0',
                        style: 'color: white; font-weight: bold;'
                    },
                    <?php if (isset($_SESSION['user_id'])): ?>
                    {
                        type: 'logout',
                        tooltip: 'Logout',
                        callback: function () {
                            Ext.Msg.confirm('Logout', 'Are you sure you want to logout?', function (btn) {
                                if (btn === 'yes') {
                                    window.location.href = '<?php echo rtrim(BASE_URL, '/'); ?>/Auth/Login/Main/logout';
                                }
                            });
                        }
                    }
                    <?php endif; ?>
                ],
                tbar: {
                    xtype: 'breadcrumb',
                    id: 'main-breadcrumb',
                    store: navStore,
                    showIcons: true,
                    listeners: {
                        change: function (breadcrumb, node) {
                            if (node && node.isLeaf()) {
                                // If the node was changed via breadcrumb clicks, we might want to trigger the same logic as tree click
                                // But to avoid infinite loops or double loading, we can check if it's already selected
                                var tree = Ext.getCmp('nav-tree');
                                var selection = tree.getSelectionModel().getSelection()[0];
                                if (selection !== node) {
                                    tree.getSelectionModel().select(node);
                                    // Trigger navigation logic
                                    tree.fireEvent('itemclick', tree.getView(), node);
                                }
                            }
                        }
                    }
                },
                items: [{
                    xtype: 'container',
                    id: 'main-content-container',
                    padding: 20,
                    layout: 'anchor',
                    items: [
                        {
                            xtype: 'box',
                            autoEl: {tag: 'p', html: 'Module content below'},
                            style: 'color: #666;',
                            margin: '0 0 20 0'
                        },
                        // Inject the actual module content below the "Main Page" text
                        <?php if (isset($content_xtype)): ?>
                        {xtype: <?php echo $this->escapeJs($content_xtype); ?>, anchor: '100%'}
                        <?php elseif (isset($content_html)): ?>
                        {html: <?php echo $this->escapeJs($content_html); ?>, anchor: '100%'}
                        <?php endif; ?>
                    ]
                }]
            }, {
                region: 'south',
                xtype: 'box',
                height: 30,
                padding: '5 10',
                style: 'background-color: #f5f5f5; border-top: 1px solid #d0d0d0;',
                html: '&copy; <?php echo date('Y') . ' ' . APP_NAME; ?>. All Rights Reserved.'
            }]
        });
        // Set initial selection
        var currentApp = <?php echo isset($current_app) ? $this->escapeJs($current_app) : 'null'; ?>;
        var currentModule = <?php echo isset($current_module) ? $this->escapeJs($current_module) : 'null'; ?>;


        if (currentApp && currentModule) {
            var appDisplayText = currentApp;
            var appMap = <?php echo json_encode($app_map ?? []); ?>;
            if (appMap[currentApp]) {
                appDisplayText = appMap[currentApp];
            }
            var appNode = navStore.getRoot().findChild('text', appDisplayText);
            if (appNode) {
                // Find module display text from config or map
                var moduleDisplayText = currentModule;

                // We don't have easy access to getModuleDisplayName here, 
                // but we can search by moduleUrl or traverse the tree
                var moduleUrl = '<?php echo rtrim(BASE_URL, '/'); ?>/' + currentApp + '/' + currentModule;
                var moduleNode = null;

                appNode.cascadeBy(function (node) {
                    if (node.get('moduleUrl') === moduleUrl) {
                        moduleNode = node;
                        return false;
                    }
                });

                if (moduleNode) {
                    Ext.getCmp('main-breadcrumb').setSelection(moduleNode);
                    Ext.getCmp('nav-tree').getSelectionModel().select(moduleNode);
                }
            }
        }

        <?php if (isset($_SESSION['user_id'])): ?>
        /**
         * Inactivity Logout Logic
         * Automatically logs out the user after a period of inactivity.
         */
        var inactivityTime = <?php echo INACTIVITY_TIME; ?>; // Time in milliseconds from config.php
        var inactivityThrottle = <?php echo INACTIVITY_THROTTLE; ?>; // Throttle time in milliseconds from config.php
        var timeoutId;
        var logoutTriggered = false;
        var lastResetTime = 0;

        /**
         * Resets the inactivity timer.
         * Called on various user interaction events to keep the session alive.
         */
        function resetTimer() {
            if (logoutTriggered) return;

            var now = Date.now();
            // Throttle resets to avoid overhead from frequent mouse movements
            if (now - lastResetTime < inactivityThrottle) return;
            lastResetTime = now;

            clearTimeout(timeoutId);
            timeoutId = setTimeout(logoutDueToInactivity, inactivityTime);
        }

        /**
         * Handles the logout process when the inactivity timeout is reached.
         * Shows an alert to the user and then redirects to the logout endpoint.
         */
        function logoutDueToInactivity() {
            if (logoutTriggered) return;
            logoutTriggered = true;

            // Show alert message using Ext JS
            var minutes = Math.floor(inactivityTime / 60000);
            var message = 'You have been logged out because of no activity';

            Ext.Msg.alert('Session Expired', message, function () {
                window.location.href = '<?php echo rtrim(BASE_URL, '/'); ?>/Auth/Login/Main/logout';
            });
        }

        // Register event listeners to track user activity and reset the timer
        window.addEventListener('load', resetTimer, true);
        window.addEventListener('mousemove', resetTimer, true);
        window.addEventListener('mousedown', resetTimer, true); // Catches mouse clicks and touchscreen presses
        window.addEventListener('touchstart', resetTimer, true); // Catches touchscreen swipes and taps
        window.addEventListener('click', resetTimer, true);     // Catches touchpad and mouse clicks
        window.addEventListener('keypress', resetTimer, true);  // Catches keyboard input
        window.addEventListener('scroll', resetTimer, true);    // Catches page scrolling

        // Initial timer start
        resetTimer();
        <?php endif; ?>

    });
</script>
