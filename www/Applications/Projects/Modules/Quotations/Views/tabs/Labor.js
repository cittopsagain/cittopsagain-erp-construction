/**
 * Labor Tab
 * This tab displays and manages the labor items for the quotation.
 */
Ext.define('App.view.quotations.tabs.Labor', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.quotations-tab-labor',
    title: 'Labor',
    itemId: 'laborGrid',

    // Detailed calculation explanation:
    // Regular Cost = No. of Men * Days * Hours * Rate/Hour
    // OT Cost = No. of Men * OT Hours * OT Rate
    // Total Labor Cost = Regular Cost + OT Cost
    // Example: Men=2, Days=3, Hours=8, Rate=50, OT Hrs=2, OT Rate=75
    // Regular Cost = 2 * 3 * 8 * 50 = 2400
    // OT Cost = 2 * 2 * 75 = 300
    // Total = 2400 + 300 = 2700

    plugins: {
        ptype: 'rowediting',
        clicksToEdit: 2
    },
    columns: [
        {
            text: 'Position',
            dataIndex: 'item_code',
            width: 150,
            editor: {
                xtype: 'combobox',
                store: {
                    fields: ['pos_id', 'pos_name'],
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo rtrim(BASE_URL, ' / '); ?>/Hr/JobPositions/Main/reportsTo',
                        reader: {
                            type: 'json',
                            rootProperty: 'data'
                        }
                    },
                    autoLoad: true
                },
                valueField: 'pos_name',
                displayField: 'pos_name',
                queryMode: 'remote',
                allowBlank: false
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
            text: 'No. Of Men',
            dataIndex: 'no_of_men',
            width: 100,
            align: 'center',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var men = field.getValue();
                            var days = record.get('days');
                            var hours = record.get('hours');
                            var rate = record.get('price');
                            var ot_hrs = record.get('ot_hrs');
                            var ot_rate = record.get('ot_rate');

                            // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                            var total = (men * days * hours * rate) + (men * ot_hrs * ot_rate);
                            record.set('total_price', total);
                        }
                    }
                }
            }
        },
        {
            text: 'Days',
            dataIndex: 'days',
            width: 80,
            align: 'center',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var men = record.get('no_of_men');
                            var days = field.getValue();
                            var hours = record.get('hours');
                            var rate = record.get('price');
                            var ot_hrs = record.get('ot_hrs');
                            var ot_rate = record.get('ot_rate');

                            // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                            var total = (men * days * hours * rate) + (men * ot_hrs * ot_rate);
                            record.set('total_price', total);
                        }
                    }
                }
            }
        },
        {
            text: 'Hours',
            dataIndex: 'hours',
            width: 80,
            align: 'center',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var men = record.get('no_of_men');
                            var days = record.get('days');
                            var hours = field.getValue();
                            var rate = record.get('price');
                            var ot_hrs = record.get('ot_hrs');
                            var ot_rate = record.get('ot_rate');

                            // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                            var total = (men * days * hours * rate) + (men * ot_hrs * ot_rate);
                            record.set('total_price', total);
                        }
                    }
                }
            }
        },
        {
            text: 'Rate/Hour',
            dataIndex: 'price',
            width: 100,
            formatter: 'number("0,000.00")',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var men = record.get('no_of_men');
                            var days = record.get('days');
                            var hours = record.get('hours');
                            var rate = field.getValue();
                            var ot_hrs = record.get('ot_hrs');
                            var ot_rate = record.get('ot_rate');

                            // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                            var total = (men * days * hours * rate) + (men * ot_hrs * ot_rate);
                            record.set('total_price', total);
                        }
                    }
                }
            }
        },
        {
            text: 'OT Hrs',
            dataIndex: 'ot_hrs',
            width: 80,
            align: 'center',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var men = record.get('no_of_men');
                            var days = record.get('days');
                            var hours = record.get('hours');
                            var rate = record.get('price');
                            var ot_hrs = field.getValue();
                            var ot_rate = record.get('ot_rate');

                            // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                            var total = (men * days * hours * rate) + (men * ot_hrs * ot_rate);
                            record.set('total_price', total);
                        }
                    }
                }
            }
        },
        {
            text: 'OT Rate',
            dataIndex: 'ot_rate',
            width: 100,
            formatter: 'number("0,000.00")',
            editor: {
                xtype: 'numberfield',
                minValue: 0,
                listeners: {
                    change: function (field) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            var men = record.get('no_of_men');
                            var days = record.get('days');
                            var hours = record.get('hours');
                            var rate = record.get('price');
                            var ot_hrs = record.get('ot_hrs');
                            var ot_rate = field.getValue();

                            // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                            var total = (men * days * hours * rate) + (men * ot_hrs * ot_rate);
                            record.set('total_price', total);
                        }
                    }
                }
            }
        },
        {
            text: 'Total',
            dataIndex: 'total_price',
            width: 120,
            align: 'right',
            formatter: 'number("0,000.00")',
            renderer: function (value, metaData, record) {
                // Formula: (Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate)
                var total = (record.get('no_of_men') * record.get('days') * record.get('hours') * record.get('price')) +
                    (record.get('no_of_men') * record.get('ot_hrs') * record.get('ot_rate'));
                record.set('total_price', total, {commit: true, silent: true});
                return Ext.util.Format.number(total, '0,000.00');
            }
        }
    ],
    /*
    bbar: [
        {
            xtype: 'component',
            flex: 1,
            padding: '10',
            html: '<div style="font-size: 12px; color: #31708f; background-color: #d9edf7; border: 1px solid #bce8f1; padding: 15px; border-radius: 4px; box-sizing: border-box;">' +
                '<div style="margin-bottom: 10px; display: flex; align-items: center;">' +
                '<i class="fa fa-calculator" style="font-size: 16px; margin-right: 8px;"></i>' +
                '<span style="font-weight: bold; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Computation Guide</span>' +
                '</div>' +
                '<div style="display: flex; flex-wrap: wrap;">' +
                '<div style="flex: 1; min-width: 300px; padding-right: 20px; border-right: 1px solid #bce8f1;">' +
                '<div style="margin-bottom: 5px; font-weight: bold; color: #245269;">Formulas:</div>' +
                '<div style="font-family: monospace; background: rgba(255,255,255,0.5); padding: 8px; border-radius: 3px; border: 1px solid rgba(188,232,241,0.5);">' +
                'Regular Cost = Men × Days × Hours × Rate<br>' +
                'OT Cost = Men × OT Hours × OT Rate<br>' +
                '<span style="border-top: 1px solid #bce8f1; display: block; margin-top: 4px; padding-top: 4px; font-weight: bold;">Total Labor Cost = Regular Cost + OT Cost</span>' +
                '</div>' +
                '</div>' +
                '<div style="flex: 2; min-width: 400px; padding-left: 20px;">' +
                '<div style="margin-bottom: 5px; font-weight: bold; color: #245269;">Definitions:</div>' +
                '<table style="width: 100%; font-size: 11px; border-collapse: collapse;">' +
                '<tr><td style="padding: 2px 0; vertical-align: top; width: 80px;"><b>Days</b></td><td style="padding: 2px 0;">Total number of working days required to complete the labor activity.</td></tr>' +
                '<tr><td style="padding: 2px 0; vertical-align: top;"><b>Hours</b></td><td style="padding: 2px 0;">Number of regular working hours per day for each worker (Commonly 8 hrs/day).</td></tr>' +
                '<tr><td style="padding: 2px 0; vertical-align: top;"><b>Rate/Hour</b></td><td style="padding: 2px 0;">Hourly labor rate or wage of each worker for regular working hours.</td></tr>' +
                '<tr><td style="padding: 2px 0; vertical-align: top;"><b>OT Hrs</b></td><td style="padding: 2px 0;">Total overtime hours rendered beyond regular working hours.</td></tr>' +
                '<tr><td style="padding: 2px 0; vertical-align: top;"><b>OT Rate</b></td><td style="padding: 2px 0;">Hourly overtime rate applied to overtime work.</td></tr>' +
                '</table>' +
                '</div>' +
                '</div>' +
                '</div>'
        }
    ],
    */
    tbar: [
        {
            text: 'Add Labor',
            iconCls: 'x-fa fa-plus-circle',
            handler: function () {
                var grid = this.up('grid');
                var rowEditing = grid.findPlugin('rowediting');
                if (rowEditing) {
                    rowEditing.cancelEdit();
                }
                var r = Ext.create(grid.getStore().getModel(), {
                    detail_type: 'LABOR',
                    item_code: '',
                    item_desc: '',
                    no_of_men: 1,
                    days: 1,
                    hours: 8,
                    price: 0,
                    ot_hrs: 0,
                    ot_rate: 0
                });
                grid.getStore().insert(0, r);
                if (rowEditing) {
                    rowEditing.startEdit(0, 0);
                }
            }
        },
        {
            text: 'Remove Labor',
            iconCls: 'x-fa fa-minus-circle',
            handler: function () {
                var grid = this.up('grid');
                var sm = grid.getSelectionModel();
                var selection = sm.getSelection();

                if (selection.length > 0) {
                    Ext.Msg.confirm('Remove Labor', 'Are you sure you want to remove the selected labor(s)?', function (btn) {
                        if (btn === 'yes') {
                            grid.getStore().remove(selection);
                        }
                    });
                } else {
                    Ext.Msg.alert('Notice', 'Please select a labor item to remove.');
                }
            }
        }
    ]
});
