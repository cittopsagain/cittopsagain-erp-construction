<style>
    .x-form-plus-trigger {
        font-family: 'FontAwesome';
    }

    .x-form-plus-trigger:before {
        content: "\f067";
    }
</style>
<script type="text/javascript">
    Ext.define('App.view.units.Window', {
        extend: 'Ext.window.Window',
        alias: 'widget.units-window',
        title: 'Add New Unit',
        width: 400,
        layout: 'fit',
        modal: true,
        combo: null,

        initComponent: function () {
            var me = this;

            me.items = {
                xtype: 'form',
                bodyPadding: 15,
                defaults: {
                    xtype: 'textfield',
                    anchor: '100%',
                    labelWidth: 100,
                    allowBlank: false
                },
                items: [
                    {
                        fieldLabel: 'Unit Code',
                        name: 'unit_code',
                        emptyText: 'e.g. PCS, KG, BOX',
                        listeners: {
                            blur: function (field) {
                                field.setValue(field.getValue().toUpperCase().replace(/\s+/g, ''));
                            }
                        }
                    },
                    {
                        fieldLabel: 'Description',
                        name: 'description',
                        emptyText: 'e.g. Pieces, Kilograms, Box'
                    }
                ]
            };

            me.buttons = [
                {
                    text: 'Save',
                    formBind: true,
                    handler: function () {
                        var form = me.down('form').getForm();
                        if (form.isValid()) {
                            Ext.Ajax.request({
                                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Units/Main/save',
                                method: 'POST',
                                params: form.getValues(),
                                success: function (response) {
                                    var result = Ext.decode(response.responseText);
                                    if (result.success) {
                                        Ext.Msg.alert('Success', result.message);
                                        if (me.combo) {
                                            var store = me.combo.getStore();
                                            // Reload the store from the server to get the new unit with ID
                                            Ext.Ajax.request({
                                                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Units/Main/all',
                                                success: function (allResponse) {
                                                    var allResult = Ext.decode(allResponse.responseText);
                                                    if (allResult.success) {
                                                        store.loadData(allResult.data);
                                                        // Set the newly added unit as selected
                                                        var newRecord = store.findRecord('id', result.id);
                                                        if (newRecord) {
                                                            me.combo.setValue(newRecord.get('id'));
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                        me.close();
                                    } else {
                                        Ext.Msg.alert('Failed', result.message);
                                    }
                                },
                                failure: function () {
                                    Ext.Msg.alert('Error', 'Failed to save unit.');
                                }
                            });
                        }
                    }
                },
                {
                    text: 'Cancel',
                    handler: function () {
                        me.close();
                    }
                }
            ];

            me.callParent(arguments);
        }
    });

    Ext.define('App.view.items.Window', {
        extend: 'Ext.window.Window',
        alias: 'widget.items-window',
        title: 'Item Details',
        width: 750,
        layout: 'fit',
        modal: true,
        record: null,
        grid: null,
        resizable: false,

        initComponent: function () {
            var me = this;

            me.items = {
                xtype: 'form',
                bodyPadding: '15 20 10 20',
                cls: 'item-details-form',
                defaults: {
                    anchor: '100%',
                    labelWidth: 120,
                    msgTarget: 'side'
                },
                items: [
                    {
                        xtype: 'hiddenfield',
                        name: 'item_id'
                    },
                    {
                        xtype: 'container',
                        layout: 'column',
                        defaults: {
                            layout: 'anchor',
                            columnWidth: 0.5,
                            defaults: {
                                anchor: '95%',
                                labelWidth: 120,
                                xtype: 'textfield',
                                allowBlank: false,
                                margin: '0 0 5 0'
                            }
                        },
                        items: [
                            {
                                items: [
                                    {
                                        fieldLabel: 'Item Code',
                                        name: 'item_code',
                                        emptyText: 'Enter unique item code'
                                    },
                                    {
                                        xtype: 'combobox',
                                        fieldLabel: 'Material Group',
                                        name: 'material_group',
                                        store: {
                                            fields: ['id', 'group_name'],
                                            data: <?php echo json_encode($material_groups); ?>
                                        },
                                        displayField: 'group_name',
                                        valueField: 'id',
                                        queryMode: 'local',
                                        forceSelection: true,
                                        allowBlank: true
                                    },
                                    {
                                        xtype: 'combobox',
                                        fieldLabel: 'Item Category',
                                        name: 'item_cat',
                                        store: {
                                            fields: ['item_cat_id', 'item_cat_name'],
                                            data: <?php echo json_encode($categories); ?>
                                        },
                                        displayField: 'item_cat_name',
                                        valueField: 'item_cat_id',
                                        queryMode: 'local',
                                        forceSelection: true
                                    }
                                ]
                            },
                            {
                                items: [
                                    {
                                        fieldLabel: 'Description',
                                        name: 'item_desc',
                                        emptyText: 'Enter item description'
                                    },
                                    {
                                        xtype: 'combobox',
                                        fieldLabel: 'Item Type',
                                        name: 'item_type',
                                        store: {
                                            fields: ['id', 'type_name'],
                                            data: <?php echo json_encode($item_types); ?>
                                        },
                                        displayField: 'type_name',
                                        valueField: 'id',
                                        queryMode: 'local',
                                        forceSelection: true,
                                        allowBlank: true
                                    },
                                    {
                                        xtype: 'fieldcontainer',
                                        fieldLabel: 'Unit & Qty',
                                        layout: 'hbox',
                                        margin: '0 0 5 0',
                                        allowBlank: true, // Container itself doesn't need allowBlank
                                        defaults: {
                                            hideLabel: true,
                                            allowBlank: false
                                        },
                                        items: [
                                            {
                                                xtype: 'combobox',
                                                name: 'unit',
                                                emptyText: 'Unit',
                                                flex: 2,
                                                store: {
                                                    fields: ['id', 'description'],
                                                    data: <?php echo json_encode($units); ?>
                                                },
                                                displayField: 'description',
                                                valueField: 'id',
                                                queryMode: 'local',
                                                forceSelection: true,
                                                triggers: {
                                                    plus: {
                                                        cls: 'x-form-plus-trigger',
                                                        handler: function () {
                                                            Ext.create('App.view.units.Window', {
                                                                combo: this
                                                            }).show();
                                                        }
                                                    }
                                                }
                                            },
                                            {
                                                xtype: 'splitter'
                                            },
                                            {
                                                xtype: 'numberfield',
                                                name: 'qty',
                                                emptyText: 'Qty',
                                                flex: 1,
                                                minValue: 0,
                                                value: 0
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        layout: 'column',
                        margin: '5 0 0 0',
                        defaults: {
                            layout: 'anchor',
                            columnWidth: 0.5,
                            defaults: {
                                anchor: '95%',
                                labelWidth: 120,
                                xtype: 'numberfield',
                                minValue: 0,
                                value: 0,
                                allowBlank: false,
                                margin: '0 0 5 0'
                            }
                        },
                        items: [
                            {
                                items: [
                                    {
                                        fieldLabel: 'Reorder Level',
                                        name: 'reorder_level',
                                        helpText: 'The inventory level at which a new order should be placed to replenish stock.',
                                        listeners: {
                                            render: function (c) {
                                                Ext.create('Ext.tip.ToolTip', {
                                                    target: c.getEl(),
                                                    html: c.helpText
                                                });
                                            }
                                        }
                                    },
                                    {
                                        fieldLabel: 'Max Stock',
                                        name: 'maximum_stock',
                                        helpText: 'The upper limit of stock that should be kept in inventory.',
                                        listeners: {
                                            render: function (c) {
                                                Ext.create('Ext.tip.ToolTip', {
                                                    target: c.getEl(),
                                                    html: c.helpText
                                                });
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                items: [
                                    {
                                        xtype: 'fieldcontainer',
                                        fieldLabel: 'Purchase Cost',
                                        layout: 'hbox',
                                        margin: '0 0 5 0',
                                        defaults: {
                                            hideLabel: true
                                        },
                                        items: [
                                            {
                                                xtype: 'combobox',
                                                name: 'currency',
                                                width: 70,
                                                store: ['PHP', 'USD'],
                                                queryMode: 'local',
                                                forceSelection: true,
                                                value: 'PHP',
                                                allowBlank: false
                                            },
                                            {
                                                xtype: 'splitter'
                                            },
                                            {
                                                xtype: 'numberfield',
                                                name: 'default_purchase_cost',
                                                flex: 1,
                                                minValue: 0,
                                                value: 0,
                                                allowBlank: false,
                                                decimalPrecision: 2
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            };

            me.buttons = [
                {
                    text: 'Save',
                    formBind: true,
                    handler: function () {
                        var form = me.down('form').getForm();
                        if (form.isValid()) {
                            Ext.Ajax.request({
                                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Items/Main/save',
                                method: 'POST',
                                params: form.getValues(),
                                success: function (response) {
                                    var result = Ext.decode(response.responseText);
                                    if (result.success) {
                                        Ext.Msg.alert('Success', result.message);
                                        me.grid.getStore().load();
                                        me.close();
                                    } else {
                                        Ext.Msg.alert('Failed', result.message);
                                    }
                                },
                                failure: function () {
                                    Ext.Msg.alert('Error', 'Failed to save item.');
                                }
                            });
                        }
                    }
                },
                {
                    text: 'Cancel',
                    handler: function () {
                        me.close();
                    }
                }
            ];

            me.callParent(arguments);

            if (me.record) {
                me.down('form').loadRecord(me.record);
            }
        }
    });

    Ext.define('App.view.items.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.items-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: [
                'item_id', 'item_code', 'item_desc', 'item_cat', 'item_cat_name',
                'material_group', 'material_group_name', 'item_type', 'item_type_name',
                'currency', 'qty', 'unit', 'unit_description',
                'reorder_level', 'maximum_stock', 'default_purchase_cost', 'date_created'
            ],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Items/Main/data',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
                }
            },
            autoLoad: true
        },
        columns: [
            {text: 'ID', dataIndex: 'item_id', width: 60},
            {
                text: 'Item Code',
                dataIndex: 'item_code',
                width: 120
            },
            {
                text: 'Description',
                dataIndex: 'item_desc',
                flex: 1
            },
            {
                text: 'Category',
                dataIndex: 'item_cat',
                width: 150,
                renderer: function (value, metaData, record) {
                    return record.get('item_cat_name');
                }
            },
            {
                text: 'Material Group',
                dataIndex: 'material_group',
                width: 150,
                renderer: function (value, metaData, record) {
                    return record.get('material_group_name');
                }
            },
            {
                text: 'Item Type',
                dataIndex: 'item_type',
                width: 150,
                renderer: function (value, metaData, record) {
                    return record.get('item_type_name');
                }
            },
            {
                text: 'Currency',
                dataIndex: 'currency',
                width: 80
            },
            {
                text: 'Qty',
                dataIndex: 'qty',
                width: 80
            },
            {
                text: 'Unit',
                dataIndex: 'unit',
                width: 100,
                renderer: function (value, metaData, record) {
                    return record.get('unit_description');
                }
            },
            {
                text: 'Reorder Level',
                dataIndex: 'reorder_level',
                width: 110,
                renderer: function (value, meta) {
                    meta.tdAttr = 'data-qtip="The inventory level at which a new order should be placed to replenish stock before it runs out."';
                    return Ext.util.Format.number(value, '0,000');
                }
            },
            {
                text: 'Max Stock',
                dataIndex: 'maximum_stock',
                width: 110,
                renderer: function (value, meta) {
                    meta.tdAttr = 'data-qtip="The upper limit of stock that should be kept in inventory to avoid overstocking and excess holding costs."';
                    return Ext.util.Format.number(value, '0,000');
                }
            },
            {
                text: 'Purchase Cost',
                dataIndex: 'default_purchase_cost',
                width: 110,
                renderer: function (value) {
                    return Ext.util.Format.number(value, '0,000.00');
                }
            },
            {
                text: 'Date Created',
                dataIndex: 'date_created',
                width: 130,
                hidden: true
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
                    Ext.create('App.view.items.Window', {
                        grid: grid
                    }).show();
                }
            },
            {
                text: 'Edit Item',
                itemId: 'editItem',
                disabled: true,
                handler: function () {
                    var grid = this.up('grid');
                    var selection = grid.getSelectionModel().getSelection();
                    if (selection.length > 0) {
                        Ext.create('App.view.items.Window', {
                            grid: grid,
                            record: selection[0]
                        }).show();
                    }
                }
            },
            {
                text: 'Remove Item',
                itemId: 'removeItem',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this item?', function (choice) {
                            if (choice === 'yes') {
                                Ext.Ajax.request({
                                    url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Items/Main/delete',
                                    method: 'POST',
                                    params: {item_id: record.get('item_id')},
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
                        });
                    }
                },
                disabled: true
            }
        ],
        listeners: {
            itemdblclick: function (grid, record) {
                Ext.create('App.view.items.Window', {
                    grid: grid,
                    record: record
                }).show();
            },
            selectionchange: function (model, records) {
                this.down('#removeItem').setDisabled(!records.length);
                this.down('#editItem').setDisabled(!records.length);
            }
        }
    });
</script>
