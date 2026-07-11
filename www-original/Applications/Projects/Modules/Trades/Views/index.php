<script type="text/javascript">
    Ext.define('App.view.trades.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.project-trades-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['trade_id', 'trade_code', 'description', 'long_description'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Trades/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Trades/Main/save',
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
            {text: 'ID', dataIndex: 'trade_id', width: 50},
            {
                text: 'Code',
                dataIndex: 'trade_code',
                width: 150,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Description',
                dataIndex: 'description',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Long Description',
                dataIndex: 'long_description',
                flex: 2,
                editor: {
                    xtype: 'textarea'
                }
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying trades {0} - {1} of {2}',
            emptyMsg: "No trades to display"
        },
        tbar: [
            {
                text: 'Add Trade',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        trade_code: '',
                        description: '',
                        long_description: ''
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Trade',
                itemId: 'removeTrade',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this trade?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Trades/Main/delete',
                                        method: 'POST',
                                        params: {trade_id: record.get('trade_id')},
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
                                            Ext.Msg.alert('Error', 'Failed to delete trade.');
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
                this.down('#removeTrade').setDisabled(!records.length);
            }
        }
    });
</script>
