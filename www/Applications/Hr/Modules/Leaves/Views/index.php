<script type="text/javascript">
    Ext.define('App.view.leave.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.leave-grid',
        title: '<?php echo $this->escape($title); ?>',
        store: {
            fields: ['id', 'name', 'position'],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Leaves/Main/data',
                reader: {
                    type: 'json'
                }
            },
            autoLoad: true
        },
        plugins: {
            ptype: 'rowediting',
            clicksToEdit: 2,
            listeners: {
                edit: function (editor, context) {
                    var record = context.record;
                    Ext.Ajax.request({
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Leaves/Main/save',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                context.grid.getStore().load();
                            } else {
                                Ext.Msg.alert('Failed', 'Server Error: ' + result.message);
                                context.grid.getStore().load();
                            }
                        },
                        failure: function (response) {
                            var msg = 'Status: ' + response.status + ': ' + response.statusText;
                            Ext.Msg.alert('Error', 'Failed to save changes.<br>' + msg);
                            context.grid.getStore().load();
                        }
                    });
                },
                cancelEdit: function (rowEditing, context) {
                    if (context.record.phantom) {
                        context.grid.getStore().remove(context.record);
                    }
                }
            }
        },
        columns: [
            {text: 'ID', dataIndex: 'id', width: 50},
            {
                text: 'Name',
                dataIndex: 'name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Position',
                dataIndex: 'position',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                xtype: 'actioncolumn',
                width: 80,
                sortable: false,
                menuDisabled: true,
                items: [
                    {
                        iconCls: 'x-fa fa-edit',
                        tooltip: 'Edit Leave',
                        handler: function (grid, rowIndex) {
                            var editing = grid.findPlugin('rowediting');
                            editing.startEdit(rowIndex, 0);
                        }
                    },
                    {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Delete Leave',
                        margin: '0 0 0 10',
                        handler: function (grid, rowIndex) {
                            var record = grid.getStore().getAt(rowIndex);
                            Ext.Msg.confirm('Delete', 'Are you sure you want to delete this record?', function (choice) {
                                if (choice === 'yes') {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Leaves/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('id')},
                                        success: function (response) {
                                            var result = Ext.decode(response.responseText);
                                            if (result.success) {
                                                grid.getStore().load();
                                            } else {
                                                Ext.Msg.alert('Failed', 'Server Error: ' + result.message);
                                            }
                                        },
                                        failure: function (response) {
                                            var msg = 'Status: ' + response.status + ': ' + response.statusText;
                                            Ext.Msg.alert('Error', 'Failed to delete record.<br>' + msg);
                                        }
                                    });
                                }
                            });
                        }
                    }]
            }
        ],
        tbar: [
            {
                text: 'Add Leave',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        name: 'New Name',
                        position: 'New Position'
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            }
        ]
    });
</script>
