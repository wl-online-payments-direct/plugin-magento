var config = {
    map: {
        '*': {
            checkConnection: 'Worldline_Payment/js/testconnection'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Worldline_Payment/js/system/config/validation-mixin': true
            }
        }
    }
};
