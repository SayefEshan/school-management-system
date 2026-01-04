<label for="{{ $setting->key }}"
       class="form-label @if($setting->required) required @endif">{{ ucwords(str_replace('_', ' ', $setting->key)) }}
</label>
<textarea name="{{ $setting->key }}" id="{{ $setting->key }}"
          class="form-control" required>{{ $setting->value }}</textarea>

@push('scripts')
    <script>
        // on load use CKEditor to replace the textarea with a rich text editor
        document.addEventListener('DOMContentLoaded', function () {
            ClassicEditor
                .create(document.querySelector('#{{ $setting->key }}'), {
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                            { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                            { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                        ]
                    },
                })
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        document.querySelector('#{{ $setting->key }}').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
@endpush
