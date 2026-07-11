/**
 * Terms & Conditions Tab
 * This tab manages the terms and conditions associated with the quotation.
 */
Ext.define('App.view.quotations.tabs.Terms', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.quotations-tab-terms',
    title: 'Terms & Conditions',
    itemId: 'termGrid',

    plugins: {
        ptype: 'rowediting',
        clicksToEdit: 2
    },
    columns: [
        {
            text: 'Section',
            dataIndex: 'section',
            width: 150,
            editor: {
                xtype: 'textfield'
            }
        },
        {
            text: 'Content',
            dataIndex: 'description',
            flex: 1,
            editor: {
                xtype: 'textarea',
                listeners: {
                    change: function (field, newValue) {
                        var rowEditing = field.up('grid').findPlugin('rowediting');
                        var record = rowEditing ? rowEditing.context.record : null;
                        if (record) {
                            record.set('description', newValue);
                        }
                    }
                }
            }
        }
    ],
    tbar: [
        {
            text: 'Add Term',
            handler: function () {
                var grid = this.up('grid');
                var rowEditing = grid.findPlugin('rowediting');
                if (rowEditing) {
                    rowEditing.cancelEdit();
                }
                var r = Ext.create(grid.getStore().getModel(), {
                    section: '',
                    description: ''
                });
                grid.getStore().insert(grid.getStore().getCount(), r);
                if (rowEditing) {
                    rowEditing.startEdit(grid.getStore().getCount() - 1, 0);
                }
            }
        },
        {
            text: 'Remove Term',
            handler: function () {
                var grid = this.up('grid');
                var sm = grid.getSelectionModel();
                grid.getStore().remove(sm.getSelection());
            }
        }
    ]
});
