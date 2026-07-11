/**
 * Quotation Buildings Tab
 * This tab handles building-related information, including floors and typical floor configurations.
 */
Ext.define('App.view.quotations.tabs.Buildings', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.quotations-tab-buildings',
    title: 'Buildings',
    layout: 'border',
    // autoScroll removed because border layout children manage their own scrolling

    /**
     * Notify BOQ tab to sync its navigator
     */
    notifyBOQSync: function () {
        var me = this;
        var formWindow = me.up('window');
        if (formWindow) {
            var boqTab = formWindow.down('quotations-tab-boq');
            if (boqTab && boqTab.syncBuildingNavigator) {
                boqTab.syncBuildingNavigator();
            }
        }
    },

    /**
     * Update root name of the tree
     */
    updateRootName: function (name) {
        var me = this;
        var tree = me.down('treepanel');
        if (tree && tree.getStore()) {
            var root = tree.getStore().getRoot();
            root.set('text', Ext.isEmpty(name) ? 'Project' : name);
            me.notifyBOQSync();
        }
    },

    /**
     * Get building and floor data for saving
     */
    getBuildingsData: function () {
        var me = this;
        var tree = me.down('treepanel');
        var buildings = [];

        if (tree && tree.getStore()) {
            var root = tree.getStore().getRoot();
            root.cascadeBy(function (node) {
                if (node.get('buildingData')) {
                    buildings.push(node.get('buildingData'));
                }
            });
        }

        return buildings;
    },

    /**
     * Load building and floor data
     */
    loadBuildingsData: function (data) {
        var me = this;
        var tree = me.down('treepanel');
        if (!tree || !data) return;

        var store = tree.getStore();
        var root = store.getRoot();
        root.removeAll();

        if (Ext.isArray(data)) {
            // Update root name if project name is available in the form
            var formWindow = me.up('window');
            if (formWindow) {
                var headerTab = formWindow.down('quotations-tab-header');
                if (headerTab) {
                    var projectNameField = headerTab.getForm().findField('project_name');
                    var projectName = projectNameField ? projectNameField.getValue() : '';
                    me.updateRootName(projectName);
                }
            }

            Ext.Array.each(data, function (building) {
                var buildingNode = root.appendChild({
                    text: building.buildingInfo.building_name || 'Unnamed Building',
                    leaf: false,
                    expanded: true,
                    iconCls: 'x-fa fa-building',
                    buildingData: building
                });

                if (building.floors && Ext.isArray(building.floors)) {
                    Ext.Array.each(building.floors, function (floor) {
                        buildingNode.appendChild({
                            text: floor.floor_name || 'Unnamed Floor',
                            leaf: true,
                            floorData: floor
                        });
                    });
                }
            });
        }
    },

    /**
     * Reset form and grid
     */
    resetBuildingDetails: function () {
        var me = this;
        var form = me.down('form');
        var grid = me.down('#floorDetailsGrid');

        if (form) form.getForm().reset();
        if (grid && grid.getStore()) grid.getStore().removeAll();
        var previewPanel = me.down('panel[title^="Building Preview"]');
        if (previewPanel) {
            previewPanel.setTitle('Building Preview');
            previewPanel.update('No building selected');
        }
    },

    /**
     * Update Building Preview
     */
    updateBuildingPreview: function () {
        var me = this;
        var tree = me.down('treepanel');
        var previewPanel = me.down('panel[title^="Building Preview"]');
        if (!tree || !previewPanel) return;

        var selection = tree.getSelectionModel().getSelection()[0];
        if (!selection) {
            previewPanel.setTitle('Building Preview');
            previewPanel.update('No building selected');
            return;
        }

        var buildingNode = selection.isLeaf() ? selection.parentNode : selection;
        if (!buildingNode || buildingNode === tree.getStore().getRoot()) {
            previewPanel.setTitle('Building Preview');
            previewPanel.update('No building selected');
            return;
        }

        var data = buildingNode.get('buildingData');
        var buildingName = data && data.buildingInfo && data.buildingInfo.building_name ? data.buildingInfo.building_name : 'Building';

        if (!data || !data.floors || data.floors.length === 0) {
            previewPanel.setTitle('Building Preview - ' + buildingName);
            previewPanel.update('No floors added to ' + buildingName);
            return;
        }

        previewPanel.setTitle('Building Preview - ' + buildingName);
        var floors = data.floors;
        // Sort floors by level (descending for stacking top-down)
        var sortedFloors = Ext.Array.slice(floors);
        sortedFloors.sort(function (a, b) {
            return b.level - a.level;
        });

        var html = '<div style="display: flex; flex-direction: column; align-items: center; width: 100%; padding: 20px 0 0 0; min-height: 100%; box-sizing: border-box; position: relative;">';
        html += '<div style="flex: 1; display: flex; flex-direction: column; align-items: center; width: 100%; padding-bottom: 20px;">';

        Ext.Array.each(sortedFloors, function (floor, index) {
            var bgColor = '#E8F5E9'; // Default Light Green
            var borderColor = '#A5D6A7';
            var borderStyle = 'solid';
            var textColor = '#2E7D32';

            switch (floor.floor_type) {
                case 'Basement':
                    bgColor = '#EFEBE9';
                    borderColor = '#BCAAA4';
                    textColor = '#4E342E';
                    break;
                case 'Roof Deck':
                    bgColor = '#EDE7F6';
                    borderColor = '#9575CD';
                    borderStyle = 'dashed';
                    textColor = '#4527A0';
                    break;
                case 'Mezzanine':
                    bgColor = '#FFF9C4';
                    borderColor = '#FFF176';
                    textColor = '#FBC02D';
                    break;
                case 'Penthouse':
                    bgColor = '#F3E5F5';
                    borderColor = '#CE93D8';
                    textColor = '#7B1FA2';
                    break;
                default:
                // Regular floors already set as default
            }

            if (!floor.include_in_project) {
                bgColor = '#F5F5F5';
                borderColor = '#E0E0E0';
                textColor = '#9E9E9E';
            }

            // Floor Block
            html += '<div style="width: 140px; height: 60px; background-color: ' + bgColor + '; border: 1.5px ' + borderStyle + ' ' + borderColor + '; ' +
                'border-radius: 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; ' +
                'box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative; z-index: 2;" ' +
                'title="' + floor.floor_name + ' (Type: ' + floor.floor_type + ', Level: ' + floor.level + ')">';

            // Floor Code
            html += '<div style="font-weight: bold; font-size: 14px; color: ' + textColor + ';">' + floor.floor_code + '</div>';
            // Floor Name
            html += '<div style="font-size: 11px; color: ' + textColor + '; margin-top: 2px;">' + floor.floor_name + '</div>';

            html += '</div>';

            // Connector Line (except for the last floor)
            if (index < sortedFloors.length - 1) {
                html += '<div style="width: 0; height: 20px; border-left: 1px dashed #757575; z-index: 1;"></div>';
            }
        });
        html += '</div>'; // End of scrollable floor content area

        // Add Legend
        html += '<div style="position: sticky; bottom: 0; background-color: white; padding: 15px; border-top: 1px solid #eee; width: 100%; height: 80px; box-sizing: border-box; z-index: 10;">';
        html += '<div style="display: flex; flex-wrap: wrap; justify-content: space-between; max-width: 250px; margin: 0 auto;">';

        // Legend Item Helper
        var getLegendItem = function (label, bgColor, borderColor, borderStyle) {
            return '<div style="display: flex; align-items: center; width: 45%; margin-bottom: 10px;">' +
                '<div style="width: 16px; height: 16px; background-color: ' + bgColor + '; border: 1px ' + (borderStyle || 'solid') + ' ' + borderColor + '; border-radius: 2px; margin-right: 8px;"></div>' +
                '<span style="font-size: 12px; color: #555;">' + label + '</span>' +
                '</div>';
        };

        html += getLegendItem('Regular Floor', '#E8F5E9', '#A5D6A7');
        html += getLegendItem('Roof Deck', '#EDE7F6', '#9575CD', 'dashed');
        html += getLegendItem('Basement', '#EFEBE9', '#BCAAA4');
        html += getLegendItem('Excluded', '#F5F5F5', '#E0E0E0');

        html += '</div>';
        html += '</div>';

        previewPanel.update(html);
    },

    /**
     * Update project summary
     */
    updateProjectSummary: function () {
        var me = this;
        var tree = me.down('treepanel');
        var summaryPanel = me.down('panel[title="Project Summary"]');
        if (!tree || !summaryPanel) return;

        var totalBuildings = 0;
        var totalFloors = 0;
        var totalTypical = 0;
        var totalGFA = 0;

        tree.getStore().getRoot().cascadeBy(function (node) {
            if (node.isRoot()) return;
            // A building is a node at level 1 (direct child of root)
            if (node.getDepth() === 1) {
                totalBuildings++;
                var data = node.get('buildingData');
                if (data) {
                    if (data.buildingInfo) {
                        totalGFA += parseFloat(data.buildingInfo.total_gfa || 0);
                    }
                    if (data.floors) {
                        totalFloors += data.floors.length;
                        Ext.Array.each(data.floors, function (floor) {
                            if (floor.typical_floor) totalTypical++;
                        });
                    }
                }
            }
        });

        summaryPanel.down('displayfield[fieldLabel="Total Buildings"]').setValue(totalBuildings);
        summaryPanel.down('displayfield[fieldLabel="Total Floors (incl. basements)"]').setValue(totalFloors);
        summaryPanel.down('displayfield[fieldLabel="Total GFA (est.)"]').setValue(totalGFA.toFixed(2) + ' sqm');
        me.updateBuildingPreview();
    },

    /**
     * Handle building field changes to sync with tree and data
     */
    onBuildingFieldChange: function (field, newVal, oldVal, eOpts) {
        var me = this;
        var buildingNode = eOpts.buildingNode;
        var data = eOpts.data;

        var tree = me.down('treepanel');
        var currentSelection = tree.getSelectionModel().getSelection()[0];
        if (!currentSelection) return;

        var activeBuildingNode = currentSelection.isLeaf() ? currentSelection.parentNode : currentSelection;

        // Only sync if this field change is for the currently active building node
        if (activeBuildingNode !== buildingNode) return;

        if (field.name === 'building_name') {
            // Check if name already exists in other buildings
            var isDuplicate = false;
            var root = tree.getStore().getRoot();
            root.cascadeBy(function (node) {
                if (node.getDepth() === 1 && node !== buildingNode) {
                    if (node.get('text').toLowerCase() === (newVal || '').toLowerCase()) {
                        isDuplicate = true;
                        return false; // stop cascade
                    }
                }
            });

            if (isDuplicate) {
                Ext.Msg.alert('Error', 'Building name already exists. Please use a unique name.');
                field.suspendEvents();
                field.setValue(oldVal);
                field.resumeEvents();
                return;
            }

            buildingNode.set('text', newVal || 'Unnamed Building');
            data.buildingInfo.building_name = newVal;
            me.updateBuildingPreview();
            me.notifyBOQSync();
        } else {
            data.buildingInfo[field.name] = newVal;
            me.updateProjectSummary();
        }
    },

    initComponent: function () {
        var me = this;

        // Building Navigator Store (Mock/Example)
        var buildingTreeStore = Ext.create('Ext.data.TreeStore', {
            root: {
                text: 'Project',
                expanded: true,
                children: []
            }
        });

        me.on('activate', function () {
            var formWindow = me.up('window');
            if (formWindow) {
                var headerTab = formWindow.down('quotations-tab-header');
                if (headerTab) {
                    var projectNameField = headerTab.getForm().findField('project_name');
                    var projectName = projectNameField ? projectNameField.getValue() : '';
                    me.updateRootName(projectName);
                }
            }
        });

        // Floor Details Store
        var floorStore = Ext.create('Ext.data.Store', {
            fields: ['floor_name', 'floor_code', 'floor_type', 'level', 'height', 'area', 'typical_floor', 'include_in_project'],
            data: [],
            listeners: {
                datachanged: function (store) {
                    if (store.isLoadingData) return;
                    syncFloorsToTree(store);
                },
                update: {
                    fn: function (store) {
                        syncFloorsToTree(store);
                    },
                    buffer: 100
                }
            }
        });

        var syncFloorsToTree = function (store) {
            var tree = me.down('treepanel');
            var selModel = tree.getSelectionModel();
            var selection = selModel.getSelection()[0];
            if (!selection) return;

            var buildingNode = selection.isLeaf() ? selection.parentNode : selection;
            if (!buildingNode || buildingNode === tree.getStore().getRoot()) return;

            var data = buildingNode.get('buildingData');
            if (!data) return;

            data.floors = [];
            store.each(function (rec) {
                data.floors.push(rec.getData());
            });

            // Sync tree nodes for floors while preserving selection
            var selectedNode = selModel.getSelection()[0];
            var isFloorSelected = selectedNode && selectedNode.isLeaf();
            var selectedFloorIndex = -1;

            if (isFloorSelected) {
                // Find the index of the selected floor in the store data
                // This is more reliable than buildingNode.indexOf because buildingNode is about to be cleared
                var selectedFloorCode = selectedNode.get('floorData') ? selectedNode.get('floorData').floor_code : null;
                if (selectedFloorCode) {
                    for (var i = 0; i < data.floors.length; i++) {
                        if (data.floors[i].floor_code === selectedFloorCode) {
                            selectedFloorIndex = i;
                            break;
                        }
                    }
                }
            }

            tree.suspendEvents();
            selModel.suspendEvents(); // Prevent feedback loop that reloads store

            buildingNode.removeAll();
            var newNodes = [];
            Ext.Array.each(data.floors, function (floor) {
                newNodes.push(buildingNode.appendChild({
                    text: floor.floor_name || 'Unnamed Floor',
                    leaf: true,
                    floorData: floor
                }));
            });

            if (isFloorSelected && selectedFloorIndex !== -1 && newNodes[selectedFloorIndex]) {
                selModel.select(newNodes[selectedFloorIndex]);
            } else if (selectedNode === buildingNode) {
                selModel.select(buildingNode);
            }

            selModel.resumeEvents();
            tree.resumeEvents();
            me.updateProjectSummary();
            me.notifyBOQSync();
        };

        me.items = [
            {
                // Left Side: Building Navigator
                xtype: 'panel',
                region: 'west',
                title: 'Building Navigator',
                width: 250,
                split: true,
                collapsible: true,
                layout: 'fit',
                tbar: [
                    {
                        text: 'Add',
                        iconCls: 'x-fa fa-plus',
                        style: 'color: green;',
                        handler: function () {
                            var formWindow = me.up('window');
                            var headerTab = formWindow.down('quotations-tab-header');
                            var projectNameField = headerTab.getForm().findField('project_name');
                            var projectName = projectNameField ? projectNameField.getValue() : '';

                            if (Ext.isEmpty(projectName)) {
                                Ext.Msg.alert('Required', 'Please enter a Project Name in the Header tab first before adding a building.');
                                return;
                            }

                            var tree = me.down('treepanel');
                            var root = tree.getStore().getRoot();

                            // Generate unique building name
                            var maxNum = 0;
                            var existingNames = [];
                            root.cascadeBy(function (node) {
                                if (node.getDepth() === 1) {
                                    var text = node.get('text');
                                    existingNames.push(text);
                                    var match = text.match(/(\d+)$/);
                                    if (match) {
                                        var num = parseInt(match[1], 10);
                                        if (num > maxNum) {
                                            maxNum = num;
                                        }
                                    }
                                }
                            });

                            var nextNum = maxNum + 1;
                            var buildingName = 'New Building' + (nextNum < 10 ? '0' + nextNum : nextNum);

                            // Ensure name is truly unique by checking against all existing names
                            while (existingNames.indexOf(buildingName) !== -1) {
                                nextNum++;
                                buildingName = 'New Building' + (nextNum < 10 ? '0' + nextNum : nextNum);
                            }

                            var newBuilding = root.appendChild({
                                text: buildingName,
                                leaf: false,
                                expanded: true,
                                iconCls: 'x-fa fa-building',
                                buildingData: {
                                    buildingInfo: {
                                        building_name: buildingName,
                                        height: 0,
                                        no_of_floors: 0,
                                        no_of_basement: 0,
                                        total_gfa: 0
                                    },
                                    floors: []
                                }
                            });
                            tree.getSelectionModel().select(newBuilding);
                            me.updateProjectSummary();
                            me.notifyBOQSync();
                        }
                    },
                    {text: 'Edit', iconCls: 'x-fa fa-edit', style: 'color: orange;'},
                    {
                        text: 'Delete',
                        iconCls: 'x-fa fa-times',
                        style: 'color: red;',
                        handler: function () {
                            var tree = me.down('treepanel');
                            var selection = tree.getSelectionModel().getSelection()[0];
                            if (selection) {
                                var buildingNode = selection.isLeaf() ? selection.parentNode : selection;
                                if (buildingNode && buildingNode !== tree.getStore().getRoot()) {
                                    buildingNode.remove();
                                    me.resetBuildingDetails();
                                    me.updateProjectSummary();
                                    me.notifyBOQSync();
                                }
                            }
                        }
                    }
                ],
                items: [
                    {
                        xtype: 'treepanel',
                        store: buildingTreeStore,
                        rootVisible: true,
                        useArrows: true,
                        // cls: 'x-tree-noicon',
                        viewConfig: {
                            // Force icons to show for leaf nodes
                            getRowClass: function (record) {
                                return record.isLeaf() ? 'x-tree-node-leaf-with-icon' : '';
                            }
                        },
                        columns: [{
                            xtype: 'treecolumn',
                            text: 'Name',
                            dataIndex: 'text',
                            flex: 1,
                            renderer: function (value, metaData, record) {
                                if (record.isLeaf()) {
                                    // Manually add the icon if it's a leaf
                                    // var iconCls = record.get('iconCls') || 'x-fa fa-list';
                                    var iconCls = record.get('iconCls');
                                    return '<i class="' + iconCls + '" style="margin-right: 5px; width: 16px; text-align: center;"></i>' + value;
                                }
                                return value;
                            }
                        }],
                        listeners: {
                            selectionchange: function (selModel, selection) {
                                var node = selection[0];
                                if (node && node.getOwnerTree()) {
                                    var buildingNode = node.isLeaf() ? node.parentNode : node;
                                    // If node is root, buildingNode might not be what we want, but usually project is root
                                    if (!buildingNode || buildingNode === node.getOwnerTree().getRootNode()) {
                                        me.resetBuildingDetails();
                                        me.updateBuildingPreview();
                                        return;
                                    }

                                    var data = buildingNode.get('buildingData');
                                    var form = me.down('form');
                                    var grid = me.down('#floorDetailsGrid');

                                    if (form) {
                                        form.getForm().setValues(data.buildingInfo);
                                        // Update building name in tree as user types
                                        form.getForm().getFields().each(function (field) {
                                            field.un('change', me.onBuildingFieldChange, me);
                                            field.on('change', me.onBuildingFieldChange, me, {
                                                buildingNode: buildingNode,
                                                data: data
                                            });
                                        });
                                    }

                                    if (grid && grid.getStore()) {
                                        var store = grid.getStore();
                                        store.isLoadingData = true;
                                        store.loadData(Ext.clone(data.floors || []));
                                        store.isLoadingData = false;
                                        grid.getView().refresh();
                                    }

                                    me.updateBuildingPreview();

                                    if (node.isLeaf() && node.get('floorData')) {
                                        // Highlight the floor in the grid if needed
                                        var floorName = node.get('text');
                                        var floorRecord = grid.getStore().findRecord('floor_name', floorName);
                                        if (floorRecord) {
                                            grid.getSelectionModel().select(floorRecord);
                                        }
                                    }
                                } else {
                                    me.resetBuildingDetails();
                                    me.updateBuildingPreview();
                                }
                            }
                        }
                    }
                ],
            },
            {
                // Center: Building Information and Floor Details
                xtype: 'container',
                region: 'center',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                scrollable: true,
                items: [
                    {
                        // Building Information Panel
                        xtype: 'form',
                        title: 'Building Information',
                        bodyPadding: 10,
                        margin: '0 0 5 0',
                        layout: 'anchor',
                        defaults: {
                            anchor: '100%',
                            margin: '0 0 10 0'
                        },
                        items: [
                            {
                                xtype: 'container',
                                layout: 'column',
                                defaults: {
                                    columnWidth: 0.5,
                                    margin: '0 10 0 0',
                                    layout: 'anchor',
                                    defaults: {
                                        anchor: '100%',
                                        labelWidth: 150
                                    }
                                },
                                items: [
                                    {
                                        // Column 1
                                        xtype: 'container',
                                        items: [
                                            {
                                                xtype: 'textfield',
                                                fieldLabel: 'Building Name',
                                                name: 'building_name',
                                                value: '',
                                                afterLabelTextTpl: '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>'
                                            },
                                            {
                                                xtype: 'textfield',
                                                fieldLabel: 'Building Code',
                                                name: 'building_code',
                                                value: ''
                                            },
                                            {
                                                xtype: 'combobox',
                                                fieldLabel: 'Building Type',
                                                name: 'building_type',
                                                store: ['Commercial', 'Residential', 'Industrial'],
                                                value: ''
                                            },
                                            {
                                                xtype: 'fieldcontainer',
                                                fieldLabel: 'Height in meters (approx.)',
                                                layout: 'hbox',
                                                items: [
                                                    {xtype: 'numberfield', name: 'height', value: 0, flex: 1},
                                                    /* {xtype: 'displayfield', value: 'm', margin: '0 0 0 5'} */
                                                ]
                                            }
                                        ]
                                    },
                                    {
                                        // Column 2
                                        xtype: 'container',
                                        margin: '0 0 0 10',
                                        items: [
                                            {
                                                xtype: 'checkboxfield',
                                                fieldLabel: 'Roof Deck Included?',
                                                name: 'roof_deck_included',
                                                checked: false,
                                                boxLabel: 'Yes'
                                            },
                                            {
                                                xtype: 'textfield',
                                                fieldLabel: 'Typical Floor Range',
                                                name: 'typical_floor_range',
                                                emptyText: 'e.g. 2F - 10F',
                                                value: ''
                                            },
                                            {
                                                xtype: 'numberfield',
                                                fieldLabel: 'No. of Floors (above ground)',
                                                name: 'no_of_floors',
                                                value: 0
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                xtype: 'container',
                                layout: 'column',
                                defaults: {
                                    columnWidth: 0.5,
                                    margin: '0 10 0 0',
                                    layout: 'anchor',
                                    defaults: {
                                        anchor: '100%',
                                        labelWidth: 150
                                    }
                                },
                                items: [
                                    {
                                        xtype: 'container',
                                        items: [
                                            {
                                                xtype: 'numberfield',
                                                fieldLabel: 'No. of Basement',
                                                name: 'no_of_basement',
                                                value: 0
                                            },
                                            {
                                                xtype: 'fieldcontainer',
                                                fieldLabel: 'Total Gross Floor Area in sqm (est.)',
                                                layout: 'hbox',
                                                items: [
                                                    {xtype: 'numberfield', name: 'total_gfa', value: 0, flex: 1},
                                                    // {xtype: 'displayfield', value: 'sqm', margin: '0 0 0 5'}
                                                ]
                                            }
                                        ]
                                    },
                                    {
                                        xtype: 'container',
                                        margin: '0 0 0 10',
                                        items: [
                                            {
                                                xtype: 'tagfield',
                                                fieldLabel: 'Non-counted Floors',
                                                name: 'non_counted_floors',
                                                store: ['Roof Deck', 'Mezzanine', 'Basement'],
                                                value: [],
                                                filterPickList: true,
                                                queryMode: 'local'
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                xtype: 'textarea',
                                fieldLabel: 'Remarks',
                                name: 'remarks',
                                anchor: '100%',
                                labelWidth: 150,
                                value: '',
                                height: 60
                            }
                        ]
                    },
                    {
                        // Floor Details Grid
                        xtype: 'gridpanel',
                        itemId: 'floorDetailsGrid',
                        title: 'Floor Details',
                        flex: 1,
                        store: floorStore,
                        tbar: [
                            {
                                text: 'Add Floor',
                                iconCls: 'x-fa fa-plus',
                                style: 'color: green;',
                                handler: function () {
                                    var grid = this.up('gridpanel');
                                    var store = grid.getStore();
                                    var tree = me.down('treepanel');
                                    var selection = tree.getSelectionModel().getSelection()[0];

                                    if (!selection) {
                                        Ext.Msg.alert('Error', 'Please select a building first.');
                                        return;
                                    }

                                    var buildingNode = selection.isLeaf() ? selection.parentNode : selection;
                                    if (!buildingNode || buildingNode === tree.getStore().getRoot()) {
                                        Ext.Msg.alert('Error', 'Please select a building first.');
                                        return;
                                    }

                                    // Generate unique floor name and code
                                    var maxNum = 0;
                                    var existingNames = [];
                                    store.each(function (rec) {
                                        var name = rec.get('floor_name');
                                        existingNames.push(name.toLowerCase());

                                        var match = name.match(/(\d+)$/);
                                        if (match) {
                                            var num = parseInt(match[1], 10);
                                            if (num > maxNum) {
                                                maxNum = num;
                                            }
                                        }
                                    });

                                    var nextNum = maxNum + 1;
                                    var floorName = 'New Floor' + (nextNum < 10 ? '0' + nextNum : nextNum);
                                    var floorCode = 'F' + (nextNum < 10 ? '0' + nextNum : nextNum);

                                    // Ensure name is truly unique within this building
                                    while (existingNames.indexOf(floorName.toLowerCase()) !== -1) {
                                        nextNum++;
                                        floorName = 'New Floor' + (nextNum < 10 ? '0' + nextNum : nextNum);
                                        floorCode = 'F' + (nextNum < 10 ? '0' + nextNum : nextNum);
                                    }

                                    var newFloor = {
                                        floor_name: floorName,
                                        floor_code: floorCode,
                                        floor_type: 'Regular',
                                        level: store.getCount() + 1,
                                        height: 3.0,
                                        area: 0,
                                        typical_floor: false,
                                        include_in_project: true
                                    };
                                    store.add(newFloor);
                                    grid.getView().refresh();
                                }
                            },
                            {
                                text: 'Edit',
                                iconCls: 'x-fa fa-edit',
                                style: 'color: orange;',
                                handler: function () {
                                    var grid = this.up('gridpanel');
                                    var store = grid.getStore();
                                    var tree = me.down('treepanel');
                                    var buildingSelection = tree.getSelectionModel().getSelection()[0];

                                    if (!buildingSelection) {
                                        Ext.Msg.alert('Error', 'Please select a building first.');
                                        return;
                                    }

                                    var buildingNode = buildingSelection.isLeaf() ? buildingSelection.parentNode : buildingSelection;
                                    if (!buildingNode || buildingNode === tree.getStore().getRoot()) {
                                        Ext.Msg.alert('Error', 'Please select a building first.');
                                        return;
                                    }

                                    var selection = grid.getSelectionModel().getSelection()[0];
                                    if (selection) {
                                        grid.getPlugin('cellEditing').startEditByPosition({
                                            row: store.indexOf(selection),
                                            column: 1
                                        });
                                    }
                                }
                            },
                            {
                                text: 'Delete',
                                iconCls: 'x-fa fa-times',
                                style: 'color: red;',
                                handler: function () {
                                    var grid = this.up('gridpanel');
                                    var tree = me.down('treepanel');
                                    var buildingSelection = tree.getSelectionModel().getSelection()[0];

                                    if (!buildingSelection) {
                                        Ext.Msg.alert('Error', 'Please select a building first.');
                                        return;
                                    }

                                    var buildingNode = buildingSelection.isLeaf() ? buildingSelection.parentNode : buildingSelection;
                                    if (!buildingNode || buildingNode === tree.getStore().getRoot()) {
                                        Ext.Msg.alert('Error', 'Please select a building first.');
                                        return;
                                    }

                                    var selection = grid.getSelectionModel().getSelection()[0];
                                    if (selection) {
                                        grid.getStore().remove(selection);
                                    }
                                }
                            },
                        ],
                        columns: [
                            {text: '#', xtype: 'rownumberer', width: 40},
                            {
                                text: 'Floor Name',
                                dataIndex: 'floor_name',
                                flex: 2,
                                editor: {xtype: 'textfield', allowBlank: false}
                            },
                            {
                                text: 'Floor Code',
                                dataIndex: 'floor_code',
                                flex: 1,
                                editor: {xtype: 'textfield', allowBlank: false}
                            },
                            {
                                text: 'Floor Type',
                                dataIndex: 'floor_type',
                                flex: 1.5,
                                editor: {
                                    xtype: 'combobox',
                                    store: ['Regular', 'Basement', 'Mezzanine', 'Roof Deck', 'Penthouse']
                                }
                            },
                            {text: 'Level', dataIndex: 'level', flex: 1, align: 'center', editor: 'numberfield'},
                            {
                                text: 'Height (m)',
                                dataIndex: 'height',
                                flex: 1,
                                align: 'right',
                                renderer: Ext.util.Format.numberRenderer('0.00'),
                                editor: {xtype: 'numberfield', decimalPrecision: 2}
                            },
                            {
                                text: 'Area (sqm)',
                                dataIndex: 'area',
                                flex: 1,
                                align: 'right',
                                renderer: Ext.util.Format.numberRenderer('0.00'),
                                editor: {xtype: 'numberfield', decimalPrecision: 2}
                            },
                            {
                                text: 'Include in Project',
                                dataIndex: 'include_in_project',
                                xtype: 'checkcolumn',
                                flex: 1
                            }
                        ],
                        plugins: {
                            ptype: 'cellediting',
                            pluginId: 'cellEditing',
                            clicksToEdit: 2,
                            listeners: {
                                validateedit: function (editor, context) {
                                    // Validation logic removed
                                }
                            }
                        },
                        bbar: {
                            xtype: 'pagingtoolbar',
                            displayInfo: true,
                            displayMsg: 'Displaying {0} - {1} of {2}',
                            store: floorStore
                        }
                    }
                ]
            }
        ];

        me.callParent(arguments);
    }
});
