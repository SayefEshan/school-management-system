<script>
    $(document).on('click', '.swal-confirm', function (e) {
        e.preventDefault();
        const $this = $(this);
        const url = $this.data('url') || $this.attr('href');
        const text = $this.data('text') || "Are you sure?";
        Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, do it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
</script>

<script>
    $(document).on('click', '.swal-delete', function (e) {
        e.preventDefault();
        const $this = $(this);
        const url = $this.data('url') || $this.attr('href');
        const text = $this.data('text') || "Are you sure?";
        Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form>', {
                    'method': 'POST',
                    'action': url
                });
                form.append('@csrf');
                form.append('@method("DELETE")');
                $('body').append(form);
                form.submit();
            }
        });
    });
</script>

<script>
    $(document).on('click', '.swal-post', function (e) {
        e.preventDefault();
        const $this = $(this);
        const url = $this.data('url') || $this.attr('href');
        const text = $this.data('text') || "Are you sure?";
        Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form>', {
                    'method': 'POST',
                    'action': url
                });
                form.append('@csrf');
                form.append('@method("POST")');
                $('body').append(form);
                form.submit();
            }
        });
    });
</script>

<script>
    function formPost(formId, submitButtonId, postUrl, redirectUrl , message_show_time = 5000) {
        $(".custom-error-p").remove();
        $(".is-invalid").removeClass("is-invalid");
        formId = "#" + formId;
        submitButtonId = "#" + submitButtonId;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        var form = $(formId);
        var form_method = $(formId).attr('method');
        // var submitButton = $(formId + " input[type=submit]");

        var submitButtonHtml = $(submitButtonId).html();
        $.ajax({
            url: postUrl,
            type: form_method,
            data: new FormData(form[0]),
            contentType: false,
            processData: false,
            dataType: "json",

            beforeSend: function() {
                $(submitButtonId).html('<i class="fa fa-spinner fa-spin"></i>');
                $(submitButtonId).attr('disabled', true);
            },
            success: function(data) {
                if (data.code === 200) {
                    Swal.fire({
                        title: "Success!",
                        text: data.message,
                        icon: "success",
                        timer: message_show_time,
                        showConfirmButton: false,
                        position: 'top',
                        toast: true,
                    });
                    window.setTimeout(function() {
                        redirectUrl = redirectUrl.replace(/&amp;/g, '&');
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: data.message,
                        icon: "error",
                        timer: message_show_time,
                        showConfirmButton: false,
                        position: 'top',
                        toast: true,
                    });
                }
            },
            error: function(error) {
                console.log(error);
                var json_response = error.responseJSON
                console.log(json_response);
                if(json_response.code === 422) {
                    Swal.fire({
                        title: "Error!",
                        text: json_response.message,
                        icon: "error",
                        timer: 5000,
                        showConfirmButton: false,
                        position: 'top',
                        toast: true,
                    });

                    if (json_response.errors) {

                        let keys = Object.keys(json_response.errors);

                        keys.forEach(key => {
                            var inputElement = $(`${formId} input[name="${key}"]`);
                            inputElement.addClass('is-invalid');

                            var classElement = $(`.${key}`);
                            classElement.addClass('is-invalid');

                            let errors = json_response.errors[key];

                            let errorString = errors.join(" ");

                            inputElement.after(
                                `<label class="custom-error-p error invalid-feedback">${errorString}</label>`
                            );
                            classElement.append(
                                `<small class="custom-error-p text-danger">${errorString}</small>`
                            );
                        });
                    }


                }
                else{
                    Swal.fire({
                        title: "Error!",
                        text: json_response.message,
                        icon: "error",
                        timer: message_show_time,
                        showConfirmButton: false,
                        position: 'top',
                        toast: true,
                    });
                }
            },
            complete: function(data) {
                $(submitButtonId).html(submitButtonHtml);
                $(submitButtonId).removeAttr('disabled');
            }
        });
    }

    $('.ajaxFormSubmit').on('submit', function(e) {
        e.preventDefault();
        var formId = $(this).attr('id');
        var submitButton = $(this).find('button[type=submit]');
        if(!submitButton) {
            submitButton = $(this).find('input[type=submit]');
        }
        submitButtonId = submitButton.attr('id')
        if(!submitButtonId)
        {
            var randomNumber = Math.floor(Math.random() * 100) + 1;
            submitButtonId.attr('id','ajaxFormSubmit'+randomNumber)
        }

        var postUrl = $(this).attr('action');
        var redirectUrl = $(this).attr('data-redirect');
        formPost(formId, submitButtonId, postUrl, redirectUrl);
    });
</script>
