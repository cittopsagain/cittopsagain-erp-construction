/**
 * Overhead Tab
 * This tab displays and manages the overhead items for the quotation.
 */
Ext.define('App.view.quotations.tabs.Overhead', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.quotations-tab-overhead',
    title: 'Overhead',
    itemId: 'overheadGrid',

    // Detailed calculation explanation:
    // If Computation Type is 'Fixed': Total = Value * Qty
    // If Computation Type is '%': Total = Value (%) of (Total Materials + Total Labor)
    // Example Fixed: Value=5,000, Qty=1 -> 5,000 * 1 = 5,000
    // Example %: Value=5%, Base=80,000 -> 0.05 * 80,000 = 4,000

    plugins: {
        ptype: 'rowediting',
        clicksToEdit: 2
    },
    columns: [
        {
            text: 'Type',
            dataIndex: 'item_code',
            width: 150,
            editor: {
                xtype: 'combobox',
                store: {
                    fields: ['id', 'code', 'description'],
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/OverheadTypes/Main/all',
                        reader: {
                            type: 'json',
                            rootProperty: 'data'
                        }
                    },
                    autoLoad: true
                },
                valueField: 'description',
                displayField: 'description',
                queryMode: 'local',
                tpl: Ext.create('Ext.XTemplate',
                    '<ul class="x-list-plain"><tpl for=".">',
                    '<li role="option" class="x-boundlist-item">{description}</li>',
                    '</tpl></ul>'
                ),
                listeners: {
                    select: function (combo, record) {
                        var rowEditing = combo.up('grid').findPlugin('rowediting');
                        var gridRecord = rowEditing ? rowEditing.context.record : null;
                        if (gridRecord) {
                            gridRecord.set('item_code', record.get('code'));
                            gridRecord.set('item_desc', record.get('description'));
                        }
                    }
                },
                allowBlank: false,
                forceSelection: true
            }
        },
        {
            text: 'Description',
            dataIndex: 'item_desc',
            flex: 1,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        },
        {
            text: 'Computation Type',
            dataIndex: 'overhead_computation_type',
            width: 150,
            editor: {
                xtype: 'combobox',
                store: ['Fixed', '%'],
                queryMode: 'local',
                editable: false,
                allowBlank: false,
                listeners: {
                    change: function (combo, newValue) {
                        var rowEditing = combo.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            if (newValue === '%') {
                                record.set('qty', 0);
                            } else if (record.get('qty') === 0) {
                                record.set('qty', 1);
                            }
                            this.up('grid').getView().refresh();
                        }
                    }
                }
            }
        },
        {
            text: 'Value',
            dataIndex: 'overhead_value',
            width: 100,
            renderer: function (value, metaData, record) {
                if (record.get('overhead_computation_type') === '%') {
                    return value + '%';
                }
                return Ext.util.Format.number(value, '0,000.00');
            },
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                decimalPrecision: 4
            }
        },
        {
            text: 'Qty',
            dataIndex: 'qty',
            width: 80,
            renderer: function (value, metaData, record) {
                if (record.get('overhead_computation_type') === '%') {
                    return '-';
                }
                return value;
            },
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                allowDecimals: false
            }
        },
        {
            text: 'Total',
            dataIndex: 'total_price',
            width: 120,
            formatter: 'number("0,000.00")',
            renderer: function (value, metaData, record) {
                var computationType = record.get('overhead_computation_type');
                var val = record.get('overhead_value') || 0;
                var qty = record.get('qty') || 0;
                var total = 0;

                if (computationType === '%') {
                    // Base is Total Materials + Total Labor
                    var form = this.up('form');
                    if (form) {
                        var materialsGrid = form.down('#materialsGrid');
                        var laborGrid = form.down('#laborGrid');

                        var totalMaterials = 0;
                        if (materialsGrid) {
                            materialsGrid.getStore().each(function (r) {
                                totalMaterials += r.get('total_price') || 0;
                            });
                        }

                        var totalLabor = 0;
                        if (laborGrid) {
                            laborGrid.getStore().each(function (r) {
                                totalLabor += r.get('total_price') || 0;
                            });
                        }

                        total = (totalMaterials + totalLabor) * (val / 100);
                    }
                } else {
                    total = val * qty;
                }

                record.set('total_price', total, {commit: true, silent: true});
                return Ext.util.Format.number(total, '0,000.00');
            }
        }
    ],
    tbar: [
        {
            text: 'Add Overhead',
            iconCls: 'x-fa fa-plus-circle',
            handler: function () {
                var grid = this.up('grid');
                var rowEditing = grid.findPlugin('rowediting');
                if (rowEditing) {
                    rowEditing.cancelEdit();
                }
                var r = Ext.create(grid.getStore().getModel(), {
                    detail_type: 'OVERHEAD',
                    item_code: '',
                    item_desc: '',
                    overhead_computation_type: 'Fixed',
                    overhead_value: 0,
                    qty: 1,
                    total_price: 0
                });
                grid.getStore().insert(0, r);
                if (rowEditing) {
                    rowEditing.startEdit(0, 0);
                }
            }
        },
        {
            text: 'Remove Overhead',
            iconCls: 'x-fa fa-minus-circle',
            handler: function () {
                var grid = this.up('grid');
                var sm = grid.getSelectionModel();
                var selection = sm.getSelection();

                if (selection.length > 0) {
                    Ext.Msg.confirm('Remove Overhead', 'Are you sure you want to remove the selected overhead item(s)?', function (btn) {
                        if (btn === 'yes') {
                            grid.getStore().remove(selection);
                        }
                    });
                } else {
                    Ext.Msg.alert('Notice', 'Please select an overhead item to remove.');
                }
            }
        }
    ]
});
