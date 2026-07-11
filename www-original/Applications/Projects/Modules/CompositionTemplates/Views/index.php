<script type="text/javascript">
    Ext.define('App.view.compositiontemplates.Main', {
        extend: 'Ext.panel.Panel',
        alias: 'widget.composition-template-main',
        draggable: true,
        frame: true,
        resizable: true,
        layout: 'border',
        height: 730,
        items: [
            {
                region: 'north',
                xtype: 'composition-template-grid',
                height: '50%',
                split: true,
                title: 'Composition Templates',
                installation_methods: <?php echo isset($installation_methods) ? json_encode($installation_methods) : '[]'; ?>,
                item_types: <?php echo isset($item_types) ? json_encode($item_types) : '[]'; ?>
            },
            {
                region: 'center',
                xtype: 'composition-template-detail-grid',
                height: '50%'
            }
        ]
    });

    Ext.define('App.view.compositiontemplates.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.composition-template-grid',
        store: {
            fields: ['id', 'template_code', 'template_name', 'installation_method_id', 'installation_method_name', 'item_type_id', 'item_type_name', 'created_at'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/CompositionTemplates/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/CompositionTemplates/Main/save',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                if (record.phantom && result.id) {
                                    record.set('id', result.id);
                                    if (result.created_at) {
                                        record.set('created_at', result.created_at);
                                    }
                                }
                                record.commit();
                                Ext.Msg.alert('Success', result.message);
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
            {text: 'ID', dataIndex: 'id', width: 100},
            {
                text: 'Template Code',
                dataIndex: 'template_code',
                width: 150,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Template Name',
                dataIndex: 'template_name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Installation Method',
                dataIndex: 'installation_method_id',
                width: 200,
                renderer: function (value, metaData, record) {
                    return record.get('installation_method_name');
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['installation_method_id', 'installation_method_name'],
                        data: []
                    },
                    displayField: 'installation_method_name',
                    valueField: 'installation_method_id',
                    queryMode: 'local',
                    forceSelection: true,
                    allowBlank: false,
                    listeners: {
                        beforerender: function (combo) {
                            var grid = combo.up('grid');
                            if (grid && grid.installation_methods) {
                                combo.getStore().loadData(grid.installation_methods);
                            }
                        },
                        select: function (combo, record) {
                            var gridRecord = this.up('grid').getSelectionModel().getSelection()[0];
                            if (gridRecord) {
                                gridRecord.set('installation_method_name', record.get('installation_method_name'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Item Type',
                dataIndex: 'item_type_id',
                width: 200,
                renderer: function (value, metaData, record) {
                    return record.get('item_type_name');
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['id', 'type_name'],
                        data: []
                    },
                    displayField: 'type_name',
                    valueField: 'id',
                    queryMode: 'local',
                    forceSelection: true,
                    allowBlank: false,
                    listeners: {
                        beforerender: function (combo) {
                            var grid = combo.up('grid');
                            if (grid && grid.item_types) {
                                combo.getStore().loadData(grid.item_types);
                            }
                        },
                        select: function (combo, record) {
                            var gridRecord = this.up('grid').getSelectionModel().getSelection()[0];
                            if (gridRecord) {
                                gridRecord.set('item_type_name', record.get('type_name'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Date Created',
                dataIndex: 'created_at',
                width: 150
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying templates {0} - {1} of {2}',
            emptyMsg: "No templates to display"
        },
        tbar: [
            {
                text: 'Add Template',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        template_code: '',
                        template_name: '',
                        installation_method_id: '',
                        installation_method_name: '',
                        item_type_id: '',
                        item_type_name: ''
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Template',
                itemId: 'removeTemplate',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this template?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/CompositionTemplates/Main/delete',
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
                                            Ext.Msg.alert('Error', 'Failed to delete template.');
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
                this.down('#removeTemplate').setDisabled(!records.length);
                var detailGrid = this.up('panel').down('composition-template-detail-grid');
                if (records.length > 0) {
                    var record = records[0];
                    if (!record.phantom) {
                        detailGrid.setDisabled(false);
                        detailGrid.setTitle('Components for: ' + record.get('template_code') + ' - ' + record.get('template_name'));
                        var store = detailGrid.getStore();
                        store.getProxy().setExtraParam('template_id', record.get('id'));
                        store.load();
                    } else {
                        detailGrid.setDisabled(true);
                        detailGrid.setTitle('Components');
                        detailGrid.getStore().removeAll();
                    }
                } else {
                    detailGrid.setDisabled(true);
                    detailGrid.setTitle('Components');
                    detailGrid.getStore().removeAll();
                }
            }
        }
    });

    Ext.define('App.view.compositiontemplates.DetailGrid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.composition-template-detail-grid',
        title: 'Components',
        store: {
            fields: ['id', 'template_id', 'seq', 'description', 'formula'],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/CompositionTemplates/Main/detailData',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
                }
            },
            autoLoad: false
        },
        plugins: {
            ptype: 'rowediting',
            clicksToEdit: 2,
            listeners: {
                edit: function (editor, context) {
                    var record = context.record;
                    var grid = context.grid;
                    var mainGrid = grid.up('panel').down('composition-template-grid');
                    var templateRecord = mainGrid.getSelectionModel().getSelection()[0];

                    if (!templateRecord) return;

                    record.set('template_id', templateRecord.get('id'));

                    Ext.Ajax.request({
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/CompositionTemplates/Main/saveDetail',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                if (record.phantom && result.id) {
                                    record.set('id', result.id);
                                }
                                record.commit();
                                Ext.Msg.alert('Success', result.message);
                            } else {
                                Ext.Msg.alert('Failed', result.message);
                                grid.getStore().load();
                            }
                        },
                        failure: function (response) {
                            Ext.Msg.alert('Error', 'Failed to save changes.');
                            grid.getStore().load();
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
            {
                text: 'Seq',
                dataIndex: 'seq',
                width: 60,
                editor: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    minValue: 1
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
                text: 'Formula',
                dataIndex: 'formula',
                width: 300,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            }
        ],
        tbar: [
            {
                text: 'Add Component',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        seq: store.getCount() + 1,
                        description: '',
                        formula: ''
                    });

                    store.insert(store.getCount(), r);
                    editing.startEdit(r, 0);
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
                        Ext.Msg.confirm('Delete', 'Are you sure you want to remove this component?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/CompositionTemplates/Main/deleteDetail',
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
                                            Ext.Msg.alert('Error', 'Failed to remove component.');
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
            }
        }
    });
</script>
