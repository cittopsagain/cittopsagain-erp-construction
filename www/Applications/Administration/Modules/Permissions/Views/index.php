<script type="text/javascript">
    Ext.define('App.view.permissions.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.permissions-grid',
        title: 'Permissions Management',
        frame: true,
        height: 600,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'name', 'description'],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/Permissions/Main/data',
                reader: {
                    type: 'json'
                }
            },
            autoLoad: true
        },
        columns: [
            {text: 'ID', dataIndex: 'id', width: 50},
            {text: 'Permission Name', dataIndex: 'name', flex: 1},
            {text: 'Description', dataIndex: 'description', flex: 2},
            {
                xtype: 'actioncolumn',
                width: 50,
                items: [
                    {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Delete Permission',
                        handler: function (grid, rowIndex) {
                            var record = grid.getStore().getAt(rowIndex);
                            Ext.Msg.confirm('Delete', 'Are you sure?', function (choice) {
                                if (choice === 'yes') {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/Permissions/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('id')},
                                        success: function () {
                                            grid.getStore().load();
                                        }
                                    });
                                }
                            });
                        }
                    }
                ]
            }
        ],
        tbar: [
            {
                text: 'Add Permission',
                handler: function () {
                    var grid = this.up('grid');
                    var win = Ext.create('Ext.window.Window', {
                        title: 'Add New Permission',
                        modal: true,
                        width: 400,
                        layout: 'fit',
                        items: [{
                            xtype: 'form',
                            bodyPadding: 10,
                            items: [
                                {
                                    xtype: 'textfield',
                                    name: 'name',
                                    fieldLabel: 'Permission Name',
                                    anchor: '100%',
                                    allowBlank: false
                                },
                                {xtype: 'textarea', name: 'description', fieldLabel: 'Description', anchor: '100%'}
                            ],
                            buttons: [{
                                text: 'Save',
                                handler: function () {
                                    var form = this.up('form').getForm();
                                    if (form.isValid()) {
                                        form.submit({
                                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/Permissions/Main/save',
                                            success: function (f, action) {
                                                Ext.Msg.alert('Success', action.result.message);
                                                win.close();
                                                grid.getStore().load();
                                            },
                                            failure: function (f, action) {
                                                Ext.Msg.alert('Error', action.result.message);
                                            }
                                        });
                                    }
                                }
                            }]
                        }]
                    });
                    win.show();
                }
            }
        ]
    });
</script>
