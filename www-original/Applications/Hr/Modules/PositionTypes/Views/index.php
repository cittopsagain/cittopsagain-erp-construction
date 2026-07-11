<script type="text/javascript">
    Ext.define('App.view.positiontype.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.position-type-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['pos_id', 'pos_name', 'pos_desc', 'status'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/PositionTypes/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/PositionTypes/Main/save',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                Ext.Msg.alert('Success', result.message);
                                context.grid.getStore().load();
                            } else {
                                Ext.Msg.alert('Failed', 'Server Error: ' + result.message);
                                context.grid.getStore().load();
                            }
                        },
                        failure: function (response) {
                            var msg = 'Status: ' + response.status + ': ' + response.statusText;
                            Ext.Msg.alert('Error', 'Failed to save changes.<br>' + msg);
                            console.error('Save Failure:', response);
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
            {text: 'ID', dataIndex: 'pos_id', width: 50},
            {
                text: 'Name',
                dataIndex: 'pos_name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Description',
                dataIndex: 'pos_desc',
                flex: 2,
                editor: {
                    xtype: 'textfield',
                    allowBlank: true
                }
            },
            {
                text: 'Status',
                dataIndex: 'status',
                flex: 1,
                editor: {
                    xtype: 'combo',
                    store: ['Active', 'Inactive'],
                    editable: false,
                    allowBlank: false
                }
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying position types {0} - {1} of {2}',
            emptyMsg: "No position types to display"
        },
        tbar: [
            {
                text: 'Add Position Type',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        pos_name: 'New Position Type',
                        pos_desc: '',
                        status: 'Active'
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Position Type',
                itemId: 'removePositionType',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this position type?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/PositionTypes/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('pos_id')},
                                        success: function (response) {
                                            var result = Ext.decode(response.responseText);
                                            if (result.success) {
                                                Ext.Msg.alert('Success', result.message);
                                                store.load();
                                            } else {
                                                Ext.Msg.alert('Failed', 'Server Error: ' + result.message);
                                            }
                                        },
                                        failure: function (response) {
                                            var msg = 'Status: ' + response.status + ': ' + response.statusText;
                                            Ext.Msg.alert('Error', 'Failed to delete position type.<br>' + msg);
                                            console.error('Delete Failure:', response);
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
                this.down('#removePositionType').setDisabled(!records.length);
            }
        }
    });
</script>
