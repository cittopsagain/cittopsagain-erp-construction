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
                trades: <?php echo isset($trades) ? json_encode($trades) : '[]'; ?>,
                work_phases: <?php echo isset($work_phases) ? json_encode($work_phases) : '[]'; ?>,
                systems: <?php echo isset($systems) ? json_encode($systems) : '[]'; ?>
            },
            {
                region: 'center',
                xtype: 'tabpanel',
                itemId: 'compositionDetailTabs',
                disabled: true,
                items: [
                    {
                        title: 'Materials',
                        xtype: 'composition-template-materials-grid'
                    },
                    {
                        title: 'Labor',
                        xtype: 'composition-template-labor-grid'
                    }
                ],
                listeners: {
                    beforetabchange: function (tabPanel, newTab, oldTab) {
                        var materialsGrid = tabPanel.down('composition-template-materials-grid');
                        var laborGrid = tabPanel.down('composition-template-labor-grid');

                        var hasUnsavedChanges = function (grid) {
                            if (!grid) return false;
                            var store = grid.getStore();
                            return store.getModifiedRecords().length > 0 || store.getNewRecords().length > 0 || store.getRemovedRecords().length > 0;
                        };

                        if (hasUnsavedChanges(materialsGrid) || hasUnsavedChanges(laborGrid)) {
                            // Check if a row is being edited
                            var materialsEditing = materialsGrid.findPlugin('rowediting');
                            var laborEditing = laborGrid.findPlugin('rowediting');
                            if ((materialsEditing && materialsEditing.editing) || (laborEditing && laborEditing.editing)) {
                                Ext.Msg.alert('Unsaved Changes', 'Please complete or cancel your current edit in the Composition Details before switching tabs.');
                                return false;
                            }

                            Ext.Msg.alert('Unsaved Changes', 'Please save or cancel your changes in the Composition Details before switching tabs.');
                            return false;
                        }
                    }
                }
            }
        ]
    });

    Ext.define('App.view.compositiontemplates.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.composition-template-grid',
        store: {
            fields: ['id', 'template_code', 'template_name', 'installation_method_id', 'installation_method_name', 'trade_id', 'trade_name', 'phase_id', 'phase_name', 'system_id', 'system_name', 'created_at'],
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
                beforeedit: function (editor, context) {
                    var record = context.record;
                    var tradeId = record.get('trade_id');
                    if (tradeId) {
                        var systemCombo = editor.editor.down('combobox[name=system_id]');
                        if (systemCombo) {
                            var systemStore = systemCombo.getStore();
                            systemStore.getProxy().setExtraParam('trade_id', tradeId);
                            systemStore.load();
                        }
                    }
                },
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
                            var grid = combo.up('grid');
                            if (!grid) return;
                            var editingPlugin = grid.findPlugin('rowediting');
                            var gridRecord = null;

                            if (editingPlugin && editingPlugin.activeRecord) {
                                gridRecord = editingPlugin.activeRecord;
                            } else {
                                var selection = grid.getSelectionModel().getSelection();
                                if (selection.length > 0) {
                                    gridRecord = selection[0];
                                }
                            }

                            if (gridRecord) {
                                gridRecord.set('installation_method_name', record.get('installation_method_name'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Trade',
                dataIndex: 'trade_id',
                width: 200,
                renderer: function (value, metaData, record) {
                    return record.get('trade_name');
                },
                editor: {
                    xtype: 'combobox',
                    name: 'trade_id',
                    store: {
                        fields: ['trade_id', 'description'],
                        data: []
                    },
                    displayField: 'description',
                    valueField: 'trade_id',
                    queryMode: 'local',
                    forceSelection: true,
                    allowBlank: false,
                    listeners: {
                        beforerender: function (combo) {
                            var grid = combo.up('grid');
                            if (grid && grid.trades) {
                                combo.getStore().loadData(grid.trades);
                            }
                        },
                        select: function (combo, record) {
                            var grid = combo.up('grid');
                            if (!grid) return;
                            var editingPlugin = grid.findPlugin('rowediting');
                            var gridRecord = null;

                            if (editingPlugin && editingPlugin.activeRecord) {
                                gridRecord = editingPlugin.activeRecord;
                            } else {
                                var selection = grid.getSelectionModel().getSelection();
                                if (selection.length > 0) {
                                    gridRecord = selection[0];
                                }
                            }

                            if (gridRecord) {
                                gridRecord.set('trade_name', record.get('description'));

                                // Clear system selection when trade changes
                                gridRecord.set('system_id', '');
                                gridRecord.set('system_name', '');

                                // Automatically check/load the system for this trade
                                var systemCombo = null;
                                if (editingPlugin && editingPlugin.editor) {
                                    systemCombo = editingPlugin.editor.down('combobox[name=system_id]');
                                }

                                if (systemCombo) {
                                    var systemStore = systemCombo.getStore();
                                    systemStore.getProxy().setExtraParam('trade_id', record.get('trade_id'));
                                    systemStore.load({
                                        callback: function (records, operation, success) {
                                            if (success) {
                                                if (records.length === 1) {
                                                    var systemRecord = records[0];
                                                    gridRecord.set('system_id', systemRecord.get('system_id'));
                                                    gridRecord.set('system_name', systemRecord.get('description'));

                                                    // Also update the combobox value in the editor if visible
                                                    if (systemCombo.isVisible()) {
                                                        systemCombo.setValue(systemRecord.get('system_id'));
                                                    }
                                                } else {
                                                    // If more than one or zero, clear the system selection
                                                    gridRecord.set('system_id', '');
                                                    gridRecord.set('system_name', '');
                                                    if (systemCombo.isVisible()) {
                                                        systemCombo.setValue('');
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    }
                }
            },
            {
                text: 'System',
                dataIndex: 'system_id',
                width: 200,
                renderer: function (value, metaData, record) {
                    return record.get('system_name');
                },
                editor: {
                    xtype: 'combobox',
                    name: 'system_id',
                    minChars: 0,
                    store: {
                        fields: ['system_id', 'description'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/Systems/Main/all',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: false
                    },
                    displayField: 'description',
                    valueField: 'system_id',
                    queryMode: 'remote',
                    forceSelection: true,
                    allowBlank: false,
                    listeners: {
                        beforequery: function (queryPlan) {
                            var combo = queryPlan.combo;
                            var grid = combo.up('grid');
                            var editingPlugin = grid.findPlugin('rowediting');
                            var gridRecord = null;

                            if (editingPlugin && editingPlugin.activeRecord) {
                                gridRecord = editingPlugin.activeRecord;
                            } else {
                                var selection = grid.getSelectionModel().getSelection();
                                if (selection.length > 0) {
                                    gridRecord = selection[0];
                                }
                            }

                            if (gridRecord) {
                                var tradeId = gridRecord.get('trade_id');
                                var tradeCombo = null;
                                if (editingPlugin && editingPlugin.editor) {
                                    tradeCombo = editingPlugin.editor.down('combobox[name=trade_id]');
                                }

                                if (!tradeId && tradeCombo) {
                                    tradeId = tradeCombo.getValue();
                                }

                                if (tradeId) {
                                    combo.getStore().getProxy().setExtraParam('trade_id', tradeId);
                                } else {
                                    combo.getStore().removeAll();
                                    return false;
                                }
                            }
                        },
                        expand: function (combo) {
                            var grid = this.up('grid');
                            var editingPlugin = grid.findPlugin('rowediting');
                            var gridRecord = null;

                            if (editingPlugin && editingPlugin.activeRecord) {
                                gridRecord = editingPlugin.activeRecord;
                            } else {
                                var selection = grid.getSelectionModel().getSelection();
                                if (selection.length > 0) {
                                    gridRecord = selection[0];
                                }
                            }

                            if (gridRecord) {
                                var tradeId = gridRecord.get('trade_id');

                                // Check if the trade combo has a value that hasn't been synced to the record yet
                                var tradeCombo = null;
                                if (editingPlugin && editingPlugin.editor) {
                                    tradeCombo = editingPlugin.editor.down('combobox[name=trade_id]');
                                }

                                if (!tradeId && tradeCombo) {
                                    tradeId = tradeCombo.getValue();
                                }

                                if (tradeId) {
                                    var store = combo.getStore();
                                    store.getProxy().setExtraParam('trade_id', tradeId);
                                    if (store.getCount() === 0 || store.getProxy().getExtraParams().trade_id !== tradeId) {
                                        store.load();
                                    }
                                } else {
                                    combo.getStore().removeAll();
                                    Ext.Msg.alert('Info', 'Please select a Trade first.');
                                }
                            }
                        },
                        select: function (combo, record) {
                            var grid = combo.up('grid');
                            if (!grid) return;
                            var editingPlugin = grid.findPlugin('rowediting');
                            var gridRecord = null;

                            if (editingPlugin && editingPlugin.activeRecord) {
                                gridRecord = editingPlugin.activeRecord;
                            } else {
                                var selection = grid.getSelectionModel().getSelection();
                                if (selection.length > 0) {
                                    gridRecord = selection[0];
                                }
                            }

                            if (gridRecord) {
                                gridRecord.set('system_name', record.get('description'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Work Phase',
                dataIndex: 'phase_id',
                width: 200,
                renderer: function (value, metaData, record) {
                    return record.get('phase_name');
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['phase_id', 'description'],
                        data: []
                    },
                    displayField: 'description',
                    valueField: 'phase_id',
                    queryMode: 'local',
                    forceSelection: true,
                    allowBlank: false,
                    listeners: {
                        beforerender: function (combo) {
                            var grid = combo.up('grid');
                            if (grid && grid.work_phases) {
                                combo.getStore().loadData(grid.work_phases);
                            }
                        },
                        select: function (combo, record) {
                            var grid = combo.up('grid');
                            if (!grid) return;
                            var editingPlugin = grid.findPlugin('rowediting');
                            var gridRecord = null;

                            if (editingPlugin && editingPlugin.activeRecord) {
                                gridRecord = editingPlugin.activeRecord;
                            } else {
                                var selection = grid.getSelectionModel().getSelection();
                                if (selection.length > 0) {
                                    gridRecord = selection[0];
                                }
                            }

                            if (gridRecord) {
                                gridRecord.set('phase_name', record.get('description'));
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
                        trade_id: '',
                        trade_name: '',
                        phase_id: '',
                        phase_name: '',
                        system_id: '',
                        system_name: ''
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
            beforeselectionchange: function (model, selected) {
                var mainPanel = this.up('panel');
                if (!mainPanel) return;
                var tabContainer = mainPanel.down('#compositionDetailTabs');
                if (!tabContainer) return;

                var materialsGrid = tabContainer.down('composition-template-materials-grid');
                var laborGrid = tabContainer.down('composition-template-labor-grid');

                var hasUnsavedChanges = function (grid) {
                    if (!grid) return false;
                    var store = grid.getStore();
                    return store.getModifiedRecords().length > 0 || store.getNewRecords().length > 0 || store.getRemovedRecords().length > 0;
                };

                if (hasUnsavedChanges(materialsGrid) || hasUnsavedChanges(laborGrid)) {
                    // Check if a row is being edited
                    var materialsEditing = materialsGrid.findPlugin('rowediting');
                    var laborEditing = laborGrid.findPlugin('rowediting');
                    if ((materialsEditing && materialsEditing.editing) || (laborEditing && laborEditing.editing)) {
                        Ext.Msg.alert('Unsaved Changes', 'Please complete or cancel your current edit in the Composition Details before selecting another template.');
                        return false;
                    }

                    Ext.Msg.alert('Unsaved Changes', 'Please save or cancel your changes in the Composition Details before selecting another template.');
                    return false;
                }
            },
            selectionchange: function (model, records) {
                var removeBtn = this.down('#removeTemplate');
                if (removeBtn) removeBtn.setDisabled(!records.length);

                var mainPanel = this.up('panel');
                if (!mainPanel) return;

                var tabContainer = mainPanel.down('#compositionDetailTabs');
                if (!tabContainer) return;

                var materialsGrid = tabContainer.down('composition-template-materials-grid');
                var laborGrid = tabContainer.down('composition-template-labor-grid');

                if (records.length > 0) {
                    var record = records[0];
                    if (!record.phantom) {
                        tabContainer.setDisabled(false);
                        tabContainer.setTitle('Composition Details for: ' + record.get('template_code') + ' - ' + record.get('template_name'));

                        if (materialsGrid) {
                            var mStore = materialsGrid.getStore();
                            mStore.getProxy().setExtraParam('template_id', record.get('id'));
                            mStore.getProxy().setExtraParam('detail_type', 'MATERIAL');
                            mStore.load();
                        }

                        if (laborGrid) {
                            var lStore = laborGrid.getStore();
                            lStore.getProxy().setExtraParam('template_id', record.get('id'));
                            lStore.getProxy().setExtraParam('detail_type', 'LABOR');
                            lStore.load();
                        }
                    } else {
                        tabContainer.setDisabled(true);
                        tabContainer.setTitle('Composition Details');
                        if (materialsGrid) materialsGrid.getStore().removeAll();
                        if (laborGrid) laborGrid.getStore().removeAll();
                    }
                } else {
                    tabContainer.setDisabled(true);
                    tabContainer.setTitle('Composition Details');
                    if (materialsGrid) materialsGrid.getStore().removeAll();
                    if (laborGrid) laborGrid.getStore().removeAll();
                }
            }
        }
    });

    Ext.define('App.view.compositiontemplates.MaterialsGrid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.composition-template-materials-grid',
        store: {
            fields: ['id', 'template_id', 'detail_type', 'inventory_item_id', 'item_code', 'item_desc', 'seq', 'qty_formula', 'waste_percentage', 'remarks'],
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
                    var mainPanel = grid.up('composition-template-main');
                    if (!mainPanel) return;
                    var mainGrid = mainPanel.down('composition-template-grid');
                    if (!mainGrid) return;
                    var selection = mainGrid.getSelectionModel().getSelection();
                    var templateRecord = selection[0];

                    if (!templateRecord) return;

                    record.set('template_id', templateRecord.get('id'));
                    record.set('detail_type', 'MATERIAL');

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
                text: 'Inventory Item',
                dataIndex: 'item_desc',
                width: 250,
                renderer: function (value, meta, record) {
                    return value ? value + ' (' + record.get('item_code') + ')' : '';
                },
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['item_id', 'item_code', 'item_desc'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Items/Main/all',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: false
                    },
                    displayField: 'item_desc',
                    valueField: 'item_id',
                    minChars: 2,
                    queryMode: 'remote',
                    listeners: {
                        select: function (combo, record) {
                            var grid = combo.up('grid');
                            if (!grid) return;
                            var rowEditing = grid.findPlugin('rowediting');
                            if (!rowEditing || !rowEditing.context) return;
                            var gridRecord = rowEditing.context.record;
                            if (gridRecord) {
                                gridRecord.set('inventory_item_id', record.get('item_id'));
                                gridRecord.set('item_code', record.get('item_code'));
                                gridRecord.set('item_desc', record.get('item_desc'));
                            }
                        }
                    }
                }
            },
            {
                text: 'Qty Formula',
                dataIndex: 'qty_formula',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Waste %',
                dataIndex: 'waste_percentage',
                width: 100,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    maxValue: 100
                }
            },
            {
                text: 'Remarks',
                dataIndex: 'remarks',
                flex: 1,
                editor: {
                    xtype: 'textfield'
                }
            }
        ],
        tbar: [
            {
                text: 'Add Material',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        seq: store.getCount() + 1,
                        detail_type: 'MATERIAL',
                        inventory_item_id: null,
                        item_code: '',
                        item_desc: '',
                        qty_formula: '',
                        waste_percentage: 0,
                        remarks: ''
                    });

                    store.insert(store.getCount(), r);
                    editing.startEdit(r, 0);
                }
            },
            {
                text: 'Remove Material',
                itemId: 'removeMaterial',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to remove this material?', function (choice) {
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
                                            Ext.Msg.alert('Error', 'Failed to remove material.');
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
                this.down('#removeMaterial').setDisabled(!records.length);
            }
        }
    });

    Ext.define('App.view.compositiontemplates.LaborGrid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.composition-template-labor-grid',
        store: {
            fields: ['id', 'template_id', 'detail_type', 'role', 'hours', 'rate', 'formula', 'seq'],
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
                    var mainPanel = grid.up('composition-template-main');
                    if (!mainPanel) return;
                    var mainGrid = mainPanel.down('composition-template-grid');
                    if (!mainGrid) return;
                    var selection = mainGrid.getSelectionModel().getSelection();
                    var templateRecord = selection[0];

                    if (!templateRecord) return;

                    record.set('template_id', templateRecord.get('id'));
                    record.set('detail_type', 'LABOR');

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
                text: 'Role',
                dataIndex: 'role',
                flex: 1,
                editor: {
                    xtype: 'combobox',
                    store: {
                        fields: ['pos_id', 'pos_name'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/JobPositions/Main/reportsTo',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: true
                    },
                    displayField: 'pos_name',
                    valueField: 'pos_name',
                    queryMode: 'remote'
                }
            },
            {
                text: 'Hours',
                dataIndex: 'hours',
                width: 100,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0
                }
            },
            {
                text: 'Rate',
                dataIndex: 'rate',
                width: 100,
                editor: {
                    xtype: 'numberfield',
                    minValue: 0
                }
            },
            {
                text: 'Formula',
                dataIndex: 'formula',
                flex: 1,
                editor: {
                    xtype: 'textfield'
                }
            }
        ],
        tbar: [
            {
                text: 'Add Labor',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        seq: store.getCount() + 1,
                        detail_type: 'LABOR',
                        role: '',
                        hours: 0,
                        rate: 0,
                        formula: ''
                    });

                    store.insert(store.getCount(), r);
                    editing.startEdit(r, 0);
                }
            },
            {
                text: 'Remove Labor',
                itemId: 'removeLabor',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to remove this labor item?', function (choice) {
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
                                            Ext.Msg.alert('Error', 'Failed to remove labor item.');
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
                this.down('#removeLabor').setDisabled(!records.length);
            }
        }
    });

</script>
