/**
 * Bill of Quantities (BOQ) Tab
 * This tab displays and manages the BOQ items for the quotation.
 */
Ext.define('App.view.quotations.tabs.BOQ', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.quotations-tab-boq',
    title: 'Bill of Quantities (BOQ)',
    layout: 'border',

    initComponent: function () {
        var me = this;

        var buildingTreeStore = Ext.create('Ext.data.TreeStore', {
            root: {
                text: 'Project',
                expanded: true,
                children: []
            }
        });

        // Left Side: Building Navigator
        var buildingNavigator = {
            xtype: 'treepanel',
            region: 'west',
            title: 'Building Navigator',
            width: 250,
            split: true,
            collapsible: true,
            store: buildingTreeStore,
            rootVisible: true,
            useArrows: true,
            columns: [{
                xtype: 'treecolumn',
                text: 'Name',
                dataIndex: 'text',
                flex: 1,
                renderer: function (value, metaData, record) {
                    if (record.isLeaf()) {
                        var iconCls = record.get('iconCls');
                        return '<i class="' + iconCls + '" style="margin-right: 5px; width: 16px; text-align: center;"></i>' + value;
                    }
                    return value;
                }
            }],
            listeners: {
                selectionchange: function (selModel, selection) {
                    var node = selection[0];
                    var grid = me.down('gridpanel');
                    if (node && node.isLeaf()) {
                        var floorData = node.get('floorData');
                        if (floorData && floorData.floor_code) {
                            // Filter grid by floor_code
                            grid.getStore().clearFilter(true);
                            grid.getStore().filter('floor_code', floorData.floor_code);
                        } else {
                            grid.getStore().clearFilter();
                        }
                    } else if (node && node.getDepth() === 1) { // Building node
                        var buildingData = node.get('buildingData');
                        if (buildingData && buildingData.buildingInfo && buildingData.buildingInfo.building_code) {
                            grid.getStore().clearFilter(true);
                            grid.getStore().filter('building_code', buildingData.buildingInfo.building_code);
                        } else {
                            grid.getStore().clearFilter();
                        }
                    } else {
                        grid.getStore().clearFilter();
                    }
                }
            }
        };

        me.items = [
            buildingNavigator,
            {
                xtype: 'gridpanel',
                region: 'center',
                itemId: 'detailGrid',
                store: me.store,
                features: [{
                    ftype: 'grouping',
                    groupHeaderTpl: [
                        'Component: {[this.getDescription(values)]}',
                        {
                            getDescription: function (values) {
                                var record = values.children[0];
                                if (record) {
                                    return record.get('component_description') || values.name;
                                }
                                return values.name;
                            }
                        }
                    ],
                    startCollapsed: false
                }],
                plugins: {
                    ptype: 'rowediting',
                    clicksToEdit: 2
                },
                columns: [
                    {
                        text: 'Component',
                        dataIndex: 'component_code',
                        width: 150,
                        renderer: function (value, metaData, record) {
                            return record.get('component_description') || value;
                        },
                        editor: {
                            xtype: 'combobox',
                            listeners: {
                                select: function (combo, record) {
                                    var rowEditing = combo.up('grid').findPlugin('rowediting');
                                    var editRecord = rowEditing ? rowEditing.context.record : null;
                                    if (editRecord) {
                                        editRecord.set('component_description', record.get('item_desc'));
                                        editRecord.set('component_code', record.get('item_code'));
                                        editRecord.set('project_component_code', record.get('item_code'));
                                        editRecord.set('component_id', record.get('item_id'));
                                    }
                                }
                            },
                            store: {
                                fields: ['item_id', 'item_code', 'item_desc'],
                                proxy: {
                                    type: 'ajax',
                                    url: '<?php echo rtrim(BASE_URL, ' / '); ?>/Inventory/Items/Main/data',
                                    reader: {
                                        type: 'json',
                                        rootProperty: 'data'
                                    }
                                },
                                autoLoad: true
                            },
                            valueField: 'item_code',
                            displayField: 'item_desc',
                            queryMode: 'local',
                            matchFieldWidth: false,
                            listConfig: {
                                minWidth: 300
                            },
                            allowBlank: false
                        }
                    },
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
                                        var rowEditing = editor.up('grid').findPlugin('rowediting');
                                        var record = rowEditing ? rowEditing.context.record : null;
                                        var component_id = null;
                                        var component_code = null;

                                        if (record) {
                                            component_code = record.get('component_code');
                                            component_id = record.get('component_id');
                                            component_description = record.get('component_description');

                                            // If component_id is not in record, try to find it in the store
                                            if (!component_id && component_code) {
                                                var combo = editor.up('grid').down('combobox[displayField=item_desc]');
                                                if (combo && combo.getStore()) {
                                                    var compRec = combo.getStore().findRecord('item_code', component_code);
                                                    if (compRec) {
                                                        component_id = compRec.get('item_id');
                                                        component_description = compRec.get('item_desc');
                                                        record.set('component_id', component_id);
                                                        record.set('component_description', component_description);
                                                    }
                                                }
                                            }

                                            if (!component_id && !component_code) {
                                                Ext.Msg.alert('Notice', 'Please select a Project Component first.');
                                                return;
                                            }
                                        }

                                        var itemWin = Ext.create('App.view.quotations.ItemWindow', {
                                            component_id: component_id,
                                            component_code: component_code,
                                            component_description: component_description,
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
                                                        unit_code: item.unit_code || item.unit,
                                                        unit_description: item.unit_description || item.unit,
                                                        total_price: totalPrice
                                                    });

                                                    if (rowEditing && rowEditing.getEditor()) {
                                                        rowEditing.getEditor().getForm().setValues({
                                                            item_code: item.item_code,
                                                            item_desc: item.item_desc,
                                                            qty: 1,
                                                            price: item.price,
                                                            unit_code: item.unit_code || item.unit,
                                                            unit_description: item.unit_description || item.unit,
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
                                        var markup = record.get('markup_percent') || 0;
                                        // Calculation: Qty * Unit Price * (1 + Markup %)
                                        record.set('total_price', newValue * price * (1 + markup / 100));
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
                                    url: '<?php echo rtrim(BASE_URL, ' / '); ?>/Inventory/Units/Main/all',
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
                                        var markup = record.get('markup_percent') || 0;
                                        // Calculation: Unit Price * Qty * (1 + Markup %)
                                        record.set('total_price', newValue * qty * (1 + markup / 100));
                                    }
                                }
                            }
                        }
                    },
                    {
                        text: 'Markup %',
                        dataIndex: 'markup_percent',
                        width: 100,
                        editor: {
                            xtype: 'numberfield',
                            minValue: 0,
                            listeners: {
                                change: function (field, newValue) {
                                    var rowEditing = field.up('grid').findPlugin('rowediting');
                                    var record = rowEditing ? rowEditing.context.record : null;
                                    if (record) {
                                        var qty = record.get('qty');
                                        var price = record.get('price');
                                        // Calculation: Qty * Unit Price * (1 + Markup %)
                                        record.set('total_price', qty * price * (1 + newValue / 100));
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
                            var markup = record.get('markup_percent') || 0;
                            // Calculation: Qty * Unit Price * (1 + Markup %)
                            var total = record.get('qty') * record.get('price') * (1 + markup / 100);
                            record.set('total_price', total, {commit: true, silent: true});
                            return Ext.util.Format.number(total, '0,000.00');
                        }
                    }
                ],
                tbar: [
                    {
                        text: 'Add Item',
                        iconCls: 'x-fa fa-plus-circle',
                        handler: function () {
                            var grid = this.up('grid');
                            var rowEditing = grid.findPlugin('rowediting');
                            if (rowEditing) {
                                rowEditing.cancelEdit();
                            }
                            var r = Ext.create(grid.getStore().getModel(), {
                                component_code: '',
                                unit_code: '',
                                item_code: '',
                                qty: 0,
                                item_desc: '',
                                price: 0,
                                detail_type: 'BOQ',
                                markup_percent: 0
                            });

                            // Pre-fill building and floor if selected in navigator
                            var tree = grid.up('quotations-tab-boq').down('treepanel');
                            var selection = tree.getSelectionModel().getSelection()[0];
                            if (selection) {
                                if (selection.isLeaf()) {
                                    var floorData = selection.get('floorData');
                                    var buildingNode = selection.parentNode;
                                    var buildingData = buildingNode ? buildingNode.get('buildingData') : null;

                                    if (floorData) r.set('floor_code', floorData.floor_code);
                                    if (buildingData && buildingData.buildingInfo) r.set('building_code', buildingData.buildingInfo.building_code);
                                } else if (selection.getDepth() === 1) {
                                    var buildingData = selection.get('buildingData');
                                    if (buildingData && buildingData.buildingInfo) r.set('building_code', buildingData.buildingInfo.building_code);
                                }
                            }

                            grid.getStore().insert(0, r);
                            if (rowEditing) {
                                rowEditing.startEdit(0, 0);
                            }
                        }
                    },
                    {
                        text: 'Remove Item',
                        iconCls: 'x-fa fa-minus-circle',
                        handler: function () {
                            var grid = this.up('grid');
                            var sm = grid.getSelectionModel();
                            var selection = sm.getSelection();

                            if (selection.length > 0) {
                                Ext.Msg.confirm('Remove Item', 'Are you sure you want to remove the selected item(s)?', function (btn) {
                                    if (btn === 'yes') {
                                        grid.getStore().remove(selection);
                                    }
                                });
                            } else {
                                Ext.Msg.alert('Notice', 'Please select an item to remove.');
                            }
                        }
                    },
                    '-',
                    {
                        text: 'Remove Component',
                        iconCls: 'x-fa fa-trash',
                        style: 'color: red;',
                        handler: function () {
                            var grid = this.up('grid');
                            var sm = grid.getSelectionModel();
                            var selection = sm.getSelection();

                            if (selection.length > 0) {
                                var record = selection[0];
                                var componentCode = record.get('component_code');
                                var componentDesc = record.get('component_description') || componentCode;

                                if (!componentCode) {
                                    Ext.Msg.alert('Notice', 'The selected item does not have a component code.');
                                    return;
                                }

                                Ext.Msg.confirm('Remove Component', 'Are you sure you want to remove the entire component "<b>' + componentDesc + '</b>" and all its associated items (Materials, Labor, Overhead)?', function (btn) {
                                    if (btn === 'yes') {
                                        var formWindow = grid.up('window');
                                        if (formWindow) {
                                            // Get all stores from the form window
                                            // These are defined in initComponent of App.view.quotations.Form
                                            // We can access them if they were assigned to 'me' or if we find them via the tab panels

                                            var storesToClean = [];

                                            // BOQ store (the current grid's store)
                                            storesToClean.push(grid.getStore());

                                            // Find other stores via their respective tabs
                                            var materialTab = formWindow.down('quotations-tab-materials');
                                            if (materialTab && materialTab.store) storesToClean.push(materialTab.store);

                                            var laborTab = formWindow.down('quotations-tab-labor');
                                            if (laborTab && laborTab.store) storesToClean.push(laborTab.store);

                                            var overheadTab = formWindow.down('quotations-tab-overhead');
                                            if (overheadTab && overheadTab.store) storesToClean.push(overheadTab.store);

                                            storesToClean.forEach(function (store) {
                                                if (store) {
                                                    var recordsToRemove = [];
                                                    store.each(function (rec) {
                                                        if (rec.get('component_code') === componentCode) {
                                                            recordsToRemove.push(rec);
                                                        }
                                                    });
                                                    if (recordsToRemove.length > 0) {
                                                        store.remove(recordsToRemove);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            } else {
                                Ext.Msg.alert('Notice', 'Please select an item within the component you want to remove.');
                            }
                        }
                    }
                ]
            }
        ];

        me.on('activate', function () {
            me.syncBuildingNavigator();
        });

        me.callParent(arguments);
    },

    /**
     * Synchronize the building navigator with the buildings tab
     */
    syncBuildingNavigator: function () {
        var me = this;
        var formWindow = me.up('window');
        if (formWindow) {
            var buildingsTab = formWindow.down('quotations-tab-buildings');
            if (buildingsTab) {
                var tree = me.down('treepanel');
                var targetStore = tree.getStore();
                var sourceTree = buildingsTab.down('treepanel');

                if (sourceTree && sourceTree.getStore()) {
                    var root = sourceTree.getStore().getRoot();
                    var targetRoot = targetStore.getRoot();

                    // Perform synchronization
                    targetRoot.removeAll();
                    targetRoot.set('text', root.get('text'));

                    var copyNodes = function (sourceNode, targetNode) {
                        sourceNode.eachChild(function (child) {
                            var newNode = targetNode.appendChild({
                                text: child.get('text'),
                                leaf: child.isLeaf(),
                                expanded: child.isExpanded(),
                                iconCls: child.get('iconCls'),
                                buildingData: child.get('buildingData'),
                                floorData: child.get('floorData')
                            });
                            if (!child.isLeaf()) {
                                copyNodes(child, newNode);
                            }
                        });
                    };
                    copyNodes(root, targetRoot);
                }
            }
        }
    }
});
