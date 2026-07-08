/**
 * Materials Tab
 * This tab displays and manages the material items for the quotation.
 */
Ext.define('App.view.quotations.tabs.Materials', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.quotations-tab-materials',
    title: 'Materials',
    itemId: 'materialGrid',

    // Detailed calculation explanation:
    // Total Price = Qty * Unit Price
    // Example: Qty=5, Price=100 -> 5 * 100 = 500

    plugins: {
        ptype: 'rowediting',
        clicksToEdit: 2
    },
    columns: [
        {
            text: 'Item Code',
            dataIndex: 'item_code',
            width: 150,
            editor: {
                xtype: 'textfield',
                triggers: {
                    search: {
                        cls: 'x-form-search-trigger',
                        handler: function () {
                            var editor = this;
                            var itemWin = Ext.create('App.view.quotations.ItemWindow', {
                                callback: function (item) {
                                    editor.setValue(item.item_code);
                                    var rowEditing = editor.up('grid').findPlugin('rowediting');
                                    var record = rowEditing ? rowEditing.context.record : null;
                                    if (record) {
                                        // Calculation: Qty (initial 1) * item.price
                                        var totalPrice = 1 * item.price;
                                        record.set({
                                            item_code: item.item_code,
                                            item_desc: item.item_desc,
                                            qty: 1,
                                            price: item.price,
                                            unit_code: item.unit_code,
                                            unit_description: item.unit_description,
                                            total_price: totalPrice
                                        });

                                        if (rowEditing && rowEditing.getEditor()) {
                                            rowEditing.getEditor().getForm().setValues({
                                                item_code: item.item_code,
                                                item_desc: item.item_desc,
                                                qty: 1,
                                                price: item.price,
                                                unit_code: item.unit_code,
                                                unit_description: item.unit_description,
                                                total_price: totalPrice
                                            });
                                        }
                                    }
                                }
                            });
                            itemWin.show();
                        }
                    }
                }
            }
        },
        {
            text: 'Description',
            dataIndex: 'item_desc',
            flex: 1,
            editor: {
                xtype: 'textfield'
            }
        },
        {
            text: 'Qty',
            dataIndex: 'qty',
            width: 80,
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field, newValue) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var price = record.get('price');
                            // Calculation: Qty * Unit Price
                            record.set('total_price', newValue * price);
                        }
                    }
                }
            }
        },
        {
            text: 'Unit',
            dataIndex: 'unit_code',
            width: 100,
            editor: {
                xtype: 'combobox',
                store: {
                    fields: ['unit_code', 'description'],
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Units/Main/all',
                        reader: {
                            type: 'json',
                            rootProperty: 'data'
                        }
                    },
                    autoLoad: true
                },
                valueField: 'unit_code',
                displayField: 'unit_code',
                queryMode: 'local',
                allowBlank: false
            }
        },
        {
            text: 'Unit Price',
            dataIndex: 'price',
            width: 100,
            formatter: 'number("0,000.00")',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field, newValue) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var qty = record.get('qty');
                            // Calculation: Unit Price * Qty
                            record.set('total_price', newValue * qty);
                        }
                    }
                }
            }
        },
        {
            text: 'Total Price',
            dataIndex: 'total_price',
            width: 120,
            formatter: 'number("0,000.00")',
            renderer: function (value, metaData, record) {
                // Calculation: Qty * Unit Price
                var total = record.get('qty') * record.get('price');
                record.set('total_price', total, {commit: true, silent: true});
                return Ext.util.Format.number(total, '0,000.00');
            }
        }
    ],
    tbar: [
        {
            text: 'Add Material',
            iconCls: 'x-fa fa-plus-circle',
            handler: function () {
                var grid = this.up('grid');
                var rowEditing = grid.findPlugin('rowediting');
                if (rowEditing) {
                    rowEditing.cancelEdit();
                }
                var r = Ext.create(grid.getStore().getModel(), {
                    detail_type: 'MATERIAL',
                    unit_code: '',
                    item_code: '',
                    qty: 1,
                    item_desc: '',
                    price: 0
                });
                grid.getStore().insert(0, r);
                if (rowEditing) {
                    rowEditing.startEdit(0, 0);
                }
            }
        },
        {
            text: 'Remove Material',
            iconCls: 'x-fa fa-minus-circle',
            handler: function () {
                var grid = this.up('grid');
                var sm = grid.getSelectionModel();
                var selection = sm.getSelection();

                if (selection.length > 0) {
                    Ext.Msg.confirm('Remove Material', 'Are you sure you want to remove the selected material(s)?', function (btn) {
                        if (btn === 'yes') {
                            grid.getStore().remove(selection);
                        }
                    });
                } else {
                    Ext.Msg.alert('Notice', 'Please select a material to remove.');
                }
            }
        }
    ]
});
