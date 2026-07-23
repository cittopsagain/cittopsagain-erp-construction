Ext.define('App.view.boq.RevisionsGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.boq-revisions-grid',
    initComponent: function () {
        var me = this;
        var mainView = me.up('boq-main');

        me.store = Ext.create('Ext.data.Store', {
            model: 'BoqRevisionModel',
            proxy: {
                type: 'ajax',
                url: mainView.baseUrl + '/Projects/Boq/Main/revisions',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        me.columns = [
            {text: 'Revision', dataIndex: 'revision', width: 100},
            {text: 'Status', dataIndex: 'status', width: 100},
            {text: 'Date Revised', dataIndex: 'created_at', flex: 1},
            {text: 'Revision ID', dataIndex: 'revision_id', width: 100},
            {
                text: 'BOQ No.',
                dataIndex: 'boq_no',
                width: 150,
                hidden: true
            }
        ];

        me.callParent(arguments);
    }
});
