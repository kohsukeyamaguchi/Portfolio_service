$(function(){
 
 
 $('.main_img img:nth-child(n+2)').hide();
      setInterval(function() {
        $(".main_img img:first-child").fadeOut(4000);
        $(".main_img img:first-child").css('display','none');
        $(".main_img img:nth-child(2)").fadeIn(10000);
        $(".main_img img:first-child").appendTo(".main_img");
      }, 8000);
 
});