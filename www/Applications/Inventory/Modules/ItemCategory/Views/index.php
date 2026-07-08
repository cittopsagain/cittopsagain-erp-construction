<script type="text/javascript">
    Ext.define('App.view.itemcategory.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.item-category-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['item_cat_id', 'item_cat_code', 'item_cat_name', 'date_created'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/ItemCategory/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/ItemCategory/Main/save',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                Ext.Msg.alert('Success', result.message);
                                context.grid.getStore().load();
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
            {text: 'ID', dataIndex: 'item_cat_id', width: 100},
            {
                text: 'Category Code',
                dataIndex: 'item_cat_code',
                width: 150,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Category Name',
                dataIndex: 'item_cat_name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Date Created',
                dataIndex: 'date_created',
                width: 150
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying categories {0} - {1} of {2}',
            emptyMsg: "No categories to display"
        },
        tbar: [
            {
                text: 'Add Category',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        item_cat_code: '',
                        item_cat_name: ''
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Category',
                itemId: 'removeCategory',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this category?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Inventory/ItemCategory/Main/delete',
                                        method: 'POST',
                                        params: {item_cat_id: record.get('item_cat_id')},
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
                                            Ext.Msg.alert('Error', 'Failed to delete category.');
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
                this.down('#removeCategory').setDisabled(!records.length);
            }
        }
    });
</script>
