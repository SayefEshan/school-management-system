<!-- Global stylesheets -->
<link href="{{asset('assets/fonts/inter/inter.css')}}" rel="stylesheet" type="text/css">
<link href="{{asset('assets/icons/phosphor/styles.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{asset('assets/icons/fontawesome/styles.min.css')}}" rel="stylesheet" type="text/css">
<link href="{{asset('assets/css/ltr/all.min.css')}}" id="stylesheet" rel="stylesheet" type="text/css">
<!-- /global stylesheets -->

<style>
    .form-label.required:after {
        content: " *";
        color: red;
    }

    .table-responsive {
        min-height: 300px;
    }

    .custom-scrollbar {
        overflow-x: auto;
        overflow-y: hidden;
        /* Hide vertical scrollbar if not needed */
    }

    .custom-scrollbar::-webkit-scrollbar {
        height: 10px;
        /* Adjust the height of the horizontal scrollbar */
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #888;
        /* Color of the thumb */
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #555;
        /* Hover color of the thumb */
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background-color: #f1f1f1;
        /* Background of the track */
    }
</style>

<!-- Core JS files -->
<script src="{{asset('assets/js/template_configurator.js')}}"></script>
<script src="{{asset('assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/js/jquery/jquery.min.js')}}"></script>
<!-- /core JS files -->

<!-- Theme JS files -->
<script src="{{asset('assets/js/app.js')}}"></script>
<script src="{{asset('assets/js/custom.js')}}"></script>
<!-- /theme JS files -->

<script src="{{asset('assets/js/vendor/notifications/sweet_alert.min.js')}}"></script>
