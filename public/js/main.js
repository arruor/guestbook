$(function() {
    $('.actions i.fa-pencil').click(function() {
        alert($(this).attr('data-id'));
    });
});