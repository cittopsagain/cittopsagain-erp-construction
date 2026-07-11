<script type="text/javascript">
    Ext.define('App.view.projectComponents.Main', {
        extend: 'Ext.panel.Panel',
        alias: 'widget.project-components-main',
        frame: true,
        draggable: true,
        resizable: true,
        layout: 'border',
        height: 700,
        items: [
            {
                region: 'north',
                xtype: 'project-components-grid',
                height: '50%',
                split: true,
                title: 'Project Components'
            },
            {
                region: 'center',
                xtype: 'project-component-items-grid',
                height: '50%',
                units: <?php echo isset($units) ? json_encode($units) : '[]'; ?>
            }
        ]
    });

    Ext.define('App.view.projectComponents.ItemGrid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.project-component-items-grid',
        title: 'Project Component Items',
        store: {
            fields: ['id', 'component_id', 'component_code', 'component_description', 'item_code', 'description', 'unit', 'price'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/itemsData',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
                }
            },
            autoLoad: false
        },
        units: <?php echo isset($units) ? json_encode($units) : '[]'; ?>,
        plugins: {
            ptype: 'rowediting',
            clicksToEdit: 2,
            listeners: {
                edit: function (editor, context) {
                    var record = context.record;
                    var grid = context.grid;
                    var mainGrid = grid.up('panel').down('project-components-grid');
                    var componentRecord = mainGrid ? mainGrid.getSelectionModel().getSelection()[0] : null;

                    if (componentRecord) {
                        record.set('component_id', componentRecord.get('component_id'));
                    }

                    Ext.Ajax.request({
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/saveItem',
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
                text: 'Item Code',
                dataIndex: 'item_code',
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
                text: 'Unit',
                dataIndex: 'unit',
                width: 100,
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['id', 'unit_code', 'description'],
                        data: []
                    },
                    displayField: 'unit_code',
                    valueField: 'unit_code',
                    queryMode: 'local',
                    allowBlank: false,
                    listeners: {
                        beforerender: function (combo) {
                            var grid = combo.up('grid');
                            if (grid && grid.units) {
                                combo.getStore().loadData(grid.units);
                            }
                        }
                    }
                }
            },
            {
                text: 'Price',
                dataIndex: 'price',
                width: 120,
                xtype: 'numbercolumn',
                format: '0,000.00',
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
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
                text: 'Add Item',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var mainGrid = grid.up('panel').down('project-components-grid');
                    var componentRecord = mainGrid ? mainGrid.getSelectionModel().getSelection()[0] : null;

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        component_id: componentRecord ? componentRecord.get('component_id') : '',
                        item_code: '',
                        description: '',
                        unit: 'lot',
                        price: 0
                    });

                    store.insert(store.getCount(), r);
                    editing.startEdit(r, 0);
                }
            },
            {
                text: 'Remove Item',
                itemId: 'removeItem',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this item?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/deleteItem',
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
                                            Ext.Msg.alert('Error', 'Failed to delete item.');
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
                this.down('#removeItem').setDisabled(!records.length);
            }
        }
    });

    Ext.define('App.view.projectComponents.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.project-components-grid',
        store: {
            fields: ['component_id', 'component_code', 'description', 'display_order'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/save',
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
            {text: 'ID', dataIndex: 'component_id', width: 50},
            {
                text: 'Order',
                dataIndex: 'display_order',
                width: 70,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    minValue: 0
                }
            },
            {
                text: 'Code',
                dataIndex: 'component_code',
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
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying components {0} - {1} of {2}',
            emptyMsg: "No components to display"
        },
        tbar: [
            {
                text: 'Add Component',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        component_code: '',
                        description: '',
                        display_order: store.getCount() + 1
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Component',
                itemId: 'removeComponent',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this component?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/delete',
                                        method: 'POST',
                                        params: {component_id: record.get('component_id')},
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
                                            Ext.Msg.alert('Error', 'Failed to delete component.');
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
                this.down('#removeComponent').setDisabled(!records.length);
                var detailGrid = this.up('panel').down('project-component-items-grid');
                if (records.length > 0) {
                    var record = records[0];
                    if (!record.phantom) {
                        detailGrid.setDisabled(false);
                        detailGrid.setTitle('Items for: ' + record.get('component_code') + ' - ' + record.get('description'));
                        var store = detailGrid.getStore();
                        store.getProxy().setExtraParam('component_id', record.get('component_id'));
                        store.load();
                    } else {
                        detailGrid.setDisabled(true);
                        detailGrid.setTitle('Project Component Items');
                        detailGrid.getStore().removeAll();
                    }
                } else {
                    detailGrid.setDisabled(true);
                    detailGrid.setTitle('Project Component Items');
                    detailGrid.getStore().removeAll();
                }
            }
        }
    });
</script>
