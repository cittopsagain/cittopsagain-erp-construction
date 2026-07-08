<script type="text/javascript">
    Ext.define('App.view.administration.RolePermissions', {
        extend: 'Ext.panel.Panel',
        alias: 'widget.role-permissions-panel',
        layout: 'border',
        height: 700,
        draggable: true,
        resizable: true,
        frame: true,
        title: 'Role Permissions Management',

        items: [
            {
                xtype: 'grid',
                region: 'west',
                title: 'Roles',
                width: 300,
                split: true,
                store: {
                    fields: ['id', 'name', 'description'],
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/RolePermissions/Main/roles',
                        reader: {
                            type: 'json',
                            rootProperty: 'data'
                        }
                    },
                    autoLoad: true
                },
                columns: [
                    {text: 'Role Name', dataIndex: 'name', flex: 1},
                    {text: 'Description', dataIndex: 'description', flex: 1}
                ],
                listeners: {
                    selectionchange: function (grid, selected) {
                        var panel = this.up('role-permissions-panel');
                        var permGrid = panel.down('#permissions-grid');
                        if (selected.length > 0) {
                            var roleId = selected[0].get('id');
                            panel.currentRoleId = roleId;
                            permGrid.setDisabled(false);
                            panel.loadRolePermissions(roleId);
                        } else {
                            panel.currentRoleId = null;
                            permGrid.setDisabled(true);
                            panel.clearCheckboxes();
                        }
                    }
                }
            },
            {
                xtype: 'grid',
                region: 'center',
                itemId: 'permissions-grid',
                title: 'Permissions',
                disabled: true,
                selModel: {
                    selType: 'checkboxmodel',
                    mode: 'SIMPLE',
                    checkOnly: true
                },
                store: {
                    fields: ['id', 'name', 'description'],
                    proxy: {
                        type: 'ajax',
                        url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/RolePermissions/Main/permissions',
                        reader: {
                            type: 'json',
                            rootProperty: 'data'
                        }
                    },
                    autoLoad: true
                },
                columns: [
                    {text: 'Permission', dataIndex: 'name', flex: 1},
                    {text: 'Description', dataIndex: 'description', flex: 2}
                ],
                tbar: [
                    {
                        text: 'Save Permissions',
                        iconCls: 'x-fa fa-save',
                        handler: function () {
                            var panel = this.up('role-permissions-panel');
                            panel.savePermissions();
                        }
                    }
                ]
            }
        ],

        loadRolePermissions: function (roleId) {
            var me = this;
            var permGrid = me.down('#permissions-grid');
            Ext.Ajax.request({
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/RolePermissions/Main/role_permissions',
                params: {role_id: roleId},
                success: function (response) {
                    var result = Ext.decode(response.responseText);
                    if (result.success) {
                        me.clearCheckboxes();
                        var selectedIds = result.data;
                        var store = permGrid.getStore();
                        var recordsToSelect = [];

                        Ext.Array.each(selectedIds, function (id) {
                            var record = store.getById(id);
                            if (record) {
                                recordsToSelect.push(record);
                            } else {
                                // If not found by ID (maybe store not loaded or ID is string vs int)
                                var rec = store.findRecord('id', id);
                                if (rec) recordsToSelect.push(rec);
                            }
                        });

                        if (recordsToSelect.length > 0) {
                            permGrid.getSelectionModel().select(recordsToSelect);
                        }
                    }
                }
            });
        },

        clearCheckboxes: function () {
            this.down('#permissions-grid').getSelectionModel().deselectAll();
        },

        savePermissions: function () {
            var me = this;
            if (!me.currentRoleId) {
                Ext.Msg.alert('Error', 'Please select a role first.');
                return;
            }

            var permGrid = me.down('#permissions-grid');
            var selection = permGrid.getSelectionModel().getSelection();
            var permissionIds = [];
            Ext.Array.each(selection, function (rec) {
                permissionIds.push(rec.get('id'));
            });

            Ext.Ajax.request({
                url: '<?php echo rtrim(BASE_URL, '/'); ?>/Administration/RolePermissions/Main/save',
                method: 'POST',
                params: {
                    role_id: me.currentRoleId,
                    permission_ids: Ext.encode(permissionIds)
                },
                success: function (response) {
                    var result = Ext.decode(response.responseText);
                    if (result.success) {
                        Ext.toast('Permissions saved successfully.');
                    } else {
                        Ext.Msg.alert('Error', result.message || 'Failed to save permissions.');
                    }
                },
                failure: function () {
                    Ext.Msg.alert('Error', 'Server communication failure.');
                }
            });
        }
    });
</script>
