$(document).ready(function () {
    $('.confirm-btn').click(function () {
        var button = $(this);
        var row = button.closest('tr');
        var appointmentId = row.data('id');

        $.ajax({
            url: 'confirm_appointment.php',
            method: 'POST',
            data: { appointment_id: appointmentId },
            success: function (response) {
                if (response.trim() === "true") {
                    button.remove();
                    row.find('.status-text').text("Confirmed");
                } else {
                    alert("Failed to confirm appointment.");
                }
            },
            error: function () {
                alert("AJAX error.");
            }
        });
    });
});