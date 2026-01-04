<div class="form-control-feedback form-control-feedback-start flex-grow-1" data-color-theme="dark">
    <input type="text" id="search-input" class="form-control bg-transparent rounded-pill" placeholder="Search"
           data-bs-toggle="dropdown" style="border-color: white;">
    <div class="form-control-feedback-icon">
        <i class="ph-magnifying-glass"></i>
    </div>
    <div class="dropdown-menu w-100" data-color-theme="light">
        <button type="button" class="dropdown-item">
            <div class="text-center w-32px me-3">
                <i class="ph-magnifying-glass"></i>
            </div>
            <span>Search <span class="fw-bold">"in"</span> everywhere</span>
        </button>

        <div id="inform-user"></div>

        <div class="dropdown-divider"></div>
        <div class="dropdown-menu-scrollable-lg">
            <div id="search-results">
                <!-- Results will be appended here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const informUser = document.getElementById('inform-user');
            const searchResults = document.getElementById('search-results');

            searchInput.addEventListener('input', function () {
                const query = searchInput.value;

                if (query.length > 2) {
                    // hide inform user
                    informUser.innerHTML = '';
                    fetch(`/search?search=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            // handles users
                            searchResults.innerHTML = '';

                            const userHeaderItem = document.createElement('div');
                            userHeaderItem.className = 'dropdown-header';
                            userHeaderItem.innerHTML = `Users
                        <a href="{{ route('admin.users.index') }}?search=${query}" class="float-end">
                            See all
                            <i class="ph-arrow-circle-right ms-1"></i>
                        </a>`;
                            searchResults.appendChild(userHeaderItem);

                            if (data.users) {
                                data.users.forEach(user => {
                                    const resultItem = document.createElement('div');
                                    resultItem.className = 'dropdown-item cursor-pointer';
                                    resultItem.innerHTML = `<div class="me-3">
                                        <img src="${user.image}"
                                             class="w-32px h-32px" alt="">
                                    </div>

                                    <div class="d-flex flex-column flex-grow-1 text-truncate">
                                        <div class="fw-semibold">
                                            ${user.first_name} ${user.last_name}
                                        </div>
                                        <span class="fs-sm text-muted text-truncate">${user.phone}</span>
                                    </div>

                                    <div class="d-inline-flex">
                                        <a href="/user/${user.id}" class="text-body ms-2">
                                            <i class="ph-user-circle"></i>
                                        </a>
                                    </div>`;
                                    searchResults.appendChild(resultItem);
                                });
                            }

                            // Handle Dividers
                            if (data.users) {
                                const divider = document.createElement('div');
                                divider.className = 'dropdown-divider';
                                searchResults.appendChild(divider);
                            }

                            // Handle Other Data
                        });
                } else {
                    searchResults.innerHTML = '';
                    informUser.innerHTML = '';
                    // Inform user to write at least 3 characters
                    const resultItem = document.createElement('button');
                    resultItem.type = 'button';
                    resultItem.className = 'dropdown-item';
                    resultItem.innerHTML = `
                    <div class="text-center w-100">
                        <i class="ph-x-circle fs-3 text-danger"></i>
                        <p class="text-muted text-center">Please write at least 3 characters...</p>
                    </div>
                    `;
                    informUser.appendChild(resultItem);
                }
            });
        });
    </script>
@endpush
