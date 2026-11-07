define(['jquery', 'core/ajax', 'core/templates', 'core/notification'], function($, Ajax, Templates, Notification) {
    'use strict';

    const buttonSelector = '[data-region="wall"] [data-action="load-more"]';
    const postSelector = '.post-item, article';

    /**
     * Focuses the first post in the added content.
     *
     * @param {HTMLElement|jQuery} container The inserted posts or their container.
     */
    function focusFirstNewPost(container) {
        const $content = $(container);
        const $newPost = $content.filter(postSelector).add($content.find(postSelector)).first();
        if ($newPost.length) {
            if (!$newPost.is('a, button, input, [tabindex]')) {
                $newPost.attr('tabindex', '-1');
            }
            $newPost.focus();
        }
    }

    /**
     * Loads and appends the page requested by the wall button.
     *
     * @param {HTMLButtonElement} button The load more button.
     * @param {number} courseid The course id, or zero for the general wall.
     */
    async function loadMore(button, courseid) {
        const page = Number(button.dataset.page);
        if (button.disabled || !Number.isSafeInteger(page) || page < 1) {
            return;
        }

        button.disabled = true;
        try {
            const {posts} = await Ajax.call([{
                methodname: 'local_chuspasocial_get_posts',
                args: {courseid, page, perpage: 20},
            }])[0];

            if (posts.length === 0) {
                button.remove();
                return;
            }

            const rendered = await Promise.all(posts.map(post => Templates.renderForPromise('local_chuspasocial/post', {
                ...post,
                authorname: post.authorfullname,
                isannouncement: post.type === 'announcement',
                postid: post.id,
            })));
            const $content = $(rendered.flatMap(({html}) => $.parseHTML(html)));
            $(button).before($content);
            button.dataset.page = String(page + 1);
            Templates.runTemplateJS(rendered.map(({js}) => js).join('\n'));
            focusFirstNewPost($content);
            $(document).trigger('wall:posts-loaded', [{content: $content}]);
        } catch (error) {
            Notification.exception(error);
        } finally {
            button.disabled = false;
        }
    }

    return {
        focusFirstNewPost,
        /**
         * Binds the load more button for the current wall.
         *
         * @param {number} [courseid=0] The course id, or zero for the general wall.
         */
        init: function(courseid = 0) {
            $(document)
                .off('click.chuspasocialWall', buttonSelector)
                .on('click.chuspasocialWall', buttonSelector, function(event) {
                    event.preventDefault();
                    loadMore(this, courseid);
                });
        },
    };
});
