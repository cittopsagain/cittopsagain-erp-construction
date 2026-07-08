<script type="text/javascript">
    Ext.define('App.view.departments.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.departments-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'name', 'parent_id', 'parent_name'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Departments/Main/data',
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
                    var combo = editor.editor.down('combo[name=parent_id]');
                    combo.getStore().load();
                },
                edit: function (editor, context) {
                    var record = context.record;
                    Ext.Ajax.request({
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Departments/Main/save',
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
            {text: 'ID', dataIndex: 'id', width: 50},
            {
                text: 'Department Name',
                dataIndex: 'name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Parent Department',
                dataIndex: 'parent_id',
                flex: 1,
                renderer: function (value, metaData, record) {
                    return record.get('parent_name') || 'None';
                },
                editor: {
                    xtype: 'combo',
                    name: 'parent_id',
                    store: {
                        fields: ['id', 'name'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Departments/Main/all',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        }
                    },
                    displayField: 'name',
                    valueField: 'id',
                    queryMode: 'local',
                    editable: true,
                    forceSelection: false,
                    allowBlank: true,
                    emptyText: 'None',
                    listeners: {
                        blur: function (combo) {
                            var value = combo.getValue();
                            var record = combo.findRecordByDisplay(value);
                            if (!record && value) {
                                // If no record found, it means it's a new name
                                // We keep the typed value
                            }
                        }
                    }
                }
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying departments {0} - {1} of {2}',
            emptyMsg: "No departments to display"
        },
        tbar: [
            {
                text: 'Add Department',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        name: 'New Department',
                        parent_id: null
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Department',
                itemId: 'removeDept',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this department?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Departments/Main/delete',
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
                                            Ext.Msg.alert('Error', 'Failed to delete department.');
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
                this.down('#removeDept').setDisabled(!records.length);
            }
        }
    });
</script>
