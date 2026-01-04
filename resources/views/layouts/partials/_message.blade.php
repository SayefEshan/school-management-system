<script>
    $(document).ready(function () {
        @if(Session::has('success'))
        Swal.fire({
            title: "Success!",
            text: "{{ session('success') }}",
            icon: "success",
            customClass: {
                confirmButton: 'btn btn-success',
            },
        });
        @endif

        @if(Session::has('error'))
        Swal.fire({
            title: "Error!",
            text: "{{ session('error') }}",
            icon: "error",
            customClass: {
                confirmButton: 'btn btn-danger',
            },
        });
        @endif

        @if(Session::has('info'))
        Swal.fire({
            title: "Info!",
            text: "{{ session('info') }}",
            icon: "info",
            timer: 5000,
            showConfirmButton: false,
            position: 'top',
            toast: true,
        });
        @endif

        @if(Session::has('message'))
        Swal.fire({
            title: "Message!",
            text: "{{ session('message') }}",
            icon: "success",
            timer: 5000,
            showConfirmButton: false,
            position: 'top',
            toast: true,
        });
        @endif


        @if(Session::has('status'))
        Swal.fire({
            title: "Status!",
            text: "{{ session('status') }}",
            icon: "info",
            timer: 5000,
            showConfirmButton: false,
            position: 'top',
            toast: true,
        })
        ;
        @endif

        @if(Session::has('warning'))
        Swal.fire({
            title: "Warning!",
            text: "{{ session('warning') }}",
            icon: "warning",
            customClass: {
                confirmButton: 'btn btn-warning',
            },
        });
        @endif

        @if($errors->any())
        let errors = '';
        @foreach ($errors->all() as $error)
            errors += "{{ $error }}\n";
        @endforeach
        Swal.fire({
            title: "Error!",
            text: errors,
            icon: "error",
            customClass: {
                confirmButton: 'btn btn-danger',
            },
        });
        @endif
    });
</script>
