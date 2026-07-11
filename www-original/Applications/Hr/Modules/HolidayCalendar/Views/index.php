<script type="text/javascript">
    Ext.define('App.view.holidays.Grid', {
        extend: 'Ext.grid.Panel',
        alias: 'widget.holidays-grid',
        title: '<?php echo $this->escape($title); ?>',
        height: 700,
        frame: true,
        draggable: true,
        resizable: true,
        store: {
            fields: ['id', 'holiday_date', 'description', 'type'],
            pageSize: 25,
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/HolidayCalendar/Main/data',
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
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/HolidayCalendar/Main/save',
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
                            console.error('Save Failure:', response);
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
                text: 'Date',
                dataIndex: 'holiday_date',
                flex: 1,
                xtype: 'datecolumn',
                format: 'Y-m-d',
                editor: {
                    xtype: 'datefield',
                    allowBlank: false,
                    format: 'Y-m-d'
                }
            },
            {
                text: 'Description',
                dataIndex: 'description',
                flex: 2,
                editor: {
                    xtype: 'textfield',
                    allowBlank: false
                }
            },
            {
                text: 'Type',
                dataIndex: 'type',
                flex: 1,
                editor: {
                    xtype: 'combo',
                    store: ['Regular', 'Special Non-Working', 'Special Working'],
                    editable: false,
                    allowBlank: false
                }
            }
        ],
        bbar: {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            displayMsg: 'Displaying holidays {0} - {1} of {2}',
            emptyMsg: "No holidays to display"
        },
        tbar: [
            {
                text: 'Add Holiday',
                handler: function () {
                    var grid = this.up('grid');
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();

                    editing.cancelEdit();

                    var r = Ext.create(store.getModel(), {
                        holiday_date: Ext.Date.format(new Date(), 'Y-m-d'),
                        description: 'New Holiday',
                        type: 'Regular'
                    });

                    store.insert(0, r);
                    editing.startEdit(0, 0);
                }
            },
            {
                text: 'Remove Holiday',
                itemId: 'removeHoliday',
                handler: function () {
                    var grid = this.up('grid');
                    var sm = grid.getSelectionModel();
                    var editing = grid.findPlugin('rowediting');
                    var store = grid.getStore();
                    var selection = sm.getSelection();

                    if (selection.length > 0) {
                        var record = selection[0];
                        Ext.Msg.confirm('Delete', 'Are you sure you want to delete this holiday?', function (choice) {
                            if (choice === 'yes') {
                                editing.cancelEdit();
                                if (record.phantom) {
                                    store.remove(record);
                                } else {
                                    Ext.Ajax.request({
                                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Hr/HolidayCalendar/Main/delete',
                                        method: 'POST',
                                        params: {id: record.get('id')},
                                        success: function (response) {
                                            var result = Ext.decode(response.responseText);
                                            if (result.success) {
                                                Ext.Msg.alert('Success', result.message);
                                                store.load();
                                            } else {
                                                Ext.Msg.alert('Failed', 'Server Error: ' + result.message);
                                            }
                                        },
                                        failure: function (response) {
                                            var msg = 'Status: ' + response.status + ': ' + response.statusText;
                                            Ext.Msg.alert('Error', 'Failed to delete holiday.<br>' + msg);
                                            console.error('Delete Failure:', response);
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
                this.down('#removeHoliday').setDisabled(!records.length);
            }
        }
    });
</script>
