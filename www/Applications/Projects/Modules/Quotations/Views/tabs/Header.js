/**
 * Quotation Header Information Tab
 * This tab handles the general information of the quotation such as client, project name, and terms.
 */
Ext.define('App.view.quotations.tabs.Header', {
    extend: 'Ext.form.Panel',
    alias: 'widget.quotations-tab-header',
    title: 'Header',
    layout: 'anchor',
    bodyPadding: 10,
    autoScroll: true,
    defaults: {
        anchor: '100%',
        margin: '5',
        xtype: 'textfield',
        labelWidth: 120
    },
    items: [
        {
            xtype: 'container',
            layout: 'column',
            anchor: '100%',
            defaults: {
                columnWidth: 0.5,
                xtype: 'textfield',
                labelWidth: 120,
                margin: '5 5 5 5'
            },
            items: [
                {
                    fieldLabel: 'Quot Ctrl No',
                    name: 'quot_ctrl_no',
                    readOnly: true,
                    emptyText: 'Auto generated'
                },
                {
                    fieldLabel: 'Work Ctrl No',
                    name: 'work_ctrl_no',
                    readOnly: true,
                    emptyText: 'Auto generated',
                    margin: '5 5 5 10'
                },
                {
                    xtype: 'combobox',
                    fieldLabel: 'Client',
                    name: 'client_code',
                    store: {
                        fields: ['client_code', 'client_name'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, "/"); ?>/Sales/Clients/Main/all',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: true
                    },
                    valueField: 'client_code',
                    displayField: 'client_name',
                    queryMode: 'local',
                    matchFieldWidth: false,
                    listConfig: {
                        minWidth: 300
                    },
                    allowBlank: false
                },
                {
                    xtype: 'combobox',
                    fieldLabel: 'Service',
                    name: 'service_code',
                    store: {
                        fields: ['service_code', 'description'],
                        proxy: {
                            type: 'ajax',
                            url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/Services/Main/all',
                            reader: {
                                type: 'json',
                                rootProperty: 'data'
                            }
                        },
                        autoLoad: true
                    },
                    valueField: 'service_code',
                    displayField: 'description',
                    queryMode: 'local',
                    matchFieldWidth: false,
                    listConfig: {
                        minWidth: 300
                    },
                    allowBlank: false,
                    margin: '5 5 5 10'
                },
                {
                    fieldLabel: 'Project Name',
                    name: 'project_name',
                    allowBlank: false,
                    listeners: {
                        change: function (field, newValue) {
                            var formWindow = field.up('window');
                            if (formWindow) {
                                var buildingsTab = formWindow.down('quotations-tab-buildings');
                                if (buildingsTab && buildingsTab.updateRootName) {
                                    buildingsTab.updateRootName(newValue);
                                }
                            }
                        }
                    }
                },
                {
                    fieldLabel: 'Contact Person',
                    name: 'contact_person',
                    margin: '5 5 5 10'
                },
                {
                    fieldLabel: 'Terms',
                    name: 'terms'
                },
                {
                    fieldLabel: 'Term Remarks',
                    name: 'term_remarks',
                    margin: '5 5 5 10'
                },
                {
                    fieldLabel: 'Discount',
                    name: 'discount',
                    xtype: 'numberfield',
                    value: 0.00,
                    allowBlank: false
                },
                {
                    fieldLabel: 'Markup %',
                    name: 'markup_percent',
                    xtype: 'numberfield',
                    value: 25,
                    minValue: 0,
                    allowBlank: false,
                    margin: '5 5 5 10'
                }
            ]
        },
        {
            fieldLabel: 'Remarks',
            name: 'remarks',
            xtype: 'textarea',
            anchor: '100%',
            margin: '5 10 5 10'
        },
        /*
        {
            xtype: 'component',
            html: '<div style="font-size: 12px; color: #31708f; background-color: #d9edf7; border: 1px solid #bce8f1; padding: 15px; border-radius: 4px; margin-top: 10px;">' +
                '<div style="margin-bottom: 8px;">' +
                '<i class="fa fa-info-circle" style="font-size: 16px; margin-right: 8px; vertical-align: middle;"></i>' +
                '<span style="font-weight: bold; font-size: 13px;">Important Note:</span>' +
                '</div>' +
                '<div style="padding-left: 24px;">' +
                'If the validity section is left blank in <b>Terms & Conditions</b>, the system will use the default quotation validity of <b>15 days</b> upon receipt of quotation.<br><br>' +
                'When adding validity, use the standard format:<br>' +
                '<span style="display: inline-block; background: #fff; padding: 2px 8px; border-radius: 3px; border: 1px solid #bce8f1; margin-top: 5px; font-family: monospace;">30 days upon receipt of quotation</span><br><br>' +
                '<span style="font-style: italic; font-size: 11px;">* The number of days is variable and may change depending on the project requirements.</span>' +
                '</div>' +
                '</div>',
            margin: '5 10 5 10'
        }
        */
    ]
});
