

$(function() {

  //başlangıçta tüm switchlerin valueleri arraya yükleniyor
  $('.user-select-bulk').each(function() {
    pushToggle( $.trim($(this).val()) );
  });

  // - LABEL
  $('.mesajkolik_label').on('click', function() {
    var data = $(this).parent().parent().attr('data-id');
    var val = $('#pills-auto-sms div[data-id="' + data + '"] textarea').val();
    $('#pills-auto-sms div[data-id="' + data + '"] textarea').val(val + ' ' + $(this).html());
  });

  $('.mesajkolik_label_bulk').on('click', function() {
    var data = $(this).parent().parent().attr('data-id');
    var val = $('#parent-modal-bulk textarea').val();
    $('#parent-modal-bulk textarea').val(val + ' ' + $(this).html());

  });

  // - SWITCH
  $('.mesajkolik_check').on('change', function() {
    var data = $(this).parent().parent().attr('data-id');
    if ($(this).is(':checked')) {
      $('div.mesajkolik_option[data-id="' + $(this).attr('data-id') + '"]').fadeIn();
    } else {
      $('div.mesajkolik_option[data-id="' + $(this).attr('data-id') + '"]').fadeOut();
    }
  });

  //Datatables
  $('#users-bulk-table').DataTable({
    stateSave: true,
    "aoColumnDefs": [{
      "bSortable": false,
      "aTargets": [0, 1, 2, 3]
    }],
    "paging": false,
    "ordering": false,
    "info": false
  });



  //Select All Toggle
  var change = false;
  $('#toggle-select-all-users').change(function() {
    if (!change) {
      change = true;
      var allTog = $(this).is(':checked') ? true : false;
      $('.user-select-bulk').bootstrapToggle($(this).is(':checked') ? 'on' : 'off');
      $('.user-select-bulk').each(function() {
        allTog ? pushToggle( $.trim($(this).val()) ) : unPushToggle( $.trim($(this).val()) );
      });
      change = false;
    }
  });
  //Select User Toggle
  $('.user-select-bulk').on('change', function() {
    if (!change) {
      change = true;
      $(this).is(':checked') ? pushToggle( $.trim($(this).val()) ) : unPushToggle( $.trim($(this).val()) );
      var checked = $('.user-select-bulk').length == $('.user-select-bulk:checked').length;
      if ($('#toggle-select-all-users').is(':checked') != checked) {
        $('#toggle-select-all-users').bootstrapToggle(checked ? 'on' : 'off');
      }
      change = false;
    }
  });
  $('.mesajkolik_check').change(function() {
    var checked = $(this).prop('checked') ? '1' : '0';
    $(this).val(checked);
  })


  $('#mesajkolik_status').change(function() {
    var stat = $(this).prop('checked') ? '1' : '0';
    mesajkolik_alert_responser(true);
    $.post(formPostUrl("pluginStat"), {mesajkolik_status: stat}, function(data){
      mesajkolik_alert_responser(false);
    });
  });


  $('#backup_all_contact').on('click', function(e){
    e.preventDefault();
    var smsContactName = $('#contact-group-name').val();
    var smsContactNameToggle = $('#mesajkolik_lastgroup_toggle').val();
    $.post(formPostUrl("backupAllContact"), {mesajkolik_lastgroup: smsContactName,mesajkolik_lastgroup_toggle: smsContactNameToggle}, function(data){
      data = JSON.parse(data);
      mesajkolik_alert_responser(false);
      if(data.result){
        mesajkolik_alert(data.message, true);
      }else{
        mesajkolik_alert(data.message, false);
      }
    });
  });

  $('#form_private_sms').on('click', function(){
    var gsms = document.getElementsByName("gsm")[0].value;
    var message = document.getElementsByName("message")[0].value;
    $.post(formPostUrl("sendPrivateMessage"), {gsm: gsms,message: message}, function(data){
      data = JSON.parse(data);
      mesajkolik_alert_responser(false);
      if(data.result){
        mesajkolik_alert('SMS Gönderimi Başarılı !', true);
      }else{
        mesajkolik_alert('SMS Gönderiminde Bir Problem Oluştu.', false);
      }
    });

  });

  $('#modal-bulk-private').on('click', function(){
    var gsms = $('#recipient-name').val();
    var message = $('#message-text').val();
    $.post(formPostUrl("sendBulkSms"), {id: gsms,message: message}, function(data){
      $('#bulkPrivateSmsModal').modal('hide');
      data = JSON.parse(data);
      mesajkolik_alert_responser(false);
      if(data.result){
        mesajkolik_alert('SMS Gönderimi Başarılı !', true);
      }else{
        mesajkolik_alert('SMS Gönderiminde Bir Problem Oluştu.', false);
      }
    });

  });


  // - BULK SMS MODAL
  $('#bulkPrivateSmsModal').on('show.bs.modal', function (event) {
    var send = [];
    var button = $(event.relatedTarget)
    var phone = button.data('phone')
    var modal = $(this)
    if (phone == '0') {
      modal.find('.modal-body #recipient-name').val(selectedtoggle)
    }else {
      modal.find('.modal-body #recipient-name').val(phone)
    }
  });

});

// - ALERT EVENTS
function mesajkolik_alert(text, success){
  $('#alert-modal-mesajkolik .mesajkolik_alert_success').hide();
  $('#alert-modal-mesajkolik .mesajkolik_alert_danger').hide();
  if(success !== undefined){
    $('#alert-modal-mesajkolik .mesajkolik_alert_'+(success ? 'success' : 'danger')).show();
  }
  $('#alert-modal-desc').html(text);
  $('#alert-modal').modal('show');
}

function mesajkolik_alert_responser(e){
  var stat = e ? 'show' : 'hide';
  $('#modal-responser').modal({backdrop: 'static', keyboard: false});
  $('#modal-responser').modal(stat);
}

function afterBulkSms(){
  $('#toggle-select-all-users ,.user-select-bulk').bootstrapToggle('on');
}

var selectedtoggle=[];
function pushToggle(e){
  if (!selectedtoggle.includes(e)) {
    selectedtoggle.push(e);
  }

}
function unPushToggle(e){
  if (selectedtoggle.includes(e)) {
    var index = selectedtoggle.indexOf(e);
    selectedtoggle.splice(index, 1);
  }
}
