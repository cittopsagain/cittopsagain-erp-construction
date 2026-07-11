/**
 * Main Quotations Grid View
 *
 * This component displays the list of quotations in a grid format.
 * It provides functionality for:
 * - Paginated loading of quotations.
 * - Searching by Quotation Number or Client Name.
 * - Opening the Add/Edit form.
 * - Deleting existing quotations.
 */
Ext.define('App.view.quotations.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.quotations-grid',
    title: '<?php echo $this->escape($title); ?>',
    height: 700,
    frame: true,
    draggable: true,

    // Store configuration for fetching quotation list
    store: {
        fields: ['id', 'service_desc', 'project_name', 'quot_ctrl_no', 'client_code', 'client_name', 'contact_person', 'terms', 'term_remarks', 'discount', 'remarks', 'status', 'date_created'],
        pageSize: 25,
        proxy: {
            type: 'ajax',
            url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/Quotations/Main/data',
            reader: {
                type: 'json',
                rootProperty: 'data',
                totalProperty: 'total'
            }
        },
        autoLoad: true
    },

    // Grid column definitions
    columns: [{
        text: 'ID',
        dataIndex: 'id',
        width: 50
    },
        {
            text: 'Service',
            dataIndex: 'service_desc',
            width: 120
        },
        {
            text: 'Project Name',
            dataIndex: 'project_name',
            width: 150
        },
        {
            text: 'Quot Ctrl No',
            dataIndex: 'quot_ctrl_no',
            width: 120
        },
        {
            text: 'Client',
            dataIndex: 'client_name',
            flex: 1
        },
        {
            text: 'Contact Person',
            dataIndex: 'contact_person',
            width: 150
        },
        {
            text: 'Status',
            dataIndex: 'status',
            width: 100,
            renderer: function (value) {
                var color = value === 'DRAFT' ? 'gray' : 'green';
                return '<span style="color:' + color + ';font-weight:bold;">' + value + '</span>';
            }
        },
        {
            text: 'Date',
            dataIndex: 'date_created',
            width: 150
        }
    ],

    // Paging toolbar at the bottom
    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true
    },

    // Action buttons at the top
    tbar: [{
        text: 'Add Quotation',
        handler: function () {
            var grid = this.up('grid');
            grid.showForm();
        }
    },
        {
            text: 'Edit Quotation',
            itemId: 'editBtn',
            disabled: true,
            handler: function () {
                var grid = this.up('grid');
                var selection = grid.getSelectionModel().getSelection();
                if (selection.length > 0) {
                    grid.showForm(selection[0]);
                }
            }
        },
        {
            text: 'Delete Quotation',
            itemId: 'deleteBtn',
            disabled: true,
            handler: function () {
                var grid = this.up('grid');
                var selection = grid.getSelectionModel().getSelection();
                if (selection.length > 0) {
                    Ext.Msg.confirm('Delete', 'Are you sure you want to delete this quotation?', function (choice) {
                        if (choice === 'yes') {
                            Ext.Ajax.request({
                                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/Quotations/Main/delete',
                                method: 'POST',
                                params: {
                                    id: selection[0].get('quot_ctrl_no')
                                },
                                success: function (response) {
                                    var result = Ext.decode(response.responseText);
                                    if (result.success) {
                                        Ext.Msg.alert('Success', result.message);
                                        grid.getStore().load();
                                    } else {
                                        Ext.Msg.alert('Failed', result.message);
                                    }
                                }
                            });
                        }
                    });
                }
            }
        },
        {
            text: 'Print',
            itemId: 'printBtn',
            disabled: true,
            handler: function () {
                var grid = this.up('grid');
                var selection = grid.getSelectionModel().getSelection();
                if (selection.length > 0) {
                    window.open('/Projects/Quotations/Export/pdf?id=' + selection[0].get('id'), '_blank');
                }
            }
        },
        '->',
        {
            xtype: 'textfield',
            emptyText: 'Search...',
            enableKeyEvents: true,
            listeners: {
                keyup: function (field, e) {
                    if (e.getKey() === e.ENTER) {
                        var grid = field.up('grid');
                        var value = field.getValue();
                        var store = grid.getStore();
                        store.getProxy().setExtraParam('query', value);
                        store.loadPage(1);
                    }
                }
            }
        },
        {
            text: 'Search',
            handler: function () {
                var grid = this.up('grid');
                var field = grid.down('textfield[emptyText=Search...]');
                var value = field.getValue();
                var store = grid.getStore();
                store.getProxy().setExtraParam('query', value);
                store.loadPage(1);
            }
        }
    ],

    listeners: {
        // Enable/disable buttons based on selection
        selectionchange: function (model, records) {
            this.down('#editBtn').setDisabled(!records.length);
            this.down('#printBtn').setDisabled(!records.length);
            this.down('#deleteBtn').setDisabled(!records.length);
        },
        // Open edit form on double click
        itemdblclick: function (grid, record) {
            this.showForm(record);
        }
    },

    /**
     * Show the Add/Edit Quotation Form Window
     * @param {Ext.data.Model} record (Optional) The record to edit
     */
    showForm: function (record) {
        var win = Ext.create('App.view.quotations.Form', {
            record: record,
            grid: this
        });
        win.show();
    }
});