<?php
$baseUrl = rtrim(BASE_URL, '/');
?>
<script type="text/javascript">
    <?php include "tabs/Header.js"; ?>
    <?php include "tabs/Lines.js"; ?>

    Ext.define('CostEstimateModel', {
        extend: 'Ext.data.Model',
        fields: [
            'id', 'estimate_no', 'estimate_name', 'project_name', 'client_code', 'status', 'created_at',
            'estimate_type_id', 'revision', 'currency', 'costing_date', 'remarks', 'source_boq_id', 'source_boq_revision_id', 'boq_no', 'boq_revision'
        ]
    });

    Ext.define('ApprovedBoqModel', {
        extend: 'Ext.data.Model',
        idProperty: 'record_id',
        fields: [
            'id', 'boq_no', 'project_name', 'client_code', 'client_name', 'revision', 'status', 'created_at', 'source', 'record_id'
        ]
    });

    Ext.define('EstimateTypeModel', {
        extend: 'Ext.data.Model',
        fields: ['id', 'estimate_type']
    });

    Ext.define('App.view.costestimate.Main', {
        extend: 'Ext.panel.Panel',
        alias: 'widget.cost-estimate-main',
        layout: 'border',
        height: 760,
        frame: true,

        baseUrl: '<?php echo $baseUrl; ?>',

        initComponent: function () {
            var me = this;

            me.items = [
                {
                    xtype: 'grid',
                    region: 'west',
                    title: 'Cost Estimate List',
                    width: 550,
                    split: true,
                    collapsible: true,
                    tbar: [
                        {
                            text: 'Create Estimate',
                            iconCls: 'x-fa fa-plus',
                            handler: function () {
                                var main = me;
                                var grid = main.down('grid');
                                grid.getSelectionModel().deselectAll();

                                var headerTab = main.down('cost-estimate-tab-header');
                                if (headerTab) {
                                    headerTab.getForm().reset();
                                    headerTab.getForm().setValues({
                                        estimate_no: 'Auto generated',
                                        status: 'Draft',
                                        revision: 'Rev. 0',
                                        currency: 'PHP',
                                        costing_date: new Date()
                                    });
                                }
                                main.down('panel[region=center]').setDisabled(false);

                                main.down('#saveBtn').setText('Save Estimate');
                                main.down('panel[region=center]').setTitle('New Cost Estimate');
                            }
                        },
                        '->',
                        {
                            xtype: 'textfield',
                            emptyText: 'Search Estimate...',
                            width: 200,
                            enableKeyEvents: true,
                            listeners: {
                                specialkey: function (f, e) {
                                    if (e.getKey() === e.ENTER) {
                                        var store = me.down('grid').getStore();
                                        store.getProxy().setExtraParam('query', f.getValue());
                                        store.loadPage(1);
                                    }
                                }
                            }
                        }
                    ],
                    store: Ext.create('Ext.data.Store', {
                        model: 'CostEstimateModel',
                        pageSize: 25,
                        proxy: {
                            type: 'ajax',
                            url: me.baseUrl + '/Projects/CostEstimate/Main/data',
                            reader: {
                                type: 'json',
                                rootProperty: 'data',
                                totalProperty: 'total'
                            }
                        },
                        autoLoad: true
                    }),
                    columns: [
                        {text: 'Estimate No.', dataIndex: 'estimate_no', flex: 1},
                        {text: 'Estimate Name', dataIndex: 'estimate_name', flex: 1},
                        {text: 'Project Name', dataIndex: 'project_name', flex: 1},
                        {text: 'Status', dataIndex: 'status', width: 80}
                    ],
                    bbar: {
                        xtype: 'pagingtoolbar',
                        displayInfo: true,
                        displayMsg: 'Displaying estimates {0} - {1} of {2}',
                        emptyMsg: "No estimates to display"
                    }
                },
                {
                    xtype: 'panel',
                    region: 'center',
                    title: 'Cost Estimate Details',
                    layout: 'fit',
                    disabled: true,
                    items: [
                        {
                            xtype: 'tabpanel',
                            items: [
                                {
                                    xtype: 'cost-estimate-tab-header'
                                },
                                {
                                    xtype: 'cost-estimate-tab-lines'
                                }
                            ],
                            buttons: [
                                {
                                    text: 'Save Estimate',
                                    itemId: 'saveBtn',
                                    handler: function () {
                                        var main = me;
                                        var headerTab = main.down('cost-estimate-tab-header');
                                        var form = headerTab.getForm();

                                        if (form.isValid()) {
                                            form.submit({
                                                url: me.baseUrl + '/Projects/CostEstimate/Main/save',
                                                waitMsg: 'Saving...',
                                                success: function (fp, o) {
                                                    Ext.Msg.alert('Success', o.result.message);
                                                    me.down('grid').getStore().reload();

                                                    // If it was a new estimate, we might want to select it
                                                    if (o.result.id) {
                                                        var estimateId = o.result.id;
                                                        var grid = me.down('grid');
                                                        var store = grid.getStore();
                                                        store.load({
                                                            callback: function (records, operation, success) {
                                                                if (success && estimateId) {
                                                                    var record = store.getById(estimateId);
                                                                    if (record) {
                                                                        grid.getSelectionModel().select(record);
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    } else {
                                                        // If it was an update, just reload the lines to be sure
                                                        var selected = me.down('grid').getSelectionModel().getSelection()[0];
                                                        if (selected) {
                                                            me.down('cost-estimate-tab-lines').getStore().load({
                                                                params: {estimate_id: selected.get('id')}
                                                            });
                                                        }
                                                    }
                                                },
                                                failure: function (fp, o) {
                                                    Ext.Msg.alert('Error', o.result.message || 'Failed to save estimate.');
                                                }
                                            });
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                }
            ];

            me.listeners = {
                render: function () {
                    var grid = me.down('grid');
                    grid.on('selectionchange', function (selModel, selected) {
                        var headerTab = me.down('cost-estimate-tab-header');
                        var linesTab = me.down('cost-estimate-tab-lines');
                        var detailsPanel = me.down('panel[region=center]');
                        if (selected.length > 0) {
                            headerTab.getForm().loadRecord(selected[0]);
                            headerTab.getForm().setValues({
                                source_boq_no: selected[0].get('boq_no') || '',
                                boq_revision: selected[0].get('boq_revision') || ''
                            });
                            detailsPanel.setDisabled(false);
                            me.down('#saveBtn').setText('Update Estimate');
                            detailsPanel.setTitle('Cost Estimate: ' + selected[0].get('estimate_no'));

                            // Load lines
                            linesTab.getStore().load({
                                params: {
                                    estimate_id: selected[0].get('id')
                                }
                            });
                        } else {
                            if (detailsPanel.getTitle() === 'New Cost Estimate') {
                                // Keep form for new estimate
                            } else {
                                headerTab.getForm().reset();
                                linesTab.getStore().removeAll();
                                detailsPanel.setDisabled(true);
                                detailsPanel.setTitle('Cost Estimate Details');
                            }
                        }
                    });
                }
            };

            me.callParent(arguments);
        },

        showBoqSelector: function () {
            var me = this;
            var boqStore = Ext.create('Ext.data.Store', {
                model: 'ApprovedBoqModel',
                pageSize: 25,
                proxy: {
                    type: 'ajax',
                    url: me.baseUrl + '/Projects/CostEstimate/Main/approved_boqs',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        totalProperty: 'total'
                    }
                },
                autoLoad: true
            });

            var win = Ext.create('Ext.window.Window', {
                title: 'Select Source BOQ',
                width: 700,
                height: 450,
                layout: 'fit',
                modal: true,
                items: [
                    {
                        xtype: 'grid',
                        store: boqStore,
                        columns: [
                            {text: 'BOQ No.', dataIndex: 'boq_no', width: 150},
                            {text: 'Project Name', dataIndex: 'project_name', flex: 1},
                            {text: 'Client Name', dataIndex: 'client_name', flex: 1},
                            {text: 'Revision', dataIndex: 'revision', width: 80}
                        ],
                        tbar: [
                            {
                                xtype: 'textfield',
                                emptyText: 'Search BOQ...',
                                width: 250,
                                enableKeyEvents: true,
                                listeners: {
                                    specialkey: function (f, e) {
                                        if (e.getKey() === e.ENTER) {
                                            var store = boqStore;
                                            store.getProxy().setExtraParam('query', f.getValue());
                                            store.loadPage(1);
                                        }
                                    }
                                }
                            }
                        ],
                        bbar: {
                            xtype: 'pagingtoolbar',
                            store: boqStore,
                            displayInfo: true
                        },
                        listeners: {
                            itemdblclick: function (view, record) {
                                me.selectBoq(record, win);
                            }
                        }
                    }
                ],
                buttons: [
                    {
                        text: 'Cancel',
                        handler: function () {
                            win.close();
                        }
                    },
                    {
                        text: 'Select',
                        handler: function () {
                            var grid = win.down('grid');
                            var selected = grid.getSelectionModel().getSelection();
                            if (selected.length > 0) {
                                me.selectBoq(selected[0], win);
                            } else {
                                Ext.Msg.alert('Selection Required', 'Please select a BOQ.');
                            }
                        }
                    }
                ]
            });
            win.show();
        },

        selectBoq: function (record, win) {
            var me = this;
            var headerTab = me.down('cost-estimate-tab-header');
            headerTab.getForm().setValues({
                source_boq_id: record.get('id'),
                source_boq_revision_id: record.get('source'),
                source_boq_no: record.get('boq_no'),
                boq_revision: record.get('revision'),
                project_name: record.get('project_name'),
                client_code: record.get('client_code'),
                estimate_name: record.get('project_name') + ' - Initial Estimate'
            });

            // Also update the title
            me.down('panel[region=center]').setTitle('New Cost Estimate for ' + record.get('project_name'));

            win.close();
        },

        showApprovedBoqs: function () {
            var me = this;
            var boqStore = Ext.create('Ext.data.Store', {
                model: 'ApprovedBoqModel',
                pageSize: 25,
                proxy: {
                    type: 'ajax',
                    url: me.baseUrl + '/Projects/CostEstimate/Main/approved_boqs',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        totalProperty: 'total'
                    }
                },
                autoLoad: true
            });

            var estimateTypeStore = Ext.create('Ext.data.Store', {
                model: 'EstimateTypeModel',
                proxy: {
                    type: 'ajax',
                    url: me.baseUrl + '/Projects/EstimateTypes/Main/all',
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                autoLoad: true
            });

            var win = Ext.create('Ext.window.Window', {
                title: '1. Select Source BOQ',
                width: 800,
                height: 550,
                layout: 'card',
                modal: true,
                resizable: false,
                items: [
                    // Step 1: Select Source BOQ
                    {
                        xtype: 'panel',
                        layout: 'vbox',
                        items: [
                            {
                                xtype: 'label',
                                text: '1. Select Source BOQ',
                                style: 'font-weight: bold; font-size: 14px; margin-bottom: 10px;',
                                hidden: true
                            },
                            {
                                xtype: 'grid',
                                store: boqStore,
                                width: '100%',
                                flex: 1,
                                tbar: [
                                    '->',
                                    {
                                        xtype: 'textfield',
                                        emptyText: 'Search BOQ...',
                                        width: 200,
                                        enableKeyEvents: true,
                                        listeners: {
                                            specialkey: function (f, e) {
                                                if (e.getKey() === e.ENTER) {
                                                    var store = boqStore;
                                                    store.getProxy().setExtraParam('query', f.getValue());
                                                    store.loadPage(1);
                                                }
                                            }
                                        }
                                    }
                                ],
                                columns: [
                                    {text: 'BOQ No.', dataIndex: 'boq_no', width: 150},
                                    {text: 'Project Name', dataIndex: 'project_name', flex: 1},
                                    {text: 'Client Name', dataIndex: 'client_name', flex: 1},
                                    {text: 'Revision', dataIndex: 'revision', width: 80}
                                ],
                                bbar: {
                                    xtype: 'pagingtoolbar',
                                    store: boqStore,
                                    displayInfo: true
                                },
                                listeners: {
                                    selectionchange: function (sm, selected) {
                                        if (selected.length > 0) {
                                            win.down('#nextBtn').setDisabled(false);
                                        } else {
                                            win.down('#nextBtn').setDisabled(true);
                                        }
                                    }
                                }
                            }
                        ]
                    },
                    // Step 2: Estimate Information
                    {
                        xtype: 'form',
                        itemId: 'estimateInfoForm',
                        layout: 'anchor',
                        bodyPadding: 15,
                        defaults: {
                            anchor: '100%',
                            labelWidth: 120,
                            margin: '0 0 10 0'
                        },
                        items: [
                            {
                                xtype: 'label',
                                text: '2. Estimate Information',
                                style: 'font-weight: bold; font-size: 14px; margin-bottom: 20px; display: block;',
                                hidden: true
                            },
                            {
                                xtype: 'textfield',
                                name: 'estimate_no',
                                fieldLabel: 'Estimate No.',
                                value: '[Auto generated]',
                                readOnly: true
                            },
                            {
                                xtype: 'textfield',
                                name: 'estimate_name',
                                fieldLabel: 'Estimate Name',
                                allowBlank: false
                            },
                            {
                                xtype: 'combobox',
                                name: 'estimate_type_id',
                                fieldLabel: 'Estimate Type',
                                store: estimateTypeStore,
                                displayField: 'estimate_type',
                                valueField: 'id',
                                queryMode: 'local',
                                allowBlank: false
                            },
                            {
                                xtype: 'textfield',
                                name: 'revision',
                                fieldLabel: 'Revision',
                                value: 'Rev. 0',
                                allowBlank: false
                            },
                            {
                                xtype: 'combobox',
                                name: 'currency',
                                fieldLabel: 'Currency',
                                store: ['PHP', 'USD', 'EUR'],
                                value: 'PHP',
                                allowBlank: false
                            },
                            {
                                xtype: 'datefield',
                                name: 'costing_date',
                                fieldLabel: 'Costing Date',
                                value: new Date(),
                                format: 'Y-m-d',
                                allowBlank: false
                            },
                            {
                                xtype: 'textarea',
                                name: 'remarks',
                                fieldLabel: 'Remarks',
                                height: 80
                            }
                        ]
                    },
                    // Step 3: Review
                    {
                        xtype: 'panel',
                        itemId: 'reviewPanel',
                        bodyPadding: 15,
                        scrollable: true,
                        html: 'Loading summary...'
                    },
                    // Step 4: Processing
                    {
                        xtype: 'panel',
                        itemId: 'processingPanel',
                        layout: 'vbox',
                        bodyPadding: 15,
                        items: [
                            {
                                xtype: 'label',
                                text: 'Step 4 - Processing',
                                style: 'font-weight: bold; font-size: 14px; margin-bottom: 10px;',
                                hidden: true
                            },
                            {
                                xtype: 'label',
                                text: 'Creating Estimate...',
                                margin: '0 0 15 0'
                            },
                            {
                                xtype: 'container',
                                itemId: 'processList',
                                html: '<ul style="list-style:none; padding:0; line-height:25px;">' +
                                    '<li id="step_header">⏳ Create Estimate Header</li>' +
                                    '<li id="step_lines">⏳ Copy BOQ Lines</li>' +
                                    '<li id="step_locations">⏳ Copy Locations</li>' +
                                    '<li id="step_link_lines">⏳ Link BOQ Lines</li>' +
                                    '<li id="step_link_templates">⏳ Link Composition Templates</li>' +
                                    '<li id="step_create_lines">⏳ Create Estimate Lines</li>' +
                                    '<li id="step_save">⏳ Save Estimate</li>' +
                                    '</ul>'
                            }
                        ]
                    }
                ],
                bbar: [
                    '->',
                    {
                        text: 'Cancel',
                        itemId: 'cancelBtn',
                        handler: function () {
                            win.close();
                        }
                    },
                    {
                        text: 'Back',
                        itemId: 'backBtn',
                        disabled: true,
                        handler: function () {
                            var layout = win.getLayout();
                            var activeIndex = win.items.indexOf(layout.getActiveItem());
                            layout.setActiveItem(activeIndex - 1);
                            me.updateWizardButtons(win);
                        }
                    },
                    {
                        text: 'Next >',
                        itemId: 'nextBtn',
                        disabled: true,
                        handler: function () {
                            var layout = win.getLayout();
                            var activeIndex = win.items.indexOf(layout.getActiveItem());

                            if (activeIndex === 0) {
                                // Transition from Step 1 to Step 2
                                var selectedBoq = win.down('grid').getSelectionModel().getSelection()[0];
                                var form = win.down('#estimateInfoForm');
                                form.getForm().setValues({
                                    estimate_name: selectedBoq.get('project_name') + ' - Initial Estimate'
                                });
                            } else if (activeIndex === 1) {
                                // Transition from Step 2 to Step 3
                                var form = win.down('#estimateInfoForm');
                                if (!form.getForm().isValid()) return;

                                me.loadReviewSummary(win);
                            }

                            layout.setActiveItem(activeIndex + 1);
                            me.updateWizardButtons(win);
                        }
                    },
                    {
                        text: 'Create Estimate',
                        itemId: 'createBtn',
                        hidden: true,
                        handler: function () {
                            me.startProcessing(win);
                        }
                    },
                    {
                        text: 'Open Estimate',
                        itemId: 'openBtn',
                        hidden: true,
                        handler: function () {
                            // This button is no longer used since we open a new success window
                            win.close();
                            me.down('grid').getStore().reload();
                        }
                    }
                ]
            });
            win.show();
        },

        updateWizardButtons: function (win) {
            var layout = win.getLayout();
            var activeIndex = win.items.indexOf(layout.getActiveItem());
            var backBtn = win.down('#backBtn');
            var nextBtn = win.down('#nextBtn');
            var createBtn = win.down('#createBtn');
            var cancelBtn = win.down('#cancelBtn');
            var openBtn = win.down('#openBtn');

            backBtn.setDisabled(activeIndex === 0 || activeIndex === 3);
            nextBtn.setHidden(activeIndex >= 2);
            createBtn.setHidden(activeIndex !== 2);
            cancelBtn.setHidden(activeIndex === 3);

            var titles = [
                '1. Select Source BOQ',
                '2. Estimate Information',
                '3. Review',
                'Step 4 - Processing'
            ];
            win.setTitle(titles[activeIndex] || 'Create Estimate Wizard');

            if (activeIndex === 3) {
                backBtn.setHidden(true);
            }
        },

        loadReviewSummary: function (win) {
            var me = this;
            var selectedBoq = win.down('grid').getSelectionModel().getSelection()[0];
            var reviewPanel = win.down('#reviewPanel');

            reviewPanel.update('Loading summary...');
            console.log('BOQ Id: ' + selectedBoq.get('id'));
            console.log('Source: ' + selectedBoq.get('source'));
            Ext.Ajax.request({
                url: me.baseUrl + '/Projects/CostEstimate/Main/boq_summary',
                params: {
                    boq_id: selectedBoq.get('id'),
                    record_id: selectedBoq.get('record_id'),
                    source: selectedBoq.get('source')
                },
                success: function (response) {
                    var result = Ext.decode(response.responseText);
                    if (result.success) {
                        var data = result.data;
                        reviewPanel.update(
                            '<div style="line-height:22px;">' +
                            '<b>Source BOQ</b><br>' +
                            selectedBoq.get('boq_no') + '<br>' +
                            '<b>Revision</b><br>' +
                            selectedBoq.get('revision') + '<br>' +
                            '<b>Project</b><br>' +
                            selectedBoq.get('project_name') + '<br>' +
                            '<b>Client</b><br>' +
                            selectedBoq.get('client_name') + '<br>' +
                            '<hr>' +
                            '<b>Summary</b><br>' +
                            '<table width="100%">' +
                            '<tr><td>BOQ Lines</td><td align="right">' + data.lines + '</td></tr>' +
                            '<tr><td>Composition Templates</td><td align="right">' + data.templates + '</td></tr>' +
                            '<tr><td>Project Locations</td><td align="right">' + data.locations + '</td></tr>' +
                            // '<tr><td>Estimated Components</td><td align="right">≈ ' + data.components + '</td></tr>' +
                            '</table>' +
                            '</div>'
                        );
                    } else {
                        reviewPanel.update('Error loading summary: ' + result.message);
                    }
                },
                failure: function () {
                    reviewPanel.update('Failed to connect to server.');
                }
            });
        },

        startProcessing: function (win) {
            var me = this;
            var layout = win.getLayout();
            layout.setActiveItem(3);
            me.updateWizardButtons(win);

            var formValues = win.down('#estimateInfoForm').getForm().getValues();
            var selectedBoq = win.down('grid').getSelectionModel().getSelection()[0];

            var params = Ext.apply(formValues, {
                source_boq_id: selectedBoq.get('id'),
                source_boq_revision_id: selectedBoq.get('record_id'),
                source: selectedBoq.get('source'),
                project_name: selectedBoq.get('project_name'),
                client_code: selectedBoq.get('client_code')
            });

            // For simulation of the steps as described in the UI request
            var steps = [
                {id: 'header', text: 'Create Estimate Header'},
                {id: 'lines', text: 'Copy BOQ Lines'},
                {id: 'locations', text: 'Copy Locations'},
                {id: 'link_lines', text: 'Link BOQ Lines'},
                {id: 'link_templates', text: 'Link Composition Templates'},
                {id: 'create_lines', text: 'Create Estimate Lines'},
                {id: 'save', text: 'Save Estimate'}
            ];

            var updateStep = function (stepId, status, icon) {
                var el = document.getElementById('step_' + stepId);
                if (el) {
                    el.innerHTML = icon + ' ' + steps.find(s => s.id === stepId).text;
                }
            };

            // Real save request
            Ext.Ajax.request({
                url: me.baseUrl + '/Projects/CostEstimate/Main/save',
                params: params,
                success: function (response) {
                    var result = Ext.decode(response.responseText);
                    if (result.success) {
                        // Simulate progress for UI effect
                        var currentStep = 0;
                        var interval = setInterval(function () {
                            if (currentStep < steps.length) {
                                updateStep(steps[currentStep].id, 'success', '✔');
                                currentStep++;
                            } else {
                                clearInterval(interval);

                                var estimateNo = result.estimate_no || formValues.estimate_no || 'EST-2026-XXXXXX';
                                var estimateId = result.id;

                                // Close current wizard window
                                win.close();

                                // Open new success window
                                Ext.create('Ext.window.Window', {
                                    title: 'Estimate Created Successfully',
                                    width: 500,
                                    height: 500,
                                    modal: true,
                                    resizable: false,
                                    layout: {
                                        type: 'vbox',
                                        align: 'center',
                                        pack: 'center'
                                    },
                                    bodyPadding: 20,
                                    items: [{
                                        xtype: 'container',
                                        width: 400,
                                        html: '<div style="color: green; font-weight: bold; font-size: 16px; margin-bottom: 15px; text-align: center;">✔ Estimate Created Successfully</div>' +
                                            '<div style="margin-bottom: 15px; text-align: center;">Your estimate has been generated successfully.</div>' +
                                            '<div style="border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; padding: 10px 0; margin-bottom: 15px; text-align: center;">' +
                                            '<div style="color: #666; font-size: 12px; text-transform: uppercase;">Estimate Number</div>' +
                                            '<div style="font-size: 20px; font-weight: bold; color: #333;">' + estimateNo + '</div>' +
                                            '</div>' +
                                            '<div style="font-weight: bold; margin-bottom: 10px; text-align: center;">Processing Summary</div>' +
                                            '<div style="line-height: 22px; display: inline-block; width: 100%;">' +
                                            '<div style="width: 250px; margin: 0 auto; text-align: left;">' +
                                            '<div>✔ Estimate Header Created</div>' +
                                            '<div>✔ BOQ Lines Copied</div>' +
                                            '<div>✔ Locations Copied</div>' +
                                            '<div>✔ BOQ Links Created</div>' +
                                            '<div>✔ Composition Templates Linked</div>' +
                                            '<div>✔ Estimate Lines Generated</div>' +
                                            '<div>✔ Estimate Saved</div>' +
                                            '</div>' +
                                            '</div>'
                                    }],
                                    buttons: [
                                        {
                                            text: 'Close',
                                            handler: function () {
                                                this.up('window').close();
                                                me.down('grid').getStore().reload();
                                            }
                                        },
                                        {
                                            text: 'Open Estimate',
                                            handler: function () {
                                                var successWin = this.up('window');
                                                successWin.close();

                                                var grid = me.down('grid');
                                                var store = grid.getStore();

                                                store.load({
                                                    callback: function (records, operation, success) {
                                                        if (success && estimateId) {
                                                            var record = store.getById(estimateId);
                                                            if (record) {
                                                                grid.getSelectionModel().select(record);
                                                            }
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    ]
                                }).show();
                            }
                        }, 300);
                    } else {
                        Ext.Msg.alert('Error', result.message || 'Failed to create estimate.');
                        win.down('#backBtn').setDisabled(false).setHidden(false);
                        layout.setActiveItem(2);
                        me.updateWizardButtons(win);
                    }
                },
                failure: function () {
                    Ext.Msg.alert('Error', 'Connection failed.');
                    win.down('#backBtn').setDisabled(false).setHidden(false);
                    layout.setActiveItem(2);
                    me.updateWizardButtons(win);
                }
            });
        },

        populateFromBoq: function (record) {
            var me = this;
            var headerTab = me.down('cost-estimate-tab-header');
            headerTab.getForm().setValues({
                project_name: record.get('project_name'),
                client_code: record.get('client_code')
            });

            // Also update the title
            me.down('panel[region=center]').setTitle('New Cost Estimate for ' + record.get('project_name'));
        }
    });
</script>
