<script type="text/javascript">
    Ext.define('App.view.systems.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.project-systems-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['system_id', 'system_code', 'description', 'long_description', 'trade_id', 'trade_name', 'trade_code'],
            pageSize: 25,
            groupField: 'trade_name',
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Systems/Main/data',
                extraParams: {
                    _dc: new Date().getTime()
                },
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
                    var data = record.getData();

                    // If it's a new record and system_id is 0, don't send it or send it as null/empty
                    if (record.phantom || data.system_id === 0) {
                        delete data.system_id;
                    }

                    Ext.Ajax.request({
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Systems/Main/save',
                        method: 'POST',
                        params: data,
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
        features: [{
            ftype: 'grouping',
            groupHeaderTpl: 'Trade: {name}',
            hideGroupedHeader: true,
            startCollapsed: false
        }],
        columns: [
            {text: 'ID', dataIndex: 'system_id', width: 50},
            {
                text: 'Trade',
                dataIndex: 'trade_id',
                width: 150,
                renderer: function (value, metaData, record) {
                    return record.get('trade_name') || record.get('trade_code') || value;
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['trade_id', 'description', 'trade_code'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Trades/Main/all',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: true
                    },
                    displayField: 'description',
                    valueField: 'trade_id',
                    queryMode: 'local',
                    allowBlank: false,
                    forceSelection: true,
                    listeners: {
                        select: function (combo, record) {
                            var gridRecord = this.up('grid').getSelectionModel().getSelection()[0];
                            if (gridRecord) {
                                gridRecord.set('trade_name', record.get('description'));
                                gridRecord.set('trade_code', record.get('trade_code'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Code',
                dataIndex: 'system_code',
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
            displayMsg: 'Displaying systems {0} - {1} of {2}',
            emptyMsg: "No systems to display"
        },
        tbar: [
            {
                text: 'Add System',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        system_id: 0,
                        system_code: '',
                        description: '',
                        long_description: '',
                        trade_id: null,
                        trade_name: '',
                        trade_code: ''
                    });
                    r.phantom = true;

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove System',
                itemId: 'removeSystem',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this system?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Systems/Main/delete',
                                        method: 'POST',
                                        params: {system_id: record.get('system_id')},
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
                                            Ext.Msg.alert('Error', 'Failed to delete system.');
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
                this.down('#removeSystem').setDisabled(!records.length);
            }
        }
    });
</script>
