<script type="text/javascript">
    Ext.define('App.view.employee.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.employee-grid',
        title: 'Employee Management',
        draggable: true,
        resizable: true,
        frame: true,
        height: 700,
        store: {
            fields: ['id', 'name', 'position', 'department', 'status'],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/data',
                reader: {
                    type: 'json'
                }
            },
            autoLoad: true
        },
        columns: [
            {text: 'ID', dataIndex: 'id', width: 50},
            {text: 'Name', dataIndex: 'name', flex: 2},
            {text: 'Position', dataIndex: 'position', flex: 1},
            {text: 'Department', dataIndex: 'department', flex: 1},
            {text: 'Status', dataIndex: 'status', width: 100},
            {
                xtype: 'actioncolumn',
                width: 50,
                items: [
                    {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Delete Employee',
                        handler: function (grid, rowIndex) {
                            var record = grid.getStore().getAt(rowIndex);
                            Ext.Msg.confirm('Delete', 'Are you sure?', function (choice) {
                                if (choice === 'yes') {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('id')},
                                        success: function () {
                                            grid.getStore().load();
                                        }
                                    });
                                }
                            });
                        }
                    }
                ]
            }
        ],
        tbar: [
            {
                text: 'Add Employee',
                handler: function () {
                    var grid = this.up('grid');
                    var win = Ext.create('Ext.window.Window', {
                        title: 'Add New Employee',
                        modal: true,
                        width: 800,
                        height: 600,
                        layout: 'fit',
                        items: [{
                            xtype: 'form',
                            scrollable: true,
                            bodyPadding: 10,
                            layout: 'anchor',
                            defaults: {anchor: '100%', labelWidth: 150},
                            items: [{
                                xtype: 'tabpanel',
                                items: [
                                    {
                                        title: 'Personal Info',
                                        bodyPadding: 10,
                                        defaults: {anchor: '100%', labelWidth: 150},
                                        items: [
                                            {
                                                xtype: 'textfield',
                                                name: 'employee_id',
                                                fieldLabel: 'Employee ID',
                                                allowBlank: false
                                            },
                                            {
                                                xtype: 'textfield',
                                                name: 'first_name',
                                                fieldLabel: 'First Name',
                                                allowBlank: false
                                            },
                                            {xtype: 'textfield', name: 'middle_name', fieldLabel: 'Middle Name'},
                                            {
                                                xtype: 'textfield',
                                                name: 'last_name',
                                                fieldLabel: 'Last Name',
                                                allowBlank: false
                                            },
                                            {xtype: 'textfield', name: 'suffix', fieldLabel: 'Suffix (Jr/Sr)'},
                                            {
                                                xtype: 'combo', name: 'gender', fieldLabel: 'Gender',
                                                store: ['Male', 'Female', 'Other'], editable: false
                                            },
                                            {
                                                xtype: 'datefield',
                                                name: 'dob',
                                                fieldLabel: 'Date of Birth',
                                                format: 'Y-m-d'
                                            },
                                            {xtype: 'textfield', name: 'mobile_number', fieldLabel: 'Mobile Number'},
                                            {
                                                xtype: 'textfield',
                                                name: 'email_address',
                                                fieldLabel: 'Email Address',
                                                vtype: 'email'
                                            }
                                        ]
                                    },
                                    {
                                        title: 'Employment',
                                        bodyPadding: 10,
                                        defaults: {anchor: '100%', labelWidth: 150},
                                        items: [
                                            {
                                                xtype: 'datefield',
                                                name: 'date_hired',
                                                fieldLabel: 'Date Hired',
                                                format: 'Y-m-d',
                                                allowBlank: false
                                            },
                                            {
                                                xtype: 'combo',
                                                name: 'employment_status_id',
                                                fieldLabel: 'Employment Status',
                                                displayField: 'name',
                                                valueField: 'id',
                                                store: {
                                                    proxy: {
                                                        type: 'ajax',
                                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/statuses',
                                                        reader: {type: 'json', rootProperty: 'data'}
                                                    }, autoLoad: true
                                                }
                                            },
                                            {
                                                xtype: 'combo', name: 'department_id', fieldLabel: 'Department',
                                                displayField: 'name', valueField: 'id',
                                                store: {
                                                    proxy: {
                                                        type: 'ajax',
                                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/departments',
                                                        reader: {type: 'json', rootProperty: 'data'}
                                                    }, autoLoad: true
                                                }
                                            },
                                            {
                                                xtype: 'combo', name: 'position_id', fieldLabel: 'Position',
                                                displayField: 'name', valueField: 'id',
                                                store: {
                                                    proxy: {
                                                        type: 'ajax',
                                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/positions',
                                                        reader: {type: 'json', rootProperty: 'data'}
                                                    }, autoLoad: true
                                                }
                                            },
                                            {xtype: 'textfield', name: 'job_title', fieldLabel: 'Job Title'}
                                        ]
                                    },
                                    {
                                        title: 'Statutory & Salary',
                                        bodyPadding: 10,
                                        defaults: {anchor: '100%', labelWidth: 150},
                                        items: [
                                            {xtype: 'textfield', name: 'sss_number', fieldLabel: 'SSS Number'},
                                            {
                                                xtype: 'textfield',
                                                name: 'philhealth_number',
                                                fieldLabel: 'PhilHealth Number'
                                            },
                                            {xtype: 'textfield', name: 'pagibig_number', fieldLabel: 'Pag-IBIG Number'},
                                            {xtype: 'textfield', name: 'tin_number', fieldLabel: 'TIN'},
                                            {
                                                xtype: 'combo', name: 'salary_type_id', fieldLabel: 'Salary Type',
                                                displayField: 'name', valueField: 'id',
                                                store: {
                                                    proxy: {
                                                        type: 'ajax',
                                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/salary_types',
                                                        reader: {type: 'json', rootProperty: 'data'}
                                                    }, autoLoad: true
                                                }
                                            },
                                            {
                                                xtype: 'numberfield',
                                                name: 'basic_salary_rate',
                                                fieldLabel: 'Basic Salary',
                                                minValue: 0
                                            }
                                        ]
                                    }
                                ]
                            }],
                            buttons: [{
                                text: 'Save',
                                formBind: true,
                                handler: function () {
                                    var form = this.up('form').getForm();
                                    if (form.isValid()) {
                                        form.submit({
                                            url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/Employees/Main/save',
                                            success: function (form, action) {
                                                Ext.Msg.alert('Success', action.result.message);
                                                win.close();
                                                grid.getStore().load();
                                            },
                                            failure: function (form, action) {
                                                Ext.Msg.alert('Failed', action.result ? action.result.message : 'Error');
                                            }
                                        });
                                    }
                                }
                            }, {
                                text: 'Cancel',
                                handler: function () {
                                    win.close();
                                }
                            }]
                        }]
                    });
                    win.show();
                }
            }
        ]
    });
</script>
