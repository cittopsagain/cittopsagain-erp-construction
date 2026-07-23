Ext.define('App.view.boq.LinesGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.boq-lines-grid',
    height: 410,
    initComponent: function () {
        var me = this;
        var mainView = me.up('boq-main');

        me.store = Ext.create('Ext.data.Store', {
            model: 'BoqDetailModel',
            groupField: 'composition_template_code',
            proxy: {
                type: 'ajax',
                url: mainView.baseUrl + '/Projects/Boq/Main/details',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        me.features = [{
            ftype: 'grouping',
            groupHeaderTpl: [
                'Template: {[values.children[0].get("composition_template_code")]} - {[values.children[0].get("composition_template_name")]}'
            ],
            hideGroupedHeader: true,
            startCollapsed: false
        }];

        me.plugins = [
            Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1,
                listeners: {
                    beforeedit: function (editor, context) {
                        var statusField = context.grid.up('boq-main').down('form').getForm().findField('status');
                        if (statusField && statusField.getValue() === 'Approved') {
                            return false;
                        }
                    }
                }
            })
        ];

        me.columns = [
            {
                text: 'Composition Template',
                dataIndex: 'composition_template_id',
                flex: 1,
                renderer: function (value, metaData, record) {
                    return record.get('composition_template_code') || record.get('composition_template_name');
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['id', 'template_name', 'template_code', 'service_id', 'service_name', 'trade_id', 'trade_name', 'system_id', 'system_name', 'installation_method_id', 'installation_method_name'],
                        data: mainView.templates || []
                    },
                    displayField: 'template_code',
                    valueField: 'id',
                    queryMode: 'local',
                    listeners: {
                        select: function (combo, record) {
                            var gridRecord = me.getSelectionModel().getSelection()[0];
                            if (gridRecord) {
                                var oldTemplateId = gridRecord.get('composition_template_id');
                                var isAlreadyExpanded = gridRecord.get('is_expanded');

                                var currentStore = me.getStore();
                                var duplicateFound = false;
                                if (record && record.get('id')) {
                                    currentStore.each(function (rec) {
                                        if (rec !== gridRecord && rec.get('is_base_line') && rec.get('composition_template_id') == record.get('id')) {
                                            duplicateFound = true;
                                            return false;
                                        }
                                    });
                                }

                                if (duplicateFound) {
                                    Ext.Msg.alert('Duplicate', 'This Composition Template is already added to the list.');
                                    combo.setValue(oldTemplateId);
                                    return;
                                }

                                if (oldTemplateId && (!record || oldTemplateId != record.get('id'))) {
                                    var currentIndex = currentStore.indexOf(gridRecord);
                                    var recordsToRemove = [];
                                    for (var j = currentIndex + 1; j < currentStore.getCount(); j++) {
                                        var nextRec = currentStore.getAt(j);
                                        if (nextRec.get('composition_template_id') == oldTemplateId && nextRec.get('is_expanded') && !nextRec.get('is_base_line')) {
                                            recordsToRemove.push(nextRec);
                                        } else {
                                            break;
                                        }
                                    }
                                    if (recordsToRemove.length > 0) {
                                        currentStore.remove(recordsToRemove);
                                    }
                                }

                                if (!record) {
                                    gridRecord.set('composition_template_id', null);
                                    gridRecord.set('composition_template_name', '');
                                    gridRecord.set('composition_template_code', '');
                                    gridRecord.set('is_expanded', false);
                                    gridRecord.set('is_base_line', true);
                                    return;
                                }

                                gridRecord.set('composition_template_id', record.get('id'));
                                gridRecord.set('composition_template_name', record.get('template_name'));
                                gridRecord.set('composition_template_code', record.get('template_code'));
                                gridRecord.set('service_id', record.get('service_id'));
                                gridRecord.set('service_name', record.get('service_name'));
                                gridRecord.set('trade_id', record.get('trade_id'));
                                gridRecord.set('trade_name', record.get('trade_name'));
                                gridRecord.set('system_id', record.get('system_id'));
                                gridRecord.set('system_name', record.get('system_name'));
                                gridRecord.set('installation_method_id', record.get('installation_method_id'));
                                gridRecord.set('installation_method_name', record.get('installation_method_name'));
                                gridRecord.set('description', record.get('template_name'));

                                if (isAlreadyExpanded && oldTemplateId == record.get('id')) {
                                    return;
                                }

                                gridRecord.set('is_expanded', false);
                                gridRecord.set('is_base_line', true);

                                Ext.Ajax.request({
                                    url: mainView.baseUrl + '/Projects/CompositionTemplates/Main/detailData',
                                    params: {
                                        template_id: record.get('id'),
                                        detail_type: 'MATERIAL'
                                    },
                                    success: function (response) {
                                        var result = Ext.decode(response.responseText);
                                        if (result.success !== false && result.data && result.data.length > 0) {
                                            var currentStore = me.getStore();
                                            var currentRecord = me.getSelectionModel().getSelection()[0] || gridRecord;

                                            if (currentRecord.get('composition_template_id') != record.get('id')) {
                                                return;
                                            }

                                            var locationId = currentRecord.get('location_id');
                                            var locationName = currentRecord.get('location_name');

                                            var firstDetail = result.data[0];
                                            currentRecord.set('description', firstDetail.description || firstDetail.item_desc || record.get('template_name'));
                                            currentRecord.set('is_expanded', true);

                                            for (var i = 1; i < result.data.length; i++) {
                                                var detail = result.data[i];
                                                var newRec = Ext.create('BoqDetailModel', {
                                                    composition_template_id: record.get('id'),
                                                    composition_template_name: record.get('template_name'),
                                                    composition_template_code: record.get('template_code'),
                                                    service_id: record.get('service_id'),
                                                    service_name: record.get('service_name'),
                                                    trade_id: record.get('trade_id'),
                                                    trade_name: record.get('trade_name'),
                                                    system_id: record.get('system_id'),
                                                    system_name: record.get('system_name'),
                                                    installation_method_id: record.get('installation_method_id'),
                                                    installation_method_name: record.get('installation_method_name'),
                                                    location_id: locationId,
                                                    location_name: locationName,
                                                    description: detail.description || detail.item_desc,
                                                    quantity: 1,
                                                    is_expanded: true,
                                                    is_base_line: false
                                                });
                                                currentStore.insert(currentStore.indexOf(currentRecord) + i, newRec);
                                            }
                                        }
                                    }
                                });
                            }
                        }
                    }
                }
            },
            {
                text: 'Description',
                dataIndex: 'description',
                flex: 2
            },
            {
                text: 'Service',
                dataIndex: 'service_id',
                width: 120,
                renderer: function (value, metaData, record) {
                    if (!value) return record.get('service_name') || '';
                    var store = {data: mainView.services || []};
                    var res = store.data.find(function (i) {
                        return i.service_id == value;
                    });
                    return res ? res.description : record.get('service_name') || value;
                }
            },
            {
                text: 'Trade',
                dataIndex: 'trade_id',
                width: 120,
                renderer: function (value, metaData, record) {
                    if (!value) return record.get('trade_name') || '';
                    var store = {data: mainView.trades || []};
                    var res = store.data.find(function (i) {
                        return i.trade_id == value;
                    });
                    return res ? res.description : record.get('trade_name') || value;
                }
            },
            {
                text: 'System',
                dataIndex: 'system_id',
                width: 120,
                renderer: function (value, metaData, record) {
                    if (!value) return record.get('system_name') || '';
                    var store = {data: mainView.systems || []};
                    var res = store.data.find(function (i) {
                        return i.system_id == value;
                    });
                    return res ? res.description : record.get('system_name') || value;
                }
            },
            {
                text: 'Installation',
                dataIndex: 'installation_method_id',
                width: 120,
                renderer: function (value, metaData, record) {
                    if (!value) return record.get('installation_method_name') || '';
                    var store = {data: mainView.installationMethods || []};
                    var res = store.data.find(function (i) {
                        return i.installation_method_id == value;
                    });
                    return res ? res.installation_method_name : record.get('installation_method_name') || value;
                }
            },
            {
                text: 'Location',
                dataIndex: 'location_id',
                width: 150,
                renderer: function (value, metaData, record) {
                    return record.get('location_name') || value;
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['id', 'name', 'code'],
                        data: []
                    },
                    displayField: 'name',
                    valueField: 'id',
                    queryMode: 'local',
                    listeners: {
                        beforequery: function (queryPlan) {
                            var locationsStore = this.getStore();
                            var boqLocationsGrid = mainView.down('boq-locations-grid');
                            if (boqLocationsGrid) {
                                var data = [];
                                boqLocationsGrid.getStore().each(function (rec) {
                                    data.push({
                                        id: rec.get('id'),
                                        name: rec.get('name'),
                                        code: rec.get('code')
                                    });
                                });
                                locationsStore.loadData(data);
                            }
                        },
                        select: function (combo, record) {
                            var gridRecord = me.getSelectionModel().getSelection()[0];
                            if (gridRecord) {
                                gridRecord.set('location_name', record.get('name'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Quantity',
                dataIndex: 'quantity',
                width: 100,
                editor: {
                    xtype: 'numberfield',
                    minValue: 1,
                    decimalPrecision: 4
                }
            },
            {
                xtype: 'actioncolumn',
                width: 30,
                items: [
                    {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Remove Line',
                        isDisabled: function (view, rowIndex, colIndex, item, record) {
                            var statusField = view.up('boq-main').down('form').getForm().findField('status');
                            return (statusField && statusField.getValue() === 'Approved');
                        },
                        handler: function (grid, rowIndex) {
                            var store = grid.getStore();
                            var record = store.getAt(rowIndex);
                            if (record.get('is_base_line')) {
                                var nextIndex = rowIndex + 1;
                                var recordsToRemove = [record];
                                while (nextIndex < store.getCount()) {
                                    var nextRec = store.getAt(nextIndex);
                                    if (!nextRec.get('is_base_line')) {
                                        recordsToRemove.push(nextRec);
                                        nextIndex++;
                                    } else {
                                        break;
                                    }
                                }
                                store.remove(recordsToRemove);
                            } else {
                                store.remove(record);
                            }
                        }
                    }
                ]
            }
        ];

        me.tbar = [
            {
                text: 'Add Line',
                itemId: 'addLineBtn',
                iconCls: 'x-fa fa-plus',
                handler: function () {
                    var r = Ext.create('BoqDetailModel', {
                        quantity: 1,
                        is_base_line: true
                    });
                    me.getStore().add(r);
                }
            }
        ];

        me.callParent(arguments);
    }
});
