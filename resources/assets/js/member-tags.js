var MemberTags = MemberTags || {};

(function ($) {

    MemberTags = {

        setup: function () {
            var $container = $('#member-tags-display');
            if (!$container.length || !$container.data('tags-url')) {
                return;
            }

            this.tagsUrl = $container.data('tags-url');
            this.addUrl = $container.data('add-url');
            this.removeUrl = $container.data('remove-url');
            this.createUrl = $container.data('create-url');
            this.csrfToken = $('meta[name=csrf-token]').attr('content');

            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $(document).on('click', '.remove-tag', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.removeTag($(this).closest('.member-tag'));
            });

            $('#available-tags-dropdown').parent().on('show.bs.dropdown', function () {
                self.loadAvailableTags();
            });

            $(document).on('click', '.tag-option', function (e) {
                e.preventDefault();
                self.addTag($(this));
            });

            $('#create-tag-modal').on('shown.bs.modal', function () {
                $('#new-tag-name').focus();
            });

            $('#create-tag-form').on('submit', function (e) {
                e.preventDefault();
                self.createTag();
            });
        },

        removeTag: function ($tag) {
            var self = this;
            var tagId = $tag.data('tag-id');

            $tag.css('opacity', '0.5');
            $.ajax({
                url: self.removeUrl,
                method: 'POST',
                data: { tag_id: tagId, _token: self.csrfToken },
                success: function () {
                    $tag.fadeOut(200, function () { $(this).remove(); });
                },
                error: function () {
                    $tag.css('opacity', '1');
                    toastr.error('Failed to remove tag');
                }
            });
        },

        loadAvailableTags: function () {
            var self = this;
            var $dropdown = $('#available-tags-dropdown');
            $dropdown.html('<li class="dropdown-header">Loading...</li>');

            $.ajax({
                url: self.tagsUrl,
                method: 'GET',
                success: function (data) {
                    var unassigned = data.available.filter(function (tag) {
                        return data.assigned.indexOf(tag.id) === -1;
                    });

                    var html = '';
                    if (unassigned.length === 0) {
                        html += '<li><a href="#" class="text-muted" style="pointer-events: none;">No tags available</a></li>';
                    } else {
                        unassigned.forEach(function (tag) {
                            html += '<li class="tag-option" data-tag-id="' + tag.id + '" data-tag-name="' + tag.name + '" data-tag-visibility="' + tag.visibility + '">';
                            html += '<a href="#"><span class="badge tag-visibility-' + tag.visibility + '">' + tag.name + '</span></a>';
                            html += '</li>';
                        });
                    }
                    if ($('#create-tag-modal').length) {
                        html += '<li role="separator" class="divider"></li>';
                        html += '<li><a href="#" data-toggle="modal" data-target="#create-tag-modal"><i class="fa fa-plus"></i> Create new tag</a></li>';
                    }
                    $dropdown.html(html);
                },
                error: function () {
                    $dropdown.html('<li><a href="#" class="text-danger" style="pointer-events: none;">Failed to load</a></li>');
                }
            });
        },

        addTag: function ($option) {
            var self = this;
            var tagId = $option.data('tag-id');
            var tagName = $option.data('tag-name');
            var tagVisibility = $option.data('tag-visibility');

            $option.css('opacity', '0.5');
            $.ajax({
                url: self.addUrl,
                method: 'POST',
                data: { tag_id: tagId, _token: self.csrfToken },
                success: function () {
                    var $newTag = $('<span class="badge member-tag tag-visibility-' + tagVisibility + '" data-tag-id="' + tagId + '" data-visibility="' + tagVisibility + '">' +
                        tagName + ' <span class="remove-tag" style="margin-left: 4px; cursor: pointer; opacity: 0;">&times;</span></span> ');
                    $('#available-tags-dropdown').parent().before($newTag);
                    $option.remove();
                },
                error: function () {
                    $option.css('opacity', '1');
                    toastr.error('Failed to add tag');
                }
            });
        },

        createTag: function () {
            var self = this;
            var $form = $('#create-tag-form');
            var tagName = $('#new-tag-name').val().trim();
            var tagVisibility = $('#new-tag-visibility').val();

            if (!tagName) {
                toastr.warning('Please enter a tag name');
                return;
            }

            $form.find('button[type="submit"]').prop('disabled', true);
            $.ajax({
                url: self.createUrl,
                method: 'POST',
                data: { name: tagName, visibility: tagVisibility, _token: self.csrfToken },
                success: function (data) {
                    var $newTag = $('<span class="badge member-tag tag-visibility-' + data.tag.visibility + '" data-tag-id="' + data.tag.id + '" data-visibility="' + data.tag.visibility + '">' +
                        data.tag.name + ' <span class="remove-tag" style="margin-left: 4px; cursor: pointer; opacity: 0;">&times;</span></span> ');
                    $('#available-tags-dropdown').parent().before($newTag);
                    $('#create-tag-modal').modal('hide');
                    $form[0].reset();
                    toastr.success('Tag created and assigned');
                },
                error: function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to create tag';
                    toastr.error(message);
                },
                complete: function () {
                    $form.find('button[type="submit"]').prop('disabled', false);
                }
            });
        }
    };

})(window.jQuery);

MemberTags.setup();
