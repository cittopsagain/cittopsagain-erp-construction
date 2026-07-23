Ext.define('App.view.costestimate.tabs.Lines', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.cost-estimate-tab-lines',
    title: 'Estimate Lines',
    columnLines: true,
    initComponent: function () {
        var me = this;

        me.store = Ext.create('Ext.data.Store', {
            fields: [
                'id', 'cost_estimate_id', 'description', 'quantity', 'unit_cost', 'total_cost'
            ],
            proxy: {
                type: 'ajax',
                url: '/Projects/CostEstimate/Main/lines',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        me.columns = [
            {xtype: 'rownumberer'},
            {text: 'Description', dataIndex: 'description', flex: 1, editor: 'textfield'},
            {text: 'Quantity', dataIndex: 'quantity', width: 100, editor: 'numberfield'},
            {
                text: 'Unit Cost',
                dataIndex: 'unit_cost',
                width: 120,
                renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: 'numberfield'
            },
            {
                text: 'Total Cost',
                dataIndex: 'total_cost',
                width: 120,
                renderer: Ext.util.Format.numberRenderer('0,000.00'),
                summaryType: 'sum'
            }
        ];

        me.plugins = [
            Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1
            })
        ];

        me.callParent(arguments);
    }
});
