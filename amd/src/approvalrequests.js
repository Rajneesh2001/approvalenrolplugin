define(['jquery', 'core/ajax' ,'core/notification'], function($, Ajax, notification) {
    return {
        init: function() {
            $(document).on('click','.approve, .reject',function(e){
                e.preventDefault();
                const REQUEST_ACCEPTED = 1;
                const REQUEST_REJECTED = 3;
                let userid = $(this).attr('id').split(':')[1];
                let courseid = $(this).data('courseid');
                let requestStatus = $(this).hasClass('approve')?'approve':'reject';
                let userdisplayname = $(this).data('username');
                let notificationObject = {
                    message: `User ${userdisplayname} request has been ${requestStatus == 'approve'?'approved':'rejected'}.`,
                    type: 'info'
                    };
                $('.table-responsive').addClass('process');
                $('.loader_container').css('display','block');
                Ajax.call([
                    {
                        methodname: 'enrol_approvalenrol_updaterequests',
                            args: {
                                userid: userid,
                                courseid: courseid,
                                requeststatus:requestStatus == 'approve' ? REQUEST_ACCEPTED:REQUEST_REJECTED
                            },
                            done: function(response) {
                                if(response.statuscode == 200){
                                   $('.table-responsive').removeClass('process');
                                   $('.loader_container').css('display','none');
                                   notification.addNotification(notificationObject);
                                    let tableBody = '';
                                    let i=1;
                                    $.each(response.data, function(index, value){
                                        let lastrow = (index == response.data.length-1);
                                        const commonattr={
                                             'data-courseid': value.courseid,
                                            'data-username': value.firstname +" "+value.lastname,
                                        };
                                        let approveicon = $('<img>',{
                                            src:M.cfg.wwwroot + '/enrol/approvalenrol/pix/check-solid.svg',
                                            class: 'icon approve',
                                            id: 'approve-id:'+value.id,
                                        }).attr(commonattr);
                                        let rejecticon = $('<img>',{
                                            src:M.cfg.wwwroot + '/enrol/approvalenrol/pix/xmark-solid.svg',
                                            class: 'icon reject',
                                            id: 'reject-id:'+value.id,
                                        }).attr(commonattr);
                                         tableBody += `
                                        <tr class="${lastrow?'lastrow':''}">
                                            <td class="cell c0">${i}</td>
                                            <td class="cell c1">${value.firstname + value.lastname}</td>
                                            <td class="cell c2 ">${value.email}</td>
                                            <td class="cell c3 lastcol">
                                            ${approveicon.prop('outerHTML')} 
                                            ${rejecticon.prop('outerHTML')}</td>
                                        </tr>
                                    `;
                                    i++;
                                    });
                                    $('.generaltable tbody').empty();
                                    $('.generaltable tbody').append(tableBody);

                                }
                            },
                            fail:function(){
                            }
                    }
                ]);
            });
        }
    };
});