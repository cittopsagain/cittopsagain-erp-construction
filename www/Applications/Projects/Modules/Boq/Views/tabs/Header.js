Ext.define('App.view.boq.Header', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.boq-header',
    title: 'Header',
    bodyPadding: 10,
    layout: 'anchor',
    defaults: {
        anchor: '100%',
        labelWidth: 150
    },
    initComponent: function () {
        var me = this;
        var mainView = me.up('boq-main');

        me.items = [
            {xtype: 'hiddenfield', name: 'id'},
            {
                xtype: 'textfield',
                fieldLabel: 'BOQ No.',
                name: 'boq_no',
                emptyText: 'Auto-generated if blank'
            },
            {
                xtype: 'textfield',
                fieldLabel: 'Project',
                name: 'project_name',
                allowBlank: false
            },
            {
                xtype: 'combobox',
                fieldLabel: 'Client',
                name: 'client_code',
                store: {
                    fields: ['client_code', 'client_name', 'add1'],
                    data: mainView.clients || []
                },
                displayField: 'client_name',
                valueField: 'client_code',
                queryMode: 'local',
                forceSelection: true,
                allowBlank: false,
                listeners: {
                    select: function (combo, record) {
                        var locationField = combo.up('form').down('[name=location]');
                        if (locationField && record) {
                            locationField.setValue(record.get('add1'));
                        }
                    }
                }
            },
            {
                xtype: 'textfield',
                fieldLabel: 'Address',
                name: 'location'
            },
            {
                xtype: 'fieldcontainer',
                layout: 'hbox',
                defaults: {
                    flex: 1
                },
                items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Revision',
                        name: 'revision',
                        value: 'Rev. 0',
                        labelWidth: 150,
                        allowBlank: false
                    },
                    {
                        xtype: 'combobox',
                        fieldLabel: 'Status',
                        name: 'status',
                        store: ['Draft', 'For Approval', 'Approved', 'Cancelled'],
                        value: 'Draft',
                        labelWidth: 100,
                        margin: '0 0 0 10',
                        allowBlank: false
                    }
                ]
            },
            {
                xtype: 'textarea',
                fieldLabel: 'Remarks',
                name: 'remarks',
                height: 60
            }
        ];

        me.callParent(arguments);
    }
});
