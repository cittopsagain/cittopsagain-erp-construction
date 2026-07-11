<script type="text/javascript">
    Ext.define('App.view.employeemasterlist.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.employeemasterlist-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'employee_no', 'employee_name', 'position_id', 'position_name', 'department_id', 'department_name', 'branch_id', 'branch_name', 'employment_type_id', 'employment_type_name', 'work_schedule_id', 'work_schedule_name', 'date_hired', 'supervisor_id', 'supervisor_name', 'status'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/save',
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
            {text: 'ID', dataIndex: 'id', width: 50},
            {
                text: 'Employee No.',
                dataIndex: 'employee_no',
                width: 120,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Employee Name',
                dataIndex: 'employee_name',
                width: 200,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Position',
                dataIndex: 'position_id',
                width: 150,
                renderer: function (val, meta, rec) {
                    return rec.get('position_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/lookup_positions',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'pos_name',
                    valueField: 'pos_id',
                    editable: true,
                    forceSelection: true,
                    queryMode: 'local'
                }
            },
            {
                text: 'Department',
                dataIndex: 'department_id',
                width: 150,
                renderer: function (val, meta, rec) {
                    return rec.get('department_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/lookup_departments',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'name',
                    valueField: 'id',
                    editable: true,
                    forceSelection: true,
                    queryMode: 'local'
                }
            },
            {
                text: 'Branch',
                dataIndex: 'branch_id',
                width: 150,
                renderer: function (val, meta, rec) {
                    return rec.get('branch_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/lookup_branches',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'branch_name',
                    valueField: 'id',
                    editable: true,
                    forceSelection: true,
                    queryMode: 'local'
                }
            },
            {
                text: 'Employment Type',
                dataIndex: 'employment_type_id',
                width: 120,
                renderer: function (val, meta, rec) {
                    return rec.get('employment_type_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/lookup_employment_types',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'employment_type',
                    valueField: 'id',
                    editable: true,
                    forceSelection: true,
                    queryMode: 'local'
                }
            },
            {
                text: 'Work Schedule',
                dataIndex: 'work_schedule_id',
                width: 150,
                renderer: function (val, meta, rec) {
                    return rec.get('work_schedule_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/lookup_work_schedules',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'schedule_name',
                    valueField: 'id',
                    editable: true,
                    forceSelection: true,
                    queryMode: 'local'
                }
            },
            {
                text: 'Date Hired',
                dataIndex: 'date_hired',
                width: 100,
                xtype: 'datecolumn',
                format: 'Y-m-d',
                editor: {
                    xtype: 'datefield',
                    format: 'Y-m-d'
                }
            },
            {
                text: 'Immediate Supervisor',
                dataIndex: 'supervisor_id',
                width: 150,
                renderer: function (val, meta, rec) {
                    return rec.get('supervisor_name');
                },
                editor: {
                    xtype: 'combo',
                    store: {
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/lookup_supervisors',
                            reader: {type: 'json', rootProperty: 'data'}
                        },
                        autoLoad: true
                    },
                    displayField: 'employee_name',
                    valueField: 'id',
                    editable: true,
                    forceSelection: true,
                    queryMode: 'local'
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
            displayMsg: 'Displaying employees {0} - {1} of {2}',
            emptyMsg: "No employees to display"
        },
        tbar: [
            {
                text: 'Add Employee',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        employee_no: '',
                        employee_name: '',
                        status: 'Active'
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Employee',
                itemId: 'removeEmployee',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this employee?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/EmployeeMasterlist/Main/delete',
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
                                            Ext.Msg.alert('Error', 'Failed to delete employee.');
                                        }
                                    });
                                }
                            }
                        });
                    }
                },
                disabled: true
            },
            '->',
            {
                xtype: 'textfield',
                fieldLabel: 'Search',
                labelWidth: 50,
                enableKeyEvents: true,
                listeners: {
                    keyup: function (field, e) {
                        var grid = field.up('grid');
                        var store = grid.getStore();
                        var value = field.getValue();

                        store.getProxy().setExtraParam('query', value);
                        store.loadPage(1);
                    }
                }
            }
        ],
        listeners: {
            selectionchange: function (model, records) {
                this.down('#removeEmployee').setDisabled(!records.length);
            }
        }
    });
</script>
