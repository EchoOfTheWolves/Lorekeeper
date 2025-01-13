<script>
    $(document).on('click', '.form-data-button', function() {
        // get value from data-id="" attribute
        var id = $(this).attr("data-id");
        // ajax get
        $.ajax({
            type: "GET",
            url: "{{ url('character/' . $character->slug . '/image') }}/" + id,
            dataType: "text"
        }).done(function(res) {
            $("#main-tab").fadeOut(500, function() {
                $("#main-tab").html(res);
                $('#main-tab').find('[data-toggle="toggle"]').bootstrapToggle();
                $('.reupload-image').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/character/image') }}/"+$(this).data('id')+"/reupload", 'Reupload Image');
                });
                $('.active-image').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/character/image') }}/"+$(this).data('id')+"/active", 'Set Active');
                });
                $('.delete-image').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/character/image') }}/"+$(this).data('id')+"/delete", 'Delete Image');
                });
                $('.edit-features').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/character/image') }}/"+$(this).data('id')+"/traits", 'Edit Traits');
                });
                $('.edit-notes').on('click', function(e) {
                    e.preventDefault();
                    $( "div.imagenoteseditingparse" ).load("{{ url('admin/character/image') }}/"+$(this).data('id')+"/notes", function() {
                        tinymce.init({
                            selector: '.imagenoteseditingparse .wysiwyg',
                            height: 500,
                            menubar: false,
                            convert_urls: false,
                            plugins: [
                                'advlist autolink lists link image charmap print preview anchor',
                                'searchreplace visualblocks code fullscreen spoiler',
                                'insertdatetime media table paste code help wordcount'
                            ],
                            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | spoiler-add spoiler-remove | removeformat | code',
                            content_css: [
                                '{{ asset('css/app.css') }}',
                                '{{ asset('css/lorekeeper.css') }}'
                            ],
                            spoiler_caption: 'Toggle Spoiler',
                            target_list: false
                        });
                    });
                    $( ".edit-notes" ).remove();
                });
                $('.edit-credits').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/character/image') }}/"+$(this).data('id')+"/credits", 'Edit Image Credits');
                });
                $("#main-tab").fadeIn(500);
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("AJAX call failed: " + textStatus + ", " + errorThrown);
        });
    });
</script>