Ext.define('App.view.costestimate.tabs.Header', {
    extend: 'Ext.form.Panel',
    alias: 'widget.cost-estimate-tab-header',
    title: 'Header',
    bodyPadding: 20,
    scrollable: true,
    defaults: {
        anchor: '100%',
        labelWidth: 120,
        margin: '0 0 10 0'
    },
    items: [
        {
            xtype: 'hiddenfield',
            name: 'id'
        },
        {
            xtype: 'hiddenfield',
            name: 'source_boq_id'
        },
        {
            xtype: 'hiddenfield',
            name: 'source_boq_revision_id'
        },
        {
            xtype: 'textfield',
            name: 'source_boq_no',
            fieldLabel: 'Source BOQ',
            readOnly: false,
            editable: false,
            emptyText: 'Select BOQ...',
            allowBlank: false,
            triggers: {
                search: {
                    cls: 'x-form-search-trigger',
                    weight: 1,
                    handler: function () {
                        var main = this.up('cost-estimate-main');
                        if (main) {
                            main.showBoqSelector();
                        }
                    }
                }
            }
        },
        {
            xtype: 'textfield',
            name: 'boq_revision',
            fieldLabel: 'BOQ Revision',
            readOnly: true
        },
        {
            xtype: 'textfield',
            name: 'estimate_no',
            fieldLabel: 'Estimate No.',
            readOnly: true,
            emptyText: '[Auto generated]'
        },
        {
            xtype: 'textfield',
            name: 'estimate_name',
            fieldLabel: 'Estimate Name',
            allowBlank: false
        },
        {
            xtype: 'textfield',
            name: 'project_name',
            fieldLabel: 'Project Name',
            allowBlank: false
        },
        {
            xtype: 'textfield',
            name: 'client_code',
            fieldLabel: 'Client Code'
        },
        {
            xtype: 'combobox',
            name: 'estimate_type_id',
            fieldLabel: 'Estimate Type',
            store: Ext.create('Ext.data.Store', {
                fields: ['id', 'estimate_type'],
                proxy: {
                    type: 'ajax',
                    url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/EstimateTypes/Main/all',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                autoLoad: true
            }),
            displayField: 'estimate_type',
            valueField: 'id',
            queryMode: 'local'
        },
        {
            xtype: 'textfield',
            name: 'revision',
            fieldLabel: 'Revision'
        },
        {
            xtype: 'combobox',
            name: 'currency',
            fieldLabel: 'Currency',
            store: ['PHP', 'USD', 'EUR']
        },
        {
            xtype: 'datefield',
            name: 'costing_date',
            fieldLabel: 'Costing Date',
            format: 'Y-m-d'
        },
        {
            xtype: 'combobox',
            name: 'status',
            fieldLabel: 'Status',
            store: ['Draft', 'Approved', 'Cancelled'],
            value: 'Draft'
        },
        {
            xtype: 'textarea',
            name: 'remarks',
            fieldLabel: 'Remarks'
        }
    ]
});
