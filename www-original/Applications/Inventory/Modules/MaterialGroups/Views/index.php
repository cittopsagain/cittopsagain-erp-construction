<script type="text/javascript">
    Ext.define('App.view.materialgroups.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.material-groups-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'group_code', 'group_name', 'date_created'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/MaterialGroups/Main/data',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/MaterialGroups/Main/save',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                Ext.Msg.alert('Success', result.message);
                                context.grid.getStore().load();
                            } else {
                                Ext.Msg.alert('Failed', result.message);
                                context.grid.getStore().load();
                            }
                        },
                        failure: function (response) {
                            Ext.Msg.alert('Error', 'Failed to save changes.');
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
            {text: 'ID', dataIndex: 'id', width: 100},
            {
                text: 'Group Code',
                dataIndex: 'group_code',
                width: 150,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Group Name',
                dataIndex: 'group_name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Date Created',
                dataIndex: 'date_created',
                width: 150
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying material groups {0} - {1} of {2}',
            emptyMsg: "No material groups to display"
        },
        tbar: [
            {
                text: 'Add Group',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        group_code: '',
                        group_name: ''
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Group',
                itemId: 'removeGroup',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this material group?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/MaterialGroups/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('id')},
                                        success: function (response) {
                                            var result = Ext.decode(response.responseText);
                                            if (result.success) {
                                                Ext.Msg.alert('Success', result.message);
                                                store.load();
                                            } else {
                                                Ext.Msg.alert('Failed', result.message);
                                            }
                                        },
                                        failure: function (response) {
                                            Ext.Msg.alert('Error', 'Failed to delete material group.');
                                        }
                                    });
                                }
                            }
                        });
                    }
                },
                disabled: true
            }
        ],
        listeners: {
            selectionchange: function (model, records) {
                this.down('#removeGroup').setDisabled(!records.length);
            }
        }
    });
</script>
