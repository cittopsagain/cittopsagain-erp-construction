<?php
$baseUrl = rtrim(BASE_URL, '/');
?>

<script type="text/javascript">
    // Define models first
    Ext.define('BoqModel', {
        extend: 'Ext.data.Model',
        fields: [
            'id', 'boq_no', 'project_name', 'client_code', 'client_name', 'location',
            'revision', 'status', 'remarks',
            'created_at', 'updated_at'
        ]
    });

    Ext.define('BoqDetailModel', {
        extend: 'Ext.data.Model',
        fields: [
            'id', 'boq_id', 'composition_template_id', 'composition_template_name', 'composition_template_code',
            'location_id', 'location_name',
            'description',
            'service_id', 'service_name',
            'trade_id', 'trade_name',
            'system_id', 'system_name',
            'installation_method_id', 'installation_method_name',
            {name: 'quantity', type: 'float'},
            {name: 'is_expanded', type: 'boolean', defaultValue: false},
            {name: 'is_base_line', type: 'boolean', defaultValue: false}
        ]
    });

    Ext.define('BoqRevisionModel', {
        extend: 'Ext.data.Model',
        idProperty: 'revision_id',
        fields: [
            'revision_id', 'id', 'boq_no', 'project_name', 'revision', 'status', 'created_at', 'updated_at'
        ]
    });
</script>

<?php
$tabs = ['List', 'Header', 'Locations', 'Lines', 'Revisions'];
foreach ($tabs as $tab) {
    $filePath = __DIR__ . '/tabs/' . $tab . '.js';
    if (file_exists($filePath)) {
        echo '<script type="text/javascript">' . file_get_contents($filePath) . '</script>';
    }
}
?>

<script type="text/javascript">
    Ext.define('App.view.boq.Main', {
        extend: 'Ext.panel.Panel',
        alias: 'widget.boq-main',
        layout: 'border',
        height: 760,
        frame: true,
        draggable: true,
        split: true,

        // Data passed from PHP
        baseUrl: '<?php echo $baseUrl; ?>',
        locationTypes: <?php echo isset($location_types) ? json_encode($location_types) : '[]'; ?>,
        templates: <?php echo isset($templates) ? json_encode($templates) : '[]'; ?>,
        clients: <?php echo isset($clients) ? json_encode($clients) : '[]'; ?>,
        services: <?php echo isset($services) ? json_encode($services) : '[]'; ?>,
        trades: <?php echo isset($trades) ? json_encode($trades) : '[]'; ?>,
        systems: <?php echo isset($systems) ? json_encode($systems) : '[]'; ?>,
        installationMethods: <?php echo isset($installation_methods) ? json_encode($installation_methods) : '[]'; ?>,

        initComponent: function () {
            var me = this;

            me.items = [
                {
                    xtype: 'boq-list'
                },
                {
                    xtype: 'form',
                    region: 'center',
                    title: 'BOQ Details',
                    layout: 'fit',
                    disabled: true,
                    items: [
                        {
                            xtype: 'tabpanel',
                            items: [
                                {
                                    xtype: 'boq-header'
                                },
                                {
                                    title: 'Project Locations',
                                    layout: 'fit',
                                    items: [
                                        {xtype: 'boq-locations-grid'}
                                    ]
                                },
                                {
                                    title: 'BOQ Lines',
                                    layout: 'fit',
                                    height: 500,
                                    items: [{xtype: 'boq-lines-grid'}]
                                },
                                {
                                    title: 'Revisions',
                                    layout: 'fit',
                                    items: [{xtype: 'boq-revisions-grid'}]
                                }
                            ]
                        }
                    ],
                    buttons: [
                        {
                            text: 'Save BOQ',
                            itemId: 'saveBtn',
                            formBind: true,
                            listeners: {
                                enable: function (btn) {
                                    var statusField = btn.up('form').getForm().findField('status');
                                    if (statusField && statusField.getValue() === 'Approved') {
                                        btn.disable();
                                    }
                                }
                            },
                            handler: function () {
                                var formPanel = this.up('form');
                                var detailGrid = formPanel.down('boq-lines-grid');
                                var detailStore = detailGrid.getStore();

                                if (formPanel.getForm().isValid()) {
                                    var details = [];
                                    var hasEmptyTemplate = false;
                                    detailStore.each(function (record) {
                                        if (!record.get('composition_template_id')) {
                                            hasEmptyTemplate = true;
                                        }
                                        details.push(record.getData());
                                    });

                                    if (details.length === 0) {
                                        Ext.Msg.alert('Error', 'Please add at least one BOQ Line before saving.');
                                        return;
                                    }

                                    if (hasEmptyTemplate) {
                                        Ext.Msg.confirm('Confirm', 'Some BOQ Lines do not have a Composition Template selected. These lines will not be saved. Continue?', function (btn) {
                                            if (btn === 'yes') {
                                                submitForm();
                                            }
                                        });
                                        return;
                                    }

                                    submitForm();

                                    function submitForm() {
                                        var locations = [];
                                        var locationsGrid = formPanel.down('boq-locations-grid');
                                        if (locationsGrid) {
                                            locationsGrid.getStore().each(function (record) {
                                                var data = record.getData();
                                                if (data.code || data.name) {
                                                    locations.push(data);
                                                }
                                            });
                                        }

                                        formPanel.getForm().submit({
                                            url: me.baseUrl + '/Projects/Boq/Main/save',
                                            params: {
                                                details: Ext.encode(details),
                                                locations: Ext.encode(locations)
                                            },
                                            waitMsg: 'Saving...',
                                            success: function (f, action) {
                                                Ext.Msg.alert('Success', action.result.message);
                                                me.down('boq-list').getStore().load();
                                                if (action.result.boq_no) {
                                                    formPanel.getForm().findField('boq_no').setValue(action.result.boq_no);
                                                }
                                                if (action.result.revision) {
                                                    formPanel.getForm().findField('revision').setValue(action.result.revision);
                                                }
                                                if (action.result.id) {
                                                    formPanel.getForm().findField('id').setValue(action.result.id);
                                                }
                                                // Reload locations to get proper IDs
                                                if (locationsGrid) {
                                                    locationsGrid.getStore().getProxy().setExtraParam('boq_id', formPanel.getForm().findField('id').getValue());
                                                    locationsGrid.getStore().load();
                                                }
                                                // Reload revisions
                                                var revisionsGrid = me.down('boq-revisions-grid');
                                                if (revisionsGrid) {
                                                    revisionsGrid.getStore().getProxy().setExtraParam('id', formPanel.getForm().findField('id').getValue());
                                                    revisionsGrid.getStore().load();
                                                }
                                                // Reload BOQ lines to ensure proper grouping and data consistency
                                                detailStore.getProxy().setExtraParam('boq_id', formPanel.getForm().findField('id').getValue());
                                                detailStore.load();

                                                // Update UI editability based on status after saving
                                                var statusField = formPanel.getForm().findField('status');
                                                if (statusField) {
                                                    me.updateEditability(statusField.getValue());
                                                }
                                            },
                                            failure: function (f, action) {
                                                Ext.Msg.alert('Error', action.result.message || 'Failed to save BOQ');
                                            }
                                        });
                                    }
                                }
                            }
                        },
                        {
                            text: 'Delete',
                            itemId: 'deleteBtn',
                            listeners: {
                                enable: function (btn) {
                                    var statusField = btn.up('form').getForm().findField('status');
                                    if (statusField && statusField.getValue() === 'Approved') {
                                        btn.disable();
                                    }
                                }
                            },
                            handler: function () {
                                var formPanel = this.up('form');
                                var record = formPanel.getForm().getRecord();
                                if (!record) return;

                                Ext.Msg.confirm('Confirm', 'Are you sure you want to delete this BOQ?', function (btn) {
                                    if (btn === 'yes') {
                                        Ext.Ajax.request({
                                            url: me.baseUrl + '/Projects/Boq/Main/delete',
                                            params: {id: record.get('id')},
                                            success: function (response) {
                                                var result = Ext.decode(response.responseText);
                                                if (result.success) {
                                                    Ext.Msg.alert('Success', result.message);
                                                    me.down('boq-list').getStore().load();
                                                    formPanel.getForm().reset();
                                                    formPanel.setDisabled(true);

                                                    var detailGrid = me.down('boq-lines-grid');
                                                    if (detailGrid) {
                                                        detailGrid.setDisabled(true);
                                                        detailGrid.getStore().removeAll();
                                                    }

                                                    var locationsGrid = me.down('boq-locations-grid');
                                                    if (locationsGrid) {
                                                        locationsGrid.setDisabled(true);
                                                        locationsGrid.getStore().removeAll();
                                                        locationsGrid.getStore().getProxy().setExtraParam('boq_id', null);
                                                    }

                                                    var revisionsGrid = me.down('boq-revisions-grid');
                                                    if (revisionsGrid) {
                                                        revisionsGrid.getStore().removeAll();
                                                        revisionsGrid.getStore().getProxy().setExtraParam('id', null);
                                                    }
                                                } else {
                                                    Ext.Msg.alert('Error', result.message);
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    ]
                }
            ];

            me.updateEditability = function (status) {
                console.log('Button Status: ' + status);
                var isApproved = (status === 'Approved');
                var formPanel = me.down('form');

                // Toggle Header fields
                formPanel.getForm().getFields().each(function (field) {
                    field.setReadOnly(isApproved);
                });

                // Toggle Buttons
                var addLineBtn = me.down('#addLineBtn');
                if (addLineBtn) {
                    addLineBtn.setDisabled(isApproved);
                }

                var addLocationBtn = me.down('#addLocationBtn');
                if (addLocationBtn) {
                    addLocationBtn.setDisabled(isApproved);
                }

                var saveBtn = me.down('#saveBtn');
                if (saveBtn) {
                    if (isApproved) {
                        saveBtn.disable();
                        saveBtn.formBind = false;
                    } else {
                        saveBtn.enable();
                        saveBtn.formBind = true;
                    }
                }

                var deleteBtn = me.down('#deleteBtn');
                if (deleteBtn) {
                    if (isApproved) {
                        deleteBtn.disable();
                        deleteBtn.formBind = false;
                    } else {
                        deleteBtn.enable();
                        deleteBtn.formBind = true;
                    }
                }

                var reviseBtn = me.down('#reviseBtn');
                if (reviseBtn) {
                    reviseBtn.setVisible(isApproved);
                }


                // Refresh grids to update action columns
                var locationsGrid = me.down('boq-locations-grid');
                if (locationsGrid) {
                    var locAddBtn = locationsGrid.down('#addLocationBtn');
                    if (locAddBtn) {
                        locAddBtn.setDisabled(isApproved);
                    }
                    locationsGrid.getView().refresh();
                }

                var detailGrid = me.down('boq-lines-grid');
                if (detailGrid) {
                    var detailAddBtn = detailGrid.down('#addLineBtn');
                    if (detailAddBtn) {
                        detailAddBtn.setDisabled(isApproved);
                    }
                    detailGrid.getView().refresh();
                }
            };

            me.callParent(arguments);
        }
    });
</script>
