<?php require_once 'Core/Views/header.php'; ?>

<style type="text/css">
    html {
        background: #333 url('<?php echo rtrim(BASE_URL, '/'); ?>/public/assets/images/login-background-1.png') no-repeat right bottom fixed !important;
        background-size: cover !important;
    }

    body, .x-viewport, .x-viewport > .x-body {
        background: transparent !important;
    }

    .x-panel-body-default {
        background: rgba(255, 255, 255, 0.9) !important;
    }

    .x-form-item-label-default {
        color: #333 !important;
        font-weight: bold !important;
    }

    .x-form-field-default {
        background-color: #fff !important;
        color: #000 !important;
    }

    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
</style>

<div id="login-form-container" class="login-container"></div>

<script type="text/javascript">
    Ext.onReady(function () {
        // Force background on the root elements after ExtJS initializes
        var style = 'background: #333 url("<?php echo rtrim(BASE_URL, '/'); ?>/public/assets/images/login-background-1.png") no-repeat right bottom fixed !important; background-size: cover !important;';
        document.documentElement.style.cssText += style;

        // Ensure body and potential ExtJS wrappers are transparent
        document.body.style.background = 'transparent';

        // Override any classes that might be applied by ExtJS themes
        var css = 'body, .x-viewport, .x-viewport > .x-body { background: transparent !important; } ' +
            '.x-panel-body-default { background: rgba(255, 255, 255, 0.9) !important; } ' +
            '.x-form-item-label-default { color: #333 !important; font-weight: bold !important; } ' +
            '.x-form-field-default { background-color: #fff !important; color: #000 !important; }';
        var head = document.head || document.getElementsByTagName('head')[0];
        var styleTag = document.createElement('style');
        styleTag.type = 'text/css';
        if (styleTag.styleSheet) {
            styleTag.styleSheet.cssText = css;
        } else {
            styleTag.appendChild(document.createTextNode(css));
        }
        head.appendChild(styleTag);

        Ext.create('Ext.window.Window', {
            title: '<?php echo APP_NAME; ?>',
            width: 400,
            closable: false,
            draggable: false,
            resizable: false,
            modal: false,
            autoShow: true,
            layout: 'fit',
            bodyStyle: 'background: rgba(255, 255, 255, 0.9);', // Semi-transparent background
            items: {
                xtype: 'form',
                bodyPadding: 30,
                border: false,
                style: 'background: transparent;',
                defaults: {
                    anchor: '100%',
                    labelWidth: 80,
                    allowBlank: false,
                    msgTarget: 'under'
                },
                items: [
                    {
                        xtype: 'textfield',
                        name: 'username',
                        fieldLabel: 'Username',
                        labelStyle: 'color: #333; font-weight: bold;',
                        fieldStyle: 'background: white; color: black;',
                        enableKeyEvents: true,
                        listeners: {
                            keypress: function (field, e) {
                                if (e.getKey() == e.ENTER) {
                                    doLogin(this.up('form'));
                                }
                            },
                            afterrender: function (textfield) {
                                textfield.focus();
                            }
                        }
                    },
                    {
                        xtype: 'textfield',
                        name: 'password',
                        fieldLabel: 'Password',
                        labelStyle: 'color: #333; font-weight: bold;',
                        fieldStyle: 'background: white; color: black;',
                        inputType: 'password',
                        enableKeyEvents: true,
                        listeners: {
                            keypress: function (field, e) {
                                if (e.getKey() == e.ENTER) {
                                    doLogin(this.up('form'));
                                }
                            }
                        }
                    }
                ],
                buttons: [
                    {
                        text: 'Sign In',
                        scale: 'small',
                        formBind: true,
                        handler: function () {
                            doLogin(this.up('form'));
                        }
                    }
                ]
            }
        }).center();

        function doLogin(formPanel) {
            var form = formPanel.getForm();
            if (form.isValid()) {
                form.submit({
                    url: '<?php echo rtrim(BASE_URL, '/'); ?>/Auth/Login/Main/authenticate',
                    waitMsg: 'Authenticating...',
                    success: function (form, action) {
                        window.location.href = '<?php echo rtrim(BASE_URL, '/') . '/'; ?>';
                    },
                    failure: function (form, action) {
                        var message = 'Login failed. Please try again.';
                        if (action.result && action.result.message) {
                            message = action.result.message;
                        }
                        Ext.Msg.alert('Error', message);
                    }
                });
            }
        }
    });
</script>

</body>
</html>
