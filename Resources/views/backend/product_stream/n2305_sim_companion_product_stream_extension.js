//{block name="backend/product_stream/view/condition_list/condition_panel"}
//{$smarty.block.parent}

console.log('product stream extension loaded');

Ext.define('Shopware.apps.ProductStream.n2305SimCompanion.ConditionPanel', {
    override: 'Shopware.apps.ProductStream.view.condition_list.ConditionPanel',

    createConditionHandlers: function() {

        var me = this;
        // fetch original handlers
        var handlers = me.callParent(arguments);

        // add custom handlers
        handlers.push(Ext.create(
            'Shopware.apps.ProductStream.n2305SimCompanion.condition.BranchAvailabilityCondition'
        ));

        return handlers;
    },
});

Ext.define('Shopware.apps.ProductStream.n2305SimCompanion.condition.BranchAvailabilityCondition', {
    extend: 'Shopware.apps.ProductStream.view.condition_list.condition.AbstractCondition',

    getName: function() {
        return 'n2305SimCompanion\\SearchBundleDBAL\\Condition\\BranchAvailabilityCondition';
    },

    getLabel: function() {
        return 'Branch availability';
    },

    isSingleton: function() {
        return true;
    },

    create: function(callback) {
        callback(this.createField());
    },

    load: function(key, value) {
        if (key !== this.getName()) {
            return;
        }

        var field = this.createField();
        field.setValue(value);
        return field;
    },

    createField: function() {
        return Ext.create('Shopware.apps.ProductStream.n2305SimCompanion.field.Branches', {
            flex: 1,
            name: 'condition.' + this.getName()
        });
    },

    createStore: function() {
        return Ext.create('Shopware.store.Search', {
            fields: ['id', 'name'],
            configure: function() {
                return { entity: "Shopware\\Models\\Category\\Category" }
            }
        });
    }
});

Ext.define('Shopware.apps.ProductStream.n2305SimCompanion.field.Branches', {
    extend: 'Ext.form.FieldContainer',
    layout: { type: 'hbox', align: 'stretch' },
    mixins: [ 'Ext.form.field.Base' ],
    height: 30,
    value: undefined,

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            me.createTerm()
        ];
    },

    createTerm: function() {
        var me = this;
        var tm = new Ext.util.TextMetrics();

        var label = 'Branches (comma separated)';
        me.branches = Ext.create('Ext.form.field.Text', {
            labelWidth: tm.getWidth(label + ':'),
            fieldLabel: label,
            allowBlank: false,
            padding: '0 0 0 10',
            flex: 1
        });

        return me.branches;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.branches.setValue('');
            return;
        }

        if (value.hasOwnProperty('branches')) {
            me.branches.setValue(value.branches.join(', '));
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = {
            branches: this.branches.getValue().split(',').map(function (branch) {
                return branch.trim();
            }),
        };

        return value;
    }
});

//{/block}

