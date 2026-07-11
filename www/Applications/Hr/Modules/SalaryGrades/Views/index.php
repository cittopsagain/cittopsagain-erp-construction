<script type="text/javascript">
    Ext.define('App.view.salarygrades.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.salary-grade-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'grade_code', 'grade_name', 'min_salary', 'max_salary', 'description', 'status'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/SalaryGrades/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/SalaryGrades/Main/save',
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
                text: 'Grade Code',
                dataIndex: 'grade_code',
                width: 100,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Grade Name',
                dataIndex: 'grade_name',
                flex: 1,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Min Salary',
                dataIndex: 'min_salary',
                width: 120,
                renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    allowBlank: false
                }
            },
            {
                text: 'Max Salary',
                dataIndex: 'max_salary',
                width: 120,
                renderer: Ext.util.Format.numberRenderer('0,000.00'),
                editor: {
                    xtype: 'numberfield',
                    minValue: 0,
                    allowBlank: false
                }
            },
            {
                text: 'Description',
                dataIndex: 'description',
                width: 200,
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
            displayMsg: 'Displaying salary grades {0} - {1} of {2}',
            emptyMsg: "No salary grades to display"
        },
        tbar: [
            {
                text: 'Add Salary Grade',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        grade_code: '',
                        grade_name: '',
                        min_salary: 0,
                        max_salary: 0,
                        description: '',
                        status: 'Active'
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Salary Grade',
                itemId: 'removeSalaryGrade',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this salary grade?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/SalaryGrades/Main/delete',
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
                                            Ext.Msg.alert('Error', 'Failed to delete salary grade.');
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
                this.down('#removeSalaryGrade').setDisabled(!records.length);
            }
        }
    });
</script>
