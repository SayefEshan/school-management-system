<div class="modal-body">
    <div class="mb-3">
        <p class="text-danger">N:B: This is approximate location, this may be not accurate...</p>
        @if(isset($ipInfo))
            <ul>
                <li>Country: {{ $ipInfo->country ?? "N/A" }}</li>
                <li>Country Code: {{ $ipInfo->countryCode ?? "N/A" }}</li>
                <li>Region: {{ $ipInfo->region ?? "N/A" }}</li>
                <li>Region Name: {{ $ipInfo->regionName ?? "N/A" }}</li>
                <li>City: {{ $ipInfo->city ?? "N/A" }}</li>
                <li>Zip: {{ $ipInfo->zip ?? "N/A" }}</li>
                <li>Latitude: {{ $ipInfo->lat ?? "N/A" }}</li>
                <li>Longitude: {{ $ipInfo->lon ?? "N/A" }}</li>
                <li>Timezone: {{ $ipInfo->timezone ?? "N/A" }}</li>
                <li>ISP: {{ $ipInfo->isp ?? "N/A" }}</li>
                <li>Organization: {{ $ipInfo->org ?? "N/A" }}</li>
                <li>AS: {{ $ipInfo->as ?? "N/A" }}</li>
                <li>Query: {{ $ipInfo->query ?? "N/A" }}</li>
            </ul>
            <div class="text-center">
                <a href="https://www.google.com/maps/search/?api=1&query={{ $ipInfo->lat ?? 0 }},{{ $ipInfo->lon ?? 0 }}" target="_blank" class="btn btn-primary">View on Map</a>
            </div>
        @else
            <p>{{ $error }}</p>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
