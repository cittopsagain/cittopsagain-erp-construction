<script type="text/javascript">
    Ext.define('App.view.overheadtypes.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.overhead-types-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'code', 'overhead_type', 'category', 'calculation_method', 'default_rate'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/OverheadTypes/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/OverheadTypes/Main/save',
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
            {text: 'ID', dataIndex: 'id', width: 50},
            {
                text: 'Code',
                dataIndex: 'code',
                width: 100,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Overhead Type',
                dataIndex: 'overhead_type',
                flex: 1,
                minWidth: 200,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Category',
                dataIndex: 'category',
                width: 150,
                editor: {
                    xtype: 'combobox',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/OverheadCategories/Main/all',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'description',
                    valueField: 'description',
                    queryMode: 'local',
                    allowBlank: false
                }
            },
            {
                text: 'Calculation Method',
                dataIndex: 'calculation_method',
                width: 180,
                editor: {
                    xtype: 'combobox',
                    store: ['% of Labor', '% of Total Cost', 'Fixed Amount', 'Actual Cost', '% of Equipment Cost', '% of Direct Cost'],
                    allowBlank: false
                }
            },
            {
                text: 'Default Rate',
                dataIndex: 'default_rate',
                width: 120,
                renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: {
                    xtype: 'numberfield',
                    decimalPrecision: 2,
                    minValue: 0
                }
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying items {0} - {1} of {2}',
            emptyMsg: "No items to display"
        },
        tbar: [
            {
                text: 'Add Overhead Type',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        code: '',
                        overhead_type: '',
                        category: '',
                        calculation_method: '',
                        default_rate: 0
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Overhead Type',
                itemId: 'removeBtn',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this overhead type?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/OverheadTypes/Main/delete',
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
                                            Ext.Msg.alert('Error', 'Failed to delete overhead type.');
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
                this.down('#removeBtn').setDisabled(!records.length);
            }
        }
    });
</script>
