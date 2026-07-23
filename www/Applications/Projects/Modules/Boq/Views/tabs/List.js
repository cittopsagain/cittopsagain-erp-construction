Ext.define('App.view.boq.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.boq-list',
    region: 'west',
    width: 500,
    split: true,
    collapsible: true,
    title: 'BOQ List',
    initComponent: function () {
        var me = this;
        var mainView = me.up('boq-main');

        me.store = Ext.create('Ext.data.Store', {
            model: 'BoqModel',
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: mainView.baseUrl + '/Projects/Boq/Main/data',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
                }
            },
            autoLoad: true
        });

        me.columns = [
            {text: 'BOQ No.', dataIndex: 'boq_no', width: 150},
            {text: 'Project', dataIndex: 'project_name', flex: 1},
            {text: 'Revision', dataIndex: 'revision', width: 80},
            {text: 'Status', dataIndex: 'status', width: 80}
        ];

        me.tbar = [
            {
                text: 'Add BOQ',
                iconCls: 'x-fa fa-plus',
                handler: function () {
                    var main = me.up('boq-main');
                    me.getSelectionModel().deselectAll();
                    var form = main.down('form');
                    form.getForm().reset();
                    form.setDisabled(false);
                    main.down('#saveBtn').setText('Save BOQ');

                    var detailGrid = main.down('boq-lines-grid');
                    if (detailGrid) {
                        detailGrid.getStore().removeAll();
                    }

                    var locationsGrid = main.down('boq-locations-grid');
                    if (locationsGrid) {
                        locationsGrid.getStore().removeAll();
                        locationsGrid.getStore().getProxy().setExtraParam('boq_id', null);
                    }

                    // Ensure UI is editable for new BOQ
                    main.updateEditability('Draft');
                    if (detailGrid) detailGrid.setDisabled(false);
                    if (locationsGrid) locationsGrid.setDisabled(false);
                }
            },
            {
                text: 'Revise',
                itemId: 'reviseBtn',
                iconCls: 'x-fa fa-edit',
                hidden: true,
                handler: function () {
                    var selection = me.getSelectionModel().getSelection();
                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Confirm', 'Are you sure you want to create a new revision of this BOQ?', function (btn) {
                            if (btn === 'yes') {
                                Ext.Ajax.request({
                                    url: mainView.baseUrl + '/Projects/Boq/Main/revise',
                                    params: {id: record.get('id')},
                                    success: function (response) {
                                        var result = Ext.decode(response.responseText);
                                        if (result.success) {
                                            Ext.Msg.alert('Success', result.message);
                                            me.getStore().load({
                                                callback: function (records, operation, success) {
                                                    if (success && result.id) {
                                                        var newRecord = me.getStore().getById(result.id);
                                                        if (newRecord) {
                                                            me.getSelectionModel().deselectAll();
                                                            me.getSelectionModel().select(newRecord);

                                                            // Force update form values and editability
                                                            var form = mainView.down('form');
                                                            if (form) {
                                                                form.getForm().loadRecord(newRecord);
                                                                mainView.updateEditability(newRecord.get('status'));
                                                            }
                                                        }
                                                    }
                                                }
                                            });
                                        } else {
                                            Ext.Msg.alert('Error', result.message);
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            },
            '->',
            {
                xtype: 'textfield',
                emptyText: 'Search...',
                listeners: {
                    change: function (field, newValue) {
                        me.store.getProxy().setExtraParam('query', newValue);
                        me.store.loadPage(1);
                    }
                }
            }
        ];

        me.bbar = {
            xtype: 'pagingtoolbar',
            store: me.store,
            displayInfo: true
        };

        me.listeners = {
            selectionchange: function (selModel, selected) {
                var main = me.up('boq-main');
                var form = main.down('form');
                var detailGrid = main.down('boq-lines-grid');
                var locationsGrid = main.down('boq-locations-grid');
                var revisionsGrid = main.down('boq-revisions-grid');

                if (selected.length > 0) {
                    form.getForm().loadRecord(selected[0]);
                    form.setDisabled(false);
                    main.down('#saveBtn').setText('Update BOQ');

                    console.log('Button Status: ' + selected[0].get('status'));
                    // Update UI based on status
                    main.updateEditability(selected[0].get('status'));
                    if (detailGrid) {
                        detailGrid.setDisabled(false);
                        // Load BOQ lines
                        detailGrid.getStore().load({
                            params: {id: selected[0].get('id')}
                        });
                    }

                    if (locationsGrid) {
                        locationsGrid.setDisabled(false);
                        // Load BOQ Locations
                        locationsGrid.getStore().getProxy().setExtraParam('boq_id', selected[0].get('id'));
                        locationsGrid.getStore().load();
                    }

                    if (revisionsGrid) {
                        revisionsGrid.getStore().getProxy().setExtraParam('id', selected[0].get('id'));
                        revisionsGrid.getStore().load();
                    }
                } else {
                    form.getForm().reset();
                    form.setDisabled(true);
                    if (detailGrid) {
                        detailGrid.setDisabled(true);
                        detailGrid.getStore().removeAll();
                    }
                    if (locationsGrid) {
                        locationsGrid.setDisabled(true);
                        locationsGrid.getStore().removeAll();
                        locationsGrid.getStore().getProxy().setExtraParam('boq_id', null);
                    }
                    if (revisionsGrid) {
                        revisionsGrid.getStore().removeAll();
                        revisionsGrid.getStore().getProxy().setExtraParam('id', null);
                    }
                }
            }
        };

        me.callParent(arguments);
    }
});
