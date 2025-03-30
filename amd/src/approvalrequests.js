define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            $('.approve').click(function() {
                try{
                Ajax.call([{
                    methodname: 'enrol_approvalenrol_execute',
                    args: {
                        email: 'user123@gmail.com'
                    },
                    done: function(response) {
                        console.log(response);
                    }
                }]);
            }catch(e){
                console.log(e);
            }
            });
            $('.reject').click(function() {
                 console.log('rejected');
            });
        }
    };
});