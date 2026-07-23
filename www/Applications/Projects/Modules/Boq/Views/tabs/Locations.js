Ext.define('App.view.boq.LocationsGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.boq-locations-grid',
    itemId: 'locationsGrid',
    // title: 'Project Locations',
    height: 500,
    initComponent: function () {
        var me = this;
        var mainView = me.up('boq-main');

        me.store = {
            fields: ['id', 'code', 'name', 'type_id', 'type_name', 'type_code', 'parent_id', 'parent_name', 'parent_code', 'boq_id'],
            pageSize: 100,
            proxy: {
                type: 'ajax',
                url: mainView.baseUrl + '/Projects/Boq/Main/location_data',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
                }
            },
            autoLoad: false
        };

        me.plugins = {
            ptype: 'cellediting',
            pluginId: 'locationCellEditing',
            clicksToEdit: 1,
            listeners: {
                beforeedit: function (editor, context) {
                    var statusField = context.grid.up('boq-main').down('form').getForm().findField('status');
                    if (statusField && statusField.getValue() === 'Approved') {
                        return false;
                    }
                    if (context.field === 'type_id') {
                        var typeStore = context.column.getEditor().getStore();
                        typeStore.loadData(mainView.locationTypes || []);
                    }

                    if (context.field === 'parent_id') {
                        var parentStore = context.column.getEditor().getStore();
                        // Load all locations from the current grid for parent selection, filtering out self
                        var currentLocations = [];
                        context.grid.getStore().each(function (rec) {
                            if (rec.get('id') != context.record.get('id')) {
                                currentLocations.push({
                                    id: rec.get('id'),
                                    name: rec.get('name'),
                                    code: rec.get('code')
                                });
                            }
                        });

                        // Add "Project" as a dummy parent option (id: null or 0)
                        currentLocations.unshift({id: null, name: 'Project', code: 'PROJ'});
                        parentStore.loadData(currentLocations);

                        // We also need to refresh the view to ensure the renderer can find the records in the store
                        // since we just updated the store data.
                        context.column.getEditor().on('select', function () {
                            context.grid.getView().refresh();
                        }, this, {single: true});
                    }
                }
            }
        };

        me.columns = [
            {text: 'ID', dataIndex: 'id', width: 50},
            {
                text: 'Code',
                dataIndex: 'code',
                width: 120,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Name',
                dataIndex: 'name',
                width: 200,
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Type',
                dataIndex: 'type_id',
                width: 150,
                renderer: function (value, metaData, record) {
                    if (record.get('type_name')) return record.get('type_name');
                    var store = this.getColumns()[3].getEditor().getStore();
                    var match = store.findRecord('id', value);
                    return match ? match.get('name') : value;
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['id', 'name', 'code'],
                        data: mainView.locationTypes || []
                    },
                    displayField: 'name',
                    valueField: 'id',
                    queryMode: 'local',
                    allowBlank: false,
                    forceSelection: true
                }
            },
            {
                text: 'Parent',
                dataIndex: 'parent_id',
                width: 200,
                renderer: function (value, metaData, record) {
                    if (!value || value == 0) return 'Project';
                    if (record.get('parent_name')) return record.get('parent_name');
                    var store = this.getColumns()[4].getEditor().getStore();
                    var match = store.findRecord('id', value);
                    return match ? match.get('name') : value;
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
                    allowBlank: true,
                    forceSelection: true,
                    emptyText: 'Project'
                }
            },
            {
                xtype: 'actioncolumn',
                width: 30,
                itemId: 'trashAction',
                items: [
                    {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Remove Location',
                        isDisabled: function (view, rowIndex, colIndex, item, record) {
                            var statusField = view.up('boq-main').down('form').getForm().findField('status');
                            return (statusField && statusField.getValue() === 'Approved');
                        },
                        handler: function (grid, rowIndex) {
                            grid.getStore().removeAt(rowIndex);
                        }
                    }
                ]
            }
        ];

        me.bbar = {
            xtype: 'pagingtoolbar',
            displayInfo: true
        };

        me.tbar = [
            {
                text: 'Add Location',
                itemId: 'addLocationBtn',
                iconCls: 'x-fa fa-plus',
                handler: function () {
                    var grid = this.up('grid');
                    var store = grid.getStore();
                    var main = this.up('boq-main');
                    var boq_id = main.down('form').getForm().findField('id').getValue();

                    var r = Ext.create(store.getModel(), {
                        code: '',
                        name: '',
                        type_id: null,
                        parent_id: null,
                        boq_id: boq_id
                    });

                    store.insert(0, r);
                }
            }
        ];

        me.callParent(arguments);
    }
});
