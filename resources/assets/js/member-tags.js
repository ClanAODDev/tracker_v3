const MemberTags = {
    $container: null,
    $dropdown: null,
    $createModal: null,
    $createForm: null,
    urls: {},
    csrfToken: null,

    setup() {
        this.$container = $('#member-tags-display');
        if (!this.$container.length || !this.$container.data('tags-url')) {
            return;
        }

        this.$dropdown = $('#available-tags-dropdown');
        this.$createModal = $('#create-tag-modal');
        this.$createForm = $('#create-tag-form');

        this.urls = {
            tags: this.$container.data('tags-url'),
            add: this.$container.data('add-url'),
            remove: this.$container.data('remove-url'),
            create: this.$container.data('create-url')
        };
        this.csrfToken = $('meta[name=csrf-token]').attr('content');

        this.bindEvents();
    },

    bindEvents() {
        $(document).on('click', '.remove-tag', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.removeTag($(e.currentTarget).closest('.member-tag'));
        });

        this.$dropdown.parent()
            .on('show.bs.dropdown', () => this.loadAvailableTags())
            .on('shown.bs.dropdown', () => $('#tag-search-input').focus());

        $(document).on('click', '.tag-option', (e) => {
            e.preventDefault();
            this.addTag($(e.currentTarget));
        });

        $(document).on('input', '#tag-search-input', (e) => {
            e.stopPropagation();
            const search = $(e.currentTarget).val().toLowerCase();
            $('.tag-option').each(function () {
                const name = $(this).data('tag-name').toLowerCase();
                $(this).toggle(name.includes(search));
            });
        });

        $(document).on('keydown', '#tag-search-input', (e) => e.stopPropagation());

        this.$createModal.on('shown.bs.modal', () => $('#new-tag-name').focus());

        this.$createForm.on('submit', (e) => {
            e.preventDefault();
            this.createTag();
        });
    },

    createTagBadge(tag, removable = true) {
        const removeBtn = removable
            ? ` <span class="remove-tag" style="margin-left: 4px; cursor: pointer; opacity: 0;">&times;</span>`
            : '';
        return $(`<span class="badge member-tag tag-visibility-${tag.visibility}" data-tag-id="${tag.id}" data-visibility="${tag.visibility}">${tag.name}${removeBtn}</span> `);
    },

    removeTag($tag) {
        const tagId = $tag.data('tag-id');
        $tag.css('opacity', '0.5');

        $.ajax({
            url: this.urls.remove,
            method: 'POST',
            data: { tag_id: tagId, _token: this.csrfToken },
            success: () => $tag.fadeOut(200, function () { $(this).remove(); }),
            error: () => {
                $tag.css('opacity', '1');
                toastr.error('Failed to remove tag');
            }
        });
    },

    loadAvailableTags() {
        this.$dropdown.html('<li class="dropdown-header">Loading...</li>');

        $.ajax({
            url: this.urls.tags,
            method: 'GET',
            success: (data) => {
                const unassigned = data.available.filter(tag => !data.assigned.includes(tag.id));
                const canCreate = this.$createModal.length > 0;

                let html = `
                    <li class="dropdown-header" style="padding: 5px 10px;">
                        <input type="text" id="tag-search-input" class="form-control input-sm" placeholder="Search tags..." style="width: 100%;">
                    </li>`;

                if (unassigned.length === 0) {
                    html += '<li><a href="#" class="text-muted" style="pointer-events: none;">No tags available</a></li>';
                } else {
                    const tagItems = unassigned.map(tag => `
                        <li class="tag-option" data-tag-id="${tag.id}" data-tag-name="${tag.name}" data-tag-visibility="${tag.visibility}">
                            <a href="#"><span class="badge tag-visibility-${tag.visibility}">${tag.name}</span></a>
                        </li>`).join('');

                    html += `<li><div class="tag-options-scroll" style="max-height: 200px; overflow-y: auto;"><ul class="list-unstyled" style="margin: 0; padding: 0;">${tagItems}</ul></div></li>`;
                }

                if (canCreate) {
                    html += `
                        <li role="separator" class="divider"></li>
                        <li><a href="#" data-toggle="modal" data-target="#create-tag-modal"><i class="fa fa-plus"></i> Create new tag</a></li>`;
                }

                this.$dropdown.html(html);
            },
            error: () => {
                this.$dropdown.html('<li><a href="#" class="text-danger" style="pointer-events: none;">Failed to load</a></li>');
            }
        });
    },

    addTag($option) {
        const tag = {
            id: $option.data('tag-id'),
            name: $option.data('tag-name'),
            visibility: $option.data('tag-visibility')
        };

        $option.css('opacity', '0.5');

        $.ajax({
            url: this.urls.add,
            method: 'POST',
            data: { tag_id: tag.id, _token: this.csrfToken },
            success: () => {
                this.$dropdown.parent().before(this.createTagBadge(tag));
                $option.remove();
            },
            error: () => {
                $option.css('opacity', '1');
                toastr.error('Failed to add tag');
            }
        });
    },

    createTag() {
        const tagName = $('#new-tag-name').val().trim();
        const tagVisibility = $('#new-tag-visibility').val();

        if (!tagName) {
            toastr.warning('Please enter a tag name');
            return;
        }

        const $submitBtn = this.$createForm.find('button[type="submit"]');
        $submitBtn.prop('disabled', true);

        $.ajax({
            url: this.urls.create,
            method: 'POST',
            data: { name: tagName, visibility: tagVisibility, _token: this.csrfToken },
            success: (data) => {
                this.$dropdown.parent().before(this.createTagBadge(data.tag));
                this.$createModal.modal('hide');
                this.$createForm[0].reset();
                toastr.success('Tag created and assigned');
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.error || 'Failed to create tag';
                toastr.error(message);
            },
            complete: () => $submitBtn.prop('disabled', false)
        });
    }
};

MemberTags.setup();
