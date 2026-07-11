/**
 * Summary Tab
 * This tab displays the cost summary of the quotation, including sub-totals and grand total.
 */
Ext.define('App.view.quotations.tabs.Summary', {
    extend: 'Ext.form.Panel',
    alias: 'widget.quotations-tab-summary',
    title: 'Summary',
    itemId: 'summaryForm',
    bodyPadding: 20,
    scrollable: true,
    layout: {
        type: 'vbox',
        align: 'left'
    },

    // Dependency stores will be passed or accessed via the parent window
    detailStore: null,
    materialStore: null,
    laborStore: null,
    overheadStore: null,

    defaults: {
        xtype: 'displayfield',
        labelWidth: 150,
        width: 600
    },
    items: [
        {
            xtype: 'label',
            text: 'CLIENT INFORMATION',
            width: 600,
            style: 'font-weight: bold; display: block; margin-top: 10px;'
        },
        {
            xtype: 'component',
            html: '<hr style="border: 0; border-top: 1px dashed #000; margin: 5px 0;">',
            width: 600
        },
        {
            fieldLabel: 'Client Name',
            itemId: 'sum_client_name'
        },
        {
            fieldLabel: 'Project Name',
            itemId: 'sum_project_name'
        },
        {
            fieldLabel: 'Quotation No',
            itemId: 'sum_quotation_no'
        },
        {
            fieldLabel: 'Service',
            itemId: 'sum_service'
        },
        {
            fieldLabel: 'Revision No',
            itemId: 'sum_revision_no',
            value: '0'
        },
        {
            xtype: 'component',
            html: '<hr style="border: 0; border-top: 1px dashed #000; margin: 10px 0;">',
            width: 600
        },
        {
            xtype: 'label',
            text: 'COST SUMMARY',
            width: 600,
            style: 'font-weight: bold; display: block; margin-top: 10px;'
        },
        {
            xtype: 'component',
            html: '<hr style="border: 0; border-top: 1px dashed #000; margin: 5px 0;">',
            width: 600
        },
        {
            fieldLabel: 'Material Cost',
            itemId: 'sum_materials',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0
        },
        {
            fieldLabel: 'Labor Cost',
            itemId: 'sum_labor',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0
        },
        {
            fieldLabel: 'Overhead Cost',
            itemId: 'sum_overhead',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0
        },
        {
            xtype: 'component',
            margin: '10 0'
        },
        {
            fieldLabel: 'PROJECT COST',
            itemId: 'sum_project_cost',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0,
            fieldStyle: 'font-weight: bold;'
        },
        {
            xtype: 'component',
            margin: '10 0'
        },
        {
            fieldLabel: 'Markup %',
            itemId: 'sum_markup_percent',
            renderer: function (v) {
                return Ext.util.Format.number(v, '0,000.00') + '%';
            },
            value: 0
        },
        {
            fieldLabel: 'Profit Amount',
            itemId: 'sum_profit_amount',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0
        },
        {
            xtype: 'component',
            margin: '10 0'
        },
        {
            fieldLabel: 'SELLING PRICE',
            itemId: 'sum_selling_price',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0,
            fieldStyle: 'font-weight: bold; color: green;'
        },
        {
            xtype: 'component',
            margin: '10 0'
        },
        {
            fieldLabel: 'VAT (12%)',
            itemId: 'sum_vat',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0
        },
        {
            xtype: 'component',
            margin: '10 0'
        },
        {
            fieldLabel: 'FINAL AMOUNT',
            itemId: 'sum_final_amount',
            renderer: Ext.util.Format.numberRenderer('0,000.00'),
            value: 0,
            fieldStyle: 'font-weight: bold; color: blue;'
        },
        {
            xtype: 'component',
            html: '<hr style="border: 0; border-top: 1px dashed #000; margin: 10px 0;">',
            width: 600
        },
        {
            xtype: 'label',
            text: 'TERMS & CONDITIONS',
            width: 600,
            style: 'font-weight: bold; display: block; margin-top: 10px;'
        },
        {
            xtype: 'component',
            html: '<hr style="border: 0; border-top: 1px dashed #000; margin: 5px 0;">',
            width: 600
        },
        {
            xtype: 'container',
            itemId: 'sum_terms_container',
            width: 600,
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                xtype: 'displayfield',
                labelWidth: 150
            },
            items: []
        },
        {
            xtype: 'component',
            html: '<hr style="border: 0; border-top: 1px dashed #000; margin: 10px 0;">',
            width: 600
        }
    ],
    listeners: {
        beforerender: function (tab) {
            // Add a shared method to update summary data
            tab.updateSummaryData = function () {
                /**
                 * Recommended Computation Flow:
                 *
                 * 1. Direct Cost = Material Cost + Labor Cost
                 * 2. Project Cost = Direct Cost + Overhead
                 * 3. Profit Amount = Project Cost x Markup %
                 * 4. Selling Price = Project Cost + Profit Amount
                 * 5. Final Amount = Selling Price + VAT - Discount
                 */

                var win = tab.up('window');
                if (!win) return;

                var headerTab = win.down('quotations-tab-header');
                var termsTab = win.down('quotations-tab-terms');

                // --- Update Client Information ---
                if (headerTab) {
                    var headerForm = headerTab.getForm();
                    var clientCombo = headerForm.findField('client_code');
                    var serviceCombo = headerForm.findField('service_code');

                    var sumClientName = tab.down('#sum_client_name');
                    if (sumClientName) {
                        sumClientName.setValue(clientCombo ? clientCombo.getRawValue() : '');
                    }

                    var sumProjectName = tab.down('#sum_project_name');
                    if (sumProjectName) {
                        sumProjectName.setValue(headerForm.findField('project_name').getValue());
                    }

                    var sumQuotationNo = tab.down('#sum_quotation_no');
                    if (sumQuotationNo) {
                        sumQuotationNo.setValue(headerForm.findField('quot_ctrl_no').getValue() || 'NEW');
                    }

                    var sumService = tab.down('#sum_service');
                    if (sumService) {
                        sumService.setValue(serviceCombo ? serviceCombo.getRawValue() : '');
                    }

                    // Revision No could be added to header if needed, but for now we use '0' as default
                    var revision = 0;
                    if (win.record) {
                        revision = win.record.get('revision_no') || 0;
                    }
                    var sumRevisionNo = tab.down('#sum_revision_no');
                    if (sumRevisionNo) {
                        sumRevisionNo.setValue(revision);
                    }
                }

                // --- Update Terms & Conditions ---
                if (termsTab) {
                    var termStore = termsTab.getStore();
                    var termsContainer = tab.down('#sum_terms_container');

                    if (termsContainer) {
                        termsContainer.removeAll();
                        termStore.each(function (rec) {
                            var section = rec.get('section') || '';
                            var content = rec.get('description') || '';

                            termsContainer.add({
                                fieldLabel: section,
                                value: content,
                                fieldStyle: 'white-space: pre-wrap;'
                            });
                        });
                    }
                }

                // --- Cost Summary Computations ---

                // 1. Material Cost: Sum of (Qty * Price) for all items in Materials store
                var matTotal = 0;
                if (tab.materialStore) {
                    tab.materialStore.each(function (rec) {
                        matTotal += (rec.get('qty') * rec.get('price'));
                    });
                }

                // 2. Labor Cost: Sum of ((Men * Days * Hours * Rate) + (Men * OT Hours * OT Rate))
                var labTotal = 0;
                if (tab.laborStore) {
                    tab.laborStore.each(function (rec) {
                        labTotal += (rec.get('no_of_men') * rec.get('days') * rec.get('hours') * rec.get('price')) +
                            (rec.get('no_of_men') * rec.get('ot_hrs') * rec.get('ot_rate'));
                    });
                }

                // 3. Overhead Cost: Sum of overhead items (Fixed or % of Direct Cost)
                var ovhTotal = 0;
                if (tab.overheadStore) {
                    tab.overheadStore.each(function (rec) {
                        var computationType = rec.get('overhead_computation_type');
                        var val = rec.get('overhead_value') || 0;
                        var qty = rec.get('qty') || 0;

                        if (computationType === '%') {
                            // Base for percentage overhead is Direct Cost (Materials + Labor)
                            ovhTotal += (matTotal + labTotal) * (val / 100);
                        } else {
                            ovhTotal += (val * qty);
                        }
                    });
                }

                // 4. Project Cost = Direct Cost (Material + Labor) + Overhead
                var projectCost = matTotal + labTotal + ovhTotal;

                // 5. Markup % (We can get this from the header or a default. 
                // For now, let's assume it's stored in the header if available, or use a default)
                var markupPercent = 0;
                if (headerTab) {
                    markupPercent = headerTab.getForm().findField('markup_percent') ? headerTab.getForm().findField('markup_percent').getValue() : 0;
                }

                // 6. Profit Amount = Project Cost * Markup %
                var profitAmount = projectCost * (markupPercent / 100);

                // 7. Selling Price = Project Cost + Profit Amount
                var sellingPrice = projectCost + profitAmount;

                // 8. VAT (12%) = Selling Price * 12%
                var vatAmount = sellingPrice * 0.12;

                // 9. Final Amount = Selling Price + VAT
                var discount = 0;
                if (headerTab) {
                    discount = headerTab.getForm().findField('discount') ? headerTab.getForm().findField('discount').getValue() : 0;
                }
                var finalAmount = sellingPrice + vatAmount - discount;

                // Update summary fields
                var s_materials = tab.down('#sum_materials');
                if (s_materials) s_materials.setValue(matTotal);

                var s_labor = tab.down('#sum_labor');
                if (s_labor) s_labor.setValue(labTotal);

                var s_overhead = tab.down('#sum_overhead');
                if (s_overhead) s_overhead.setValue(ovhTotal);

                var s_project_cost = tab.down('#sum_project_cost');
                if (s_project_cost) s_project_cost.setValue(projectCost);

                var s_markup_percent = tab.down('#sum_markup_percent');
                if (s_markup_percent) s_markup_percent.setValue(markupPercent);

                var s_profit_amount = tab.down('#sum_profit_amount');
                if (s_profit_amount) s_profit_amount.setValue(profitAmount);

                var s_selling_price = tab.down('#sum_selling_price');
                if (s_selling_price) s_selling_price.setValue(sellingPrice);

                var s_vat = tab.down('#sum_vat');
                if (s_vat) s_vat.setValue(vatAmount);

                var s_final_amount = tab.down('#sum_final_amount');
                if (s_final_amount) s_final_amount.setValue(finalAmount);
            };
        },
        activate: function (tab) {
            if (tab.updateSummaryData) {
                tab.updateSummaryData();
            }
        }
    }
});
