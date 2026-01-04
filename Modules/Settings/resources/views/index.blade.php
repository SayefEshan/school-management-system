@extends('settings::layouts.master')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">System Settings</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('system_settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Nav tabs -->
                <div class="d-flex overflow-auto mb-3">
                    <ul class="nav nav-tabs nav-tabs-highlight flex-nowrap" id="settingsTabs" role="tablist">
                        @foreach ($settings as $key => $group)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ snakeCase($key) }}-tab"
                                    data-bs-toggle="tab" href="#{{ snakeCase($key) }}" role="tab"
                                    aria-controls="{{ snakeCase($key) }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $key }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Tab panes -->
                <div class="tab-content" id="settingsTabsContent">
                    @foreach ($settings as $key => $group)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ snakeCase($key) }}"
                            role="tabpanel" aria-labelledby="{{ snakeCase($key) }}-tab">
                            <div class="row p-2">
                                @foreach ($group as $key => $setting)
                                    @if (in_array($setting->key, $specialSettings))
                                        @continue
                                    @endif

                                    <div class="col-md-6 mb-3">
                                        <label for="{{ $setting->key }}"
                                            class="form-label @if ($setting->required) required @endif">{{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                        @if ($setting->type === 'integer' || $setting->type === 'float')
                                            <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                                class="form-control" required value="{{ $setting->value }}">
                                        @elseif($setting->type === 'boolean')
                                            <div class="form-check">
                                                <input type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                                    class="form-check-input" {{ $setting->value ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $setting->key }}">Enable</label>
                                            </div>
                                        @elseif($setting->type === 'textarea')
                                            <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control" required>{{ $setting->value }}</textarea>
                                        @elseif($setting->type === 'file')
                                            <input type="file" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                                class="form-control">
                                            @if ($setting->value)
                                                <a href="{{ $setting->value }}" target="_blank">
                                                    <x-truncated-text :text="$setting->value" :limit="20" />
                                                </a>
                                            @endif
                                        @elseif($setting->type === 'image')
                                            <input type="file" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                                class="form-control" accept="image/*">
                                            @if ($setting->value)
                                                <img src="{{ $setting->value }}" alt="{{ $setting->key }}"
                                                    class="img-thumbnail mt-2" style="max-height: 50px;">
                                            @endif
                                        @elseif($setting->type === 'select')
                                            <select name="{{ $setting->key }}" id="{{ $setting->key }}"
                                                class="form-control" required>
                                                @foreach ($setting->options as $optionKey => $optionValue)
                                                    <option value="{{ $optionKey }}"
                                                        {{ $setting->value == $optionKey ? 'selected' : '' }}>
                                                        {{ $optionValue }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($setting->type === 'multi-select')
                                            <select name="{{ $setting->key }}[]" id="{{ $setting->key }}"
                                                class="form-control" multiple required>
                                                @foreach ($setting->options as $optionKey => $optionValue)
                                                    <option value="{{ $optionKey }}"
                                                        {{ in_array($optionKey, $setting->value ?: []) ? 'selected' : '' }}>
                                                        {{ $optionValue }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($setting->type === 'json')
                                            <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                        @else
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                                class="form-control" required value="{{ $setting->value }}">
                                        @endif
                                        @if ($setting->description)
                                            <div class="form-text text-muted">{{ $setting->description }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Update Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function snakeCase(str) {
            return str.toLowerCase().replace(/\s+/g, '-');
        }
    </script>
@endpush
