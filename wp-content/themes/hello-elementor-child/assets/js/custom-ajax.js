//1. Xử lý KH chọn địa chỉ
const provinceAPI = 'https://esgoo.net/api-tinhthanh/1/0.htm';
$(function() {
   $.getJSON(provinceAPI, function(res) {
      $.each(res.data, function(key, entry) {
         const option = `<option value="${entry.id}">${entry.name}</option>`;
          $('#province').append(option);
      });
  }).fail(function() {
      console.error("Error loading provinces");
   });
});
$('#province').on('change',function(){
   var provinceId=$(this).val();
   const districtAPI = `https://esgoo.net/api-tinhthanh/2/${provinceId}.htm`;
   $.getJSON(districtAPI, function(res) {
      $('#district').empty().append('<option value="">Quận/Huyện</option>');
      $.each(res.data, function(key, entry) {
         const option = `<option value="${entry.id}">${entry.name}</option>`;
          $('#district').append(option);
      });
      $('#district').prop('disabled', false);
  }).fail(function() {
      $('#district').empty().append('<option value="">Không có dữ liệu</option>').prop('disabled', true);
   });
})
$('#district').on('change', function() {
   var districtId=$(this).val();
   if(districtId){
      const wardAPI = `https://esgoo.net/api-tinhthanh/3/${districtId}.htm`;
      $.getJSON(wardAPI, function(res) {
         $('#ward').empty().append('<option value="">Phường/Xã</option>');
         $.each(res.data, function(key, entry) {
            const option = `<option value="${entry.id}">${entry.name}</option>`;
             $('#ward').append(option);
         });
         $('#ward').prop('disabled', false);
     }).fail(function() {
         $('#ward').empty().append('<option value="">Không có dữ liệu</option>').prop('disabled', true);
           
      });
   }
})
