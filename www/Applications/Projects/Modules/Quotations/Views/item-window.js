/**
 * Item Selection Window
 */
Ext.define('App.view.quotations.ItemWindow', {
    extend: 'Ext.window.Window',
    alias: 'widget.quotations-itemwindow',
    title: 'Select Item',
    width: 700,
    height: 400,
    layout: 'fit',
    modal: true,
    callback: null,

    initComponent: function () {
        var me = this;

        var itemStore = Ext.create('Ext.data.Store', {
            fields: [
                'item_id', 'item_code', 'item_desc', 'unit', 'unit_code', 'unit_description',
                {
                    name: 'price',
                    convert: function (value, record) {
                        // Handle mapping from different sources
                        // Inventory items use default_purchase_cost
                        // Project component items use price
                        return record.get('price') || record.get('default_purchase_cost') || 0;
                    }
                },
                {
                    name: 'item_desc',
                    convert: function (value, record) {
                        // Project component items use description, Inventory items use item_desc
                        return record.get('item_desc') || record.get('description') || '';
                    }
                }
            ],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/Items/Main/data',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total'
                }
            },
            autoLoad: false
        });

        me.items = {
            xtype: 'grid',
            store: itemStore,
            columns: [{
                text: 'Code',
                dataIndex: 'item_code',
                width: 150
            },
                {
                    text: 'Description',
                    dataIndex: 'item_desc',
                    flex: 1
                },
                {
                    text: 'Unit',
                    dataIndex: 'unit_description',
                    width: 100,
                    renderer: function (value, metaData, record) {
                        return value || record.get('unit');
                    }
                },
                {
                    text: 'Price',
                    dataIndex: 'price',
                    width: 100,
                    formatter: 'number("0,000.00")'
                }
            ],
            tbar: [
                {
                    xtype: 'textfield',
                    fieldLabel: 'Search',
                    labelWidth: 50,
                    width: 250,
                    enableKeyEvents: true,
                    listeners: {
                        keyup: function (field, e) {
                            if (e.getKey() === e.ENTER) {
                                var grid = field.up('grid');
                                grid.getStore().getProxy().setExtraParam('query', field.getValue());
                                grid.getStore().loadPage(1);
                            }
                        }
                    }
                },
                {
                    text: 'Search',
                    iconCls: 'x-fa fa-search',
                    handler: function () {
                        var grid = this.up('grid');
                        var searchField = grid.down('textfield[fieldLabel=Search]');
                        grid.getStore().getProxy().setExtraParam('query', searchField.getValue());
                        grid.getStore().loadPage(1);
                    }
                },
                {
                    text: 'Clear',
                    handler: function () {
                        var grid = this.up('grid');
                        var searchField = grid.down('textfield[fieldLabel=Search]');
                        searchField.setValue('');
                        grid.getStore().getProxy().setExtraParam('query', '');
                        grid.getStore().loadPage(1);
                    }
                }
            ],
            bbar: {
                xtype: 'pagingtoolbar',
                displayInfo: true
            },
            listeners: {
                itemdblclick: function (grid, record) {
                    me.doSelect(record);
                }
            }
        };

        me.buttons = [{
            text: 'Select',
            handler: function () {
                var grid = me.down('grid');
                var selection = grid.getSelectionModel().getSelection();
                if (selection.length > 0) {
                    me.doSelect(selection[0]);
                }
            }
        },
            {
                text: 'Cancel',
                handler: function () {
                    me.close();
                }
            }
        ];

        me.callParent(arguments);

        if (me.component_id) {
            // Check if project component items exist
            Ext.Ajax.request({
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Projects/ProjectComponents/Main/itemsData',
                method: 'GET',
                params: {
                    component_id: me.component_id,
                    limit: 1
                },
                success: function (response) {
                    var result = Ext.decode(response.responseText);
                    if (result.total > 0) {
                        // Use Project Component Items
                        itemStore.getProxy().setUrl('/Projects/ProjectComponents/Main/itemsData');
                        itemStore.getProxy().setExtraParam('component_id', me.component_id);
                        me.setTitle('Select Item (Project Component: ' + (me.component_description || me.component_code || me.component_id) + ')');
                    } else {
                        // Fallback to Inventory Items
                        itemStore.getProxy().setUrl('/Inventory/Items/Main/data');
                    }
                    itemStore.loadPage(1);
                },
                failure: function () {
                    // Fallback to Inventory Items on error
                    itemStore.getProxy().setUrl('/Inventory/Items/Main/data');
                    itemStore.loadPage(1);
                }
            });
        } else {
            // No component selected, use Inventory Items
            itemStore.loadPage(1);
        }
    },

    doSelect: function (record) {
        if (this.callback) {
            this.callback(record.getData());
        }
        this.close();
    }
});
