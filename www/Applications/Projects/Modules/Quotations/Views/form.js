/**
 * Quotation Add/Edit Form Window
 *
 * This window contains the comprehensive form for creating or updating a quotation.
 * It manages several data stores (BOQ, Materials, Labor, Overhead, Terms) and
 * provides a TabPanel interface to switch between different cost categories.
 *
 * It communicates with the backend via AJAX to save the entire quotation
 * structure (Header, Details, and Terms) in a single request.
 */
Ext.define('App.view.quotations.Form', {
    extend: 'Ext.window.Window',
    alias: 'widget.quotations-form',
    requires: [
        // Tab components are now pre-loaded in index.php
    ],
    width: '95%',
    height: '95%',
    // maxWidth: 1600,
    // maxHeight: 900,
    layout: 'fit',
    modal: true,
    constrain: true,
    // maximized: true,
    maximizable: true,
    minimizable: false,
    resizable: true,
    record: null,
    grid: null,

    initComponent: function () {
        var me = this;
        var isEdit = !!me.record;
        me.title = isEdit ? 'Edit Quotation' : 'Add Quotation';

        // Auto-maximize on show
        me.on('show', function () {
            me.maximize();
        }, me, {single: true});

        // Store for quotation detail items (with grouping by component)
        var detailStore = Ext.create('Ext.data.Store', {
            fields: ['component_code', 'unit_code', 'item_code', 'qty', 'item_desc', 'price', 'unit_description', 'total_price', 'detail_type', 'markup_percent'],
            groupField: 'component_code',
            filters: [{
                property: 'detail_type',
                value: 'BOQ'
            }],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Operations/Quotations/Main/details',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        // Store for Materials
        var materialStore = Ext.create('Ext.data.Store', {
            fields: ['component_code', 'unit_code', 'item_code', 'qty', 'item_desc', 'price', 'unit_description', 'total_price', 'detail_type', 'markup_percent'],
            filters: [{
                property: 'detail_type',
                value: 'MATERIAL'
            }],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Operations/Quotations/Main/details',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        // Store for Labor
        var laborStore = Ext.create('Ext.data.Store', {
            fields: [
                'component_code', 'unit_code', 'item_code', 'qty', 'item_desc', 'price',
                'unit_description', 'total_price', 'detail_type', 'markup_percent',
                'no_of_men', 'days', 'hours', 'ot_hrs', 'ot_rate'
            ],
            filters: [{
                property: 'detail_type',
                value: 'LABOR'
            }],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Operations/Quotations/Main/details',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        // Store for Overhead
        var overheadStore = Ext.create('Ext.data.Store', {
            fields: ['component_code', 'unit_code', 'item_code', 'qty', 'item_desc', 'price', 'unit_description', 'total_price', 'detail_type', 'markup_percent', 'overhead_computation_type', 'overhead_value'],
            filters: [{
                property: 'detail_type',
                value: 'OVERHEAD'
            }],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Operations/Quotations/Main/details',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        // Store for Terms and Conditions
        var termStore = Ext.create('Ext.data.Store', {
            fields: ['section', 'description'],
            proxy: {
                type: 'ajax',
                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/Quotations/Main/terms',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            }
        });

        me.items = [
            {
                xtype: 'tabpanel',
                itemId: 'mainTabPanel',
                width: '100%',
                listeners: {
                    beforetabchange: function (tabPanel, newTab, oldTab) {
                        if (!oldTab) return true;

                        var grid = oldTab.isXType('gridpanel') ? oldTab : oldTab.down('gridpanel');
                        if (grid) {
                            var rowEditing = grid.findPlugin('rowediting');
                            if (rowEditing && rowEditing.editing) {
                                Ext.Msg.alert('Notice', 'Please update or cancel the current row editing first before proceeding to the next tab.');
                                return false;
                            }
                        }
                    }
                },
                items: [
                    {
                        xtype: 'quotations-tab-header'
                    },
                    {
                        xtype: 'quotations-tab-buildings'
                    },
                    {
                        xtype: 'quotations-tab-boq',
                        store: detailStore
                    },
                    {
                        xtype: 'quotations-tab-materials',
                        store: materialStore
                    },
                    {
                        xtype: 'quotations-tab-labor',
                        store: laborStore
                    },
                    {
                        xtype: 'quotations-tab-overhead',
                        store: overheadStore
                    },
                    {
                        xtype: 'quotations-tab-terms',
                        store: termStore
                    },
                    {
                        xtype: 'quotations-tab-summary',
                        detailStore: detailStore,
                        materialStore: materialStore,
                        laborStore: laborStore,
                        overheadStore: overheadStore
                    },
                ]
            }
        ];

        // Window buttons
        me.buttons = [
            {
                text: 'Save as Draft',
                handler: function () {
                    me.saveQuotation('DRAFT');
                }
            },
            {
                text: 'Save',
                handler: function () {
                    me.saveQuotation('SAVED');
                }
            },
            {
                text: 'Cancel',
                handler: function () {
                    me.close();
                }
            }
        ];

        me.listeners = {
            show: function () {
                if (isEdit) {
                    // Load data for editing
                    me.down('quotations-tab-header').getForm().loadRecord(me.record);
                    var loadParams = {
                        params: {
                            header_id: me.record.get('id')
                        }
                    };
                    detailStore.load(loadParams);
                    materialStore.load(loadParams);
                    laborStore.load(loadParams);
                    overheadStore.load(loadParams);
                    termStore.load(loadParams);

                    // Load building data
                    Ext.Ajax.request({
                        url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/Quotations/Main/buildings',
                        params: {header_id: me.record.get('id')},
                        success: function (response) {
                            var result = Ext.decode(response.responseText);
                            if (result.success && result.data) {
                                var buildingsTab = me.down('quotations-tab-buildings');
                                if (buildingsTab && buildingsTab.loadBuildingsData) {
                                    buildingsTab.loadBuildingsData(result.data);
                                }
                            }
                        }
                    });

                    // Re-calculate summary once stores are loaded
                    var summaryTab = me.down('quotations-tab-summary');
                    if (summaryTab && summaryTab.updateSummaryData) {
                        var storesLoaded = 0;
                        var onStoreLoad = function () {
                            storesLoaded++;
                            if (storesLoaded >= 4) { // Materials, Labor, Overhead, Terms
                                summaryTab.updateSummaryData();
                            }
                        };
                        materialStore.on('load', onStoreLoad, null, {single: true});
                        laborStore.on('load', onStoreLoad, null, {single: true});
                        overheadStore.on('load', onStoreLoad, null, {single: true});
                        termStore.on('load', onStoreLoad, null, {single: true});
                    }
                } else {
                    // Load default terms for new quotation
                    termStore.loadData([{
                        section: 'I',
                        description: 'Price: \nTax Inclusive'
                    },
                        {
                            section: 'II',
                            description: 'Payment Terms: \n30% down payment upon contract signing as mobilization.\n70% balance shall be billed based on progress of work.\nPayment shall be made within 15 days upon submission of billing and service report.\nAll checks payable to J&C Obenita Construction OPC'
                        },
                        {
                            section: 'III',
                            description: 'Warranty: \n1 year'
                        },
                        {
                            section: 'IV',
                            description: 'Technical Support: \nJ&C Obenita Construction OPC shall provide 24/7 on-call technical support for a period of one (1) year from contract start date.\nMaximum on-site response time of 12 hours from the time of official notification by the Client.'
                        },
                        {
                            section: 'V',
                            description: 'Quotation Validity: \n30 days upon receipt of quotation'
                        },
                        {
                            section: 'VI',
                            description: 'Timeline for Mobilization: \n1 day after receiving of PO / NTP/ DP'
                        }
                    ]);
                }
            }
        };

        me.callParent(arguments);
    },

    saveQuotation: function (status) {
        var me = this;
        var isEdit = !!me.record;
        var headerTab = me.down('quotations-tab-header');
        var form = headerTab.getForm();

        if (form.isValid()) {
            var headerData = form.getValues();
            if (isEdit) headerData.id = me.record.get('id');
            headerData.status = status;

            var details = [];
            var detailStore = me.down('quotations-tab-boq').getStore();
            var materialStore = me.down('quotations-tab-materials').getStore();
            var laborStore = me.down('quotations-tab-labor').getStore();
            var overheadStore = me.down('quotations-tab-overhead').getStore();
            var termStore = me.down('quotations-tab-terms').getStore();
            var buildingsTab = me.down('quotations-tab-buildings');
            var buildingsData = buildingsTab && buildingsTab.getBuildingsData ? buildingsTab.getBuildingsData() : null;

            detailStore.each(function (rec) {
                details.push(rec.getData());
            });
            materialStore.each(function (rec) {
                details.push(rec.getData());
            });
            laborStore.each(function (rec) {
                details.push(rec.getData());
            });
            overheadStore.each(function (rec) {
                details.push(rec.getData());
            });

            if (details.length === 0) {
                Ext.Msg.alert('Error', 'Please add at least one item.');
                return;
            }

            var terms = [];
            termStore.each(function (rec) {
                terms.push(rec.getData());
            });

            // Submit data to server
            Ext.Ajax.request({
                url: '<?php echo rtrim(BASE_URL, "/"); ?>/Projects/Quotations/Main/save',
                method: 'POST',
                params: {
                    header: Ext.encode(headerData),
                    details: Ext.encode(details),
                    terms: Ext.encode(terms),
                    buildings: Ext.encode(buildingsData)
                },
                success: function (response) {
                    var result = Ext.decode(response.responseText);
                    if (result.success) {
                        Ext.Msg.alert('Success', result.message);
                        me.close();
                        if (me.grid) me.grid.getStore().load();
                    } else {
                        Ext.Msg.alert('Failed', result.message);
                    }
                }
            });
        }
    }
});
