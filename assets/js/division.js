 $(".toplist tbody tr").click(function() {
     window.location.href = "member/" + $(this).attr('data-id');
 });
