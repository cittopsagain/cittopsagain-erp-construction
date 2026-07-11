<script type="text/javascript">
    Ext.define('App.view.jobposition.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.job-position-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['pos_id', 'pos_code', 'pos_name', 'pos_desc', 'dept_id', 'dept_name', 'parent_id', 'reports_to_name', 'salary_grade', 'status'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/JobPositions/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/JobPositions/Main/save',
                        method: 'POST',
                        params: record.getData(),
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success) {
                                Ext.Msg.alert('Success', result.message);
                                context.grid.getStore().load();
                            } else {
                                Ext.Msg.alert('Failed', 'Server Error: ' + result.message);
                                context.grid.getStore().load();
                            }
                        },
                        failure: function (response) {
                            var msg = 'Status: ' + response.status + ': ' + response.statusText;
                            Ext.Msg.alert('Error', 'Failed to save changes.<br>' + msg);
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
            {text: 'ID', dataIndex: 'pos_id', width: 50},
            {
                text: 'Code',
                dataIndex: 'pos_code',
                width: 100,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Position Name',
                dataIndex: 'pos_name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Department',
                dataIndex: 'dept_id',
                flex: 1,
                renderer: function (value, metaData, record) {
                    return record.get('dept_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        fields: ['id', 'name'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/JobPositions/Main/departments',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: true
                    },
                    displayField: 'name',
                    valueField: 'id',
                    editable: false,
                    allowBlank: true
                }
            },
            {
                text: 'Reports To',
                dataIndex: 'parent_id',
                flex: 1,
                renderer: function (value, metaData, record) {
                    return record.get('reports_to_name');
                },
                editor: {
                    xtype: 'combo',
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
                    valueField: 'pos_id',
                    editable: false,
                    allowBlank: true
                }
            },
            {
                text: 'Salary Grade',
                dataIndex: 'salary_grade',
                width: 100,
                editor: {
                    xtype: 'textfield'
                }
            },
            {
                text: 'Status',
                dataIndex: 'status',
                width: 100,
                editor: {
                    xtype: 'combo',
                    store: ['Active', 'Inactive'],
                    editable: false,
                    allowBlank: false
                }
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying job positions {0} - {1} of {2}',
            emptyMsg: "No job positions to display"
        },
        tbar: [
            {
                text: 'Add Job Position',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        pos_code: '',
                        pos_name: '',
                        pos_desc: '',
                        status: 'Active'
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Job Position',
                itemId: 'removeJobPosition',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this job position?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/JobPositions/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('pos_id')},
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
                                            Ext.Msg.alert('Error', 'Failed to delete job position.');
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
                this.down('#removeJobPosition').setDisabled(!records.length);
            }
        }
    });
</script>
